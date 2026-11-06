<?php

declare(strict_types=1);
namespace ProfessionalWiki\NeoWiki\EntryPoints\Semantic;

use MediaWiki\Context\DerivativeContext;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use ProfessionalWiki\NeoWiki\Application\Semantic\{AnnotationPlan,AnnotationPlanner,FieldSnapshot};
use ProfessionalWiki\NeoWiki\Domain\Page\PageSubjects;
use ProfessionalWiki\NeoWiki\Domain\Schema\Schema;
use ProfessionalWiki\NeoWiki\Domain\Subject\Subject;
use ProfessionalWiki\NeoWiki\EntryPoints\Processing\ProcessingIncomplete;

/** Called only inside the authorized full revision worker, never on the anonymous view path. */
final class AutomaticAnnotations {
    public function apply(Title $title, ParserOutput $output, ?Parser $parser, Schema $schema, PageSubjects $pageSubjects, Subject $subject, User $user, \Closure $resolveSchema): void {
        if ($parser === null || !$parser->getTitle()->isSamePageAs($title)) {
            throw new ProcessingIncomplete('SemanticNeoWiki: missing full revision parser context.');
        }
        $recorder = new RevisionDependencies($title,$output);
        $context = new DerivativeContext(RequestContext::getMain());
        $context->setUser($user);
        $language = MediaWikiServices::getInstance()->getContentLanguage()->getCode();
        $planner = new AnnotationPlanner((new WikiAnnotationConfig())->read($parser,$output,$user));
        $subjects = $pageSubjects->getAllSubjects();
        $pending = [[$subject, $schema]];
        $visited = [$subject->id->text => true];
        $rows = [];
        $objects = [];
        for ($index = 0; $index < count($pending); $index++) {
            [$current, $currentSchema] = $pending[$index];
            $recorder->recordDependencyOnSchema($current->getSchemaReference());
            $reader = FieldSnapshot::reader($currentSchema,$current,static function() use($title,$current,$language,$context,$recorder): array {
                if (!class_exists(\NeoWikiBaseBridge\ExternalValuesLookup::class)) {
                    throw new ProcessingIncomplete('SemanticNeoWiki: source binding requires NeoWikiBaseBridge.');
                }
                return (new \NeoWikiBaseBridge\ExternalValuesLookup($context))->read($title,[
                    'pageid'=>$title->getArticleID(),'subject'=>$current->id->text,'language'=>$language,'presentation'=>false
                ],$recorder,$current);
            });
            $subjectPlan = $planner->build($reader->collectForAnnotations($language));
            foreach ($subjectPlan->rows as $row) {
                $rows[$row] = true;
            }
            array_push($objects, ...$subjectPlan->objects);

            foreach ($current->getReferencedSubjects()->asArray() as $targetId) {
                if (isset($visited[$targetId->text])) {
                    continue;
                }
                $visited[$targetId->text] = true;
                $target = $subjects->getSubject($targetId);
                if ($target === null || !$target->getSchemaReference()->isLocal()) {
                    continue;
                }
                $targetSchema = $resolveSchema($target->getSchemaReference());
                if ($targetSchema->subjectsAreDependent()) {
                    $pending[] = [$target, $targetSchema];
                }
            }
        }
        $plan = new AnnotationPlan(array_keys($rows), $objects);
        // Track property-definition dependencies, including not-yet-created definitions.
        $names = [];
        foreach ($plan->rows as $row) { $names[explode('=',$row,2)[0]] = true; }
        foreach ($plan->objects as $args) { foreach (array_slice($args,1) as $row) { $names[explode('=',$row,2)[0]] = true; } }
        foreach (array_keys($names) as $name) {
            $propertyTitle = Title::makeTitleSafe(SMW_NS_PROPERTY,$name);
            if ($propertyTitle !== null) { $recorder->recordDependencyOn($propertyTitle); }
        }
        (new SmwAnnotationApplier())->apply($title,$output,$plan);
    }
}
