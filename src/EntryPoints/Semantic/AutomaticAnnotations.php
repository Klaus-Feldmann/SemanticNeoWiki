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
use ProfessionalWiki\NeoWiki\Application\Semantic\{AnnotationPlanner,FieldSnapshot};
use ProfessionalWiki\NeoWiki\Domain\Schema\Schema;
use ProfessionalWiki\NeoWiki\Domain\Subject\Subject;
use ProfessionalWiki\NeoWiki\EntryPoints\Processing\ProcessingIncomplete;

/** Called only inside the authorized full revision worker, never on the anonymous view path. */
final class AutomaticAnnotations {
    public function apply(Title $title, ParserOutput $output, ?Parser $parser, Schema $schema, Subject $subject, User $user): void {
        if ($parser === null || !$parser->getTitle()->isSamePageAs($title)) {
            throw new ProcessingIncomplete('SemanticNeoWiki: missing full revision parser context.');
        }
        $recorder = new RevisionDependencies($title,$output);
        $recorder->recordDependencyOnSchema($subject->getSchemaReference());
        $context = new DerivativeContext(RequestContext::getMain());
        $context->setUser($user);
        $language = MediaWikiServices::getInstance()->getContentLanguage()->getCode();
        $reader = FieldSnapshot::reader($schema,$subject,static function() use($title,$subject,$language,$context,$recorder): array {
            if (!class_exists(\NeoWikiBaseBridge\ExternalValuesLookup::class)) {
                throw new ProcessingIncomplete('SemanticNeoWiki: source binding requires NeoWikiBaseBridge.');
            }
            return (new \NeoWikiBaseBridge\ExternalValuesLookup($context))->read($title,[
                'pageid'=>$title->getArticleID(),'subject'=>$subject->id->text,'language'=>$language,'presentation'=>false
            ],$recorder,$subject);
        });
        $config = (new WikiAnnotationConfig())->read($parser,$output,$user);
        $plan = (new AnnotationPlanner($config))->build($reader->collect(null,'all',$language));
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
