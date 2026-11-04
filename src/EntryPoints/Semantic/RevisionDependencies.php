<?php

declare(strict_types=1);
namespace ProfessionalWiki\NeoWiki\EntryPoints\Semantic;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;
use ProfessionalWiki\NeoWiki\Application\PageDependencyRecorder;
use ProfessionalWiki\NeoWiki\Domain\Schema\SchemaReference;
use ProfessionalWiki\NeoWiki\NeoWikiExtension;

/** Full revision output is a merge of slot outputs, not necessarily Parser::getOutput(). */
final class RevisionDependencies implements PageDependencyRecorder {
    public function __construct(private Title $current, private ParserOutput $output) {}
    public function recordDependencyOn(Title $page): void {
        if ($page->canExist() && !$page->isSamePageAs($this->current)) {
            $this->output->addTemplate($page,$page->getArticleID(),$page->getLatestRevID());
        }
    }
    public function recordDependencyOnSchema(SchemaReference $schema): void {
        $title=$schema->isLocal() ? Title::makeTitleSafe(NeoWikiExtension::NS_SCHEMA,$schema->name->getText()) : null;
        if ($title!==null) { $this->recordDependencyOn($title); }
    }
}
