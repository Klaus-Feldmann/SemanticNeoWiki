<?php

declare(strict_types=1);
namespace ProfessionalWiki\NeoWiki\EntryPoints\Semantic;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\{Parser,ParserOutput,ParserOutputLinkTypes};
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use ProfessionalWiki\NeoWiki\EntryPoints\Processing\ProcessingIncomplete;

/** Evaluate existing editable Lua data only; never invoke the annotation module. */
final class WikiAnnotationConfig {
    public function read(Parser $parser,ParserOutput $output,User $user): array {
        $title=Title::newFromText('Module:NeoWiki/config');
        if(!$title || !$user->authorizeRead('read',$title)) { throw new ProcessingIncomplete('SemanticNeoWiki: configuration is not readable.'); }
        $services=MediaWikiServices::getInstance();
        $revision=$services->getRevisionLookup()->getRevisionByTitle($title,0,\Wikimedia\Rdbms\IDBAccessObject::READ_LATEST);
        if(!$revision || !$revision->userCan(\MediaWiki\Revision\RevisionRecord::DELETED_TEXT,$user)) { throw new ProcessingIncomplete('SemanticNeoWiki: configuration is unavailable.'); }
        $recorder=new RevisionDependencies($parser->getTitle(),$output);
        $recorder->recordDependencyOn($title);
        $engine=$services->getService('Scribunto.EngineFactory')->getEngineForParser($parser);
        $module=$engine->fetchModuleFromParser($title);
        if(!$module || !method_exists($module,'getExportTable')) { throw new ProcessingIncomplete('SemanticNeoWiki: configuration is not a Lua data module.'); }
        $data=$module->getExportTable();
        if(!is_array($data)) { throw new ProcessingIncomplete('SemanticNeoWiki: configuration did not return a table.'); }
        // Retain dependencies introduced by configuration imports as well as the module itself.
        foreach($parser->getOutput()->getLinkList(ParserOutputLinkTypes::TEMPLATE) as $link) {
            $output->addTemplate($link['link'],Title::newFromLinkTarget($link['link'])->getArticleID(),$link['revid']);
        }
        return $data;
    }
}
