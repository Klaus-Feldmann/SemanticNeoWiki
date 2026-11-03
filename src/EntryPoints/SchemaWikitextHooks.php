<?php

declare( strict_types = 1 );
namespace ProfessionalWiki\NeoWiki\EntryPoints;

use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use ProfessionalWiki\NeoWiki\Application\SchemaWikitext;
use ProfessionalWiki\NeoWiki\EntryPoints\Content\SchemaContent;
use ProfessionalWiki\NeoWiki\Infrastructure\ParserPageDependencyRecorder;
use ProfessionalWiki\NeoWiki\NeoWikiExtension;
use ProfessionalWiki\NeoWiki\Persistence\MediaWiki\Subject\SubjectSlotReader;
use ProfessionalWiki\NeoWiki\Persistence\MediaWiki\Subject\MediaWikiSubjectRepository;
use ProfessionalWiki\NeoWiki\EntryPoints\Jobs\SchemaWikitextRefreshJob;
use Throwable;

final class SchemaWikitextHooks {
	private const MARKER = 'neowiki-schema-wikitext-visited';

	public static function onParserBeforeInternalParse( Parser $parser, string &$text, $stripState ): void {
		if ( $parser->getOutputType() !== Parser::OT_HTML || $parser->getOptions()->isMessage()
			|| $parser->getOutput()->getExtensionData( self::MARKER ) ) { return; }
		$parser->getOutput()->setExtensionData( self::MARKER, true );
		$title = $parser->getTitle();
		if ( !$title->canExist() || $title->getContentModel() !== 'wikitext' ) { return; }
		// Lua reads the current subject, so do not graft that subject onto historical page views.
		$revisionId = $parser->getRevisionId();
		if ( $revisionId && $revisionId < $title->getLatestRevID() ) { return; }
		try {
			$extension = NeoWikiExtension::getInstance();
			$subject = $extension->newSubjectResolver( $parser )->resolveMainByTitle( $title );
			if ( $subject === null || !$subject->getSchemaReference()->isLocal() ) { return; }
			$reference = $subject->getSchemaReference();
			( new ParserPageDependencyRecorder( $parser ) )->recordDependencyOnSchema( $reference );
			$schema = $extension->getSchemaResolverFor( ParserAuthority::of( $parser ) )->getSchema( $reference );
			if ( $schema === null ) { return; }
			$text = SchemaWikitext::append( $text, SchemaWikitext::programs( $schema ) );
		} catch ( Throwable $e ) {
			LoggerFactory::getInstance( 'NeoWiki' )->error( 'Schema wikitext preparation failed', [ 'exception' => $e ] );
			$text .= "\n<strong class=\"error\">" . wfMessage( 'neowiki-wikitext-error' )->escaped() . '</strong>';
		}
	}

	public static function onRevisionFromEditComplete( $wikiPage, RevisionRecord $revision, int|bool $originalRevId, $user, array &$tags ): void {
		try {
			$old = $originalRevId ? MediaWikiServices::getInstance()->getRevisionLookup()->getRevisionById( (int)$originalRevId ) : null;
			$main = $revision->getContent( SlotRecord::MAIN );
			$oldMain = $old?->getContent( SlotRecord::MAIN );
			if ( $main instanceof SchemaContent || $oldMain instanceof SchemaContent ) {
				if ( self::schemaPrograms( $main ) !== self::schemaPrograms( $oldMain ) ) {
					$title = $wikiPage->getTitle();
					DeferredUpdates::addCallableUpdate( static function () use ( $title ): void {
						MediaWikiServices::getInstance()->getJobQueueGroup()->push( new SchemaWikitextRefreshJob( $title, [ 'after' => 0 ] ) );
					} );
				}
				return;
			}
			if ( !$revision->hasSlot( MediaWikiSubjectRepository::SLOT_NAME ) ) {
				if ( !$old?->hasSlot( MediaWikiSubjectRepository::SLOT_NAME ) ) { return; }
			} elseif ( $revision->getSlot( MediaWikiSubjectRepository::SLOT_NAME, RevisionRecord::RAW )->isInherited() ) { return; }
			if ( !self::usesExecution( $revision ) && !( $old && self::usesExecution( $old ) ) ) { return; }
			$title = $wikiPage->getTitle();
			DeferredUpdates::addCallableUpdate( static function () use ( $title ): void {
				try {
					$ok = self::refreshPage( $title );
					if ( !$ok ) { MediaWikiServices::getInstance()->getJobQueueGroup()->push( new SchemaWikitextRefreshJob( $title, [ 'page' => true ] ) ); }
				} catch ( Throwable $e ) {
					LoggerFactory::getInstance( 'NeoWiki' )->error( 'Schema wikitext page refresh failed; queued retry', [ 'exception' => $e ] );
					MediaWikiServices::getInstance()->getJobQueueGroup()->push( new SchemaWikitextRefreshJob( $title, [ 'page' => true ] ) );
				}
			} );
		} catch ( Throwable $e ) {
			LoggerFactory::getInstance( 'NeoWiki' )->error( 'Schema wikitext refresh scheduling failed', [ 'exception' => $e ] );
		}
	}

	private static function usesExecution( RevisionRecord $revision ): bool {
		$subject = SubjectSlotReader::read( $revision )?->getPageSubjects()->getMainSubject();
		if ( $subject === null || !$subject->getSchemaReference()->isLocal() ) { return false; }
		$schema = NeoWikiExtension::getInstance()->getSchemaResolver()->getSchema( $subject->getSchemaReference() );
		return $schema !== null && SchemaWikitext::programs( $schema ) !== [];
	}

	private static function schemaPrograms( $content ): array {
		if ( !$content instanceof SchemaContent ) { return []; }
		$data = json_decode( $content->getText(), true );
		$result = [];
		foreach ( $data['propertyDefinitions'] ?? [] as $name => $definition ) {
			if ( ( $definition['type'] ?? null ) === 'wikitext' && ( $definition['execute'] ?? false ) === true ) {
				$result[$name] = $definition['wikitext'] ?? '';
			}
		}
		return $result;
	}

	public static function refreshPage( Title $title ): bool {
		if ( !self::refreshJob( $title )->run() ) { return false; }
		NeoWikiExtension::getInstance()->newHookPageRebuilder()->rebuild( $title );
		return true;
	}

	public static function refreshJob( Title $title ): object {
		MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title )->doPurge();
		$class = class_exists( \MediaWiki\JobQueue\Jobs\RefreshLinksJob::class )
			? \MediaWiki\JobQueue\Jobs\RefreshLinksJob::class : \RefreshLinksJob::class;
		// No root timestamp: the job must obtain a fresh parse rather than reuse an inherited main-slot output.
		return new $class( $title, [ 'recursive' => false, 'useRecursiveLinksUpdate' => false,
			'causeAction' => 'neowiki-schema-wikitext', 'causeAgent' => 'NeoWiki' ] );
	}
}
