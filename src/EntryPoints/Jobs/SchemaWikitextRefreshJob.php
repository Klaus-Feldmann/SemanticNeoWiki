<?php

declare( strict_types = 1 );
namespace ProfessionalWiki\NeoWiki\EntryPoints\Jobs;

use Job;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use ProfessionalWiki\NeoWiki\EntryPoints\SchemaWikitextHooks;

/** Indexed, bounded batches; no whole-wiki scan or SQL schema change. */
class SchemaWikitextRefreshJob extends Job {
	public function __construct( Title $title, array $params ) {
		parent::__construct( 'neowikiSchemaWikitextRefresh', $title, $params );
		$this->removeDuplicates = true;
	}
	public function run(): bool {
		$services = MediaWikiServices::getInstance();
		if ( ( $this->params['page'] ?? false ) === true ) { return SchemaWikitextHooks::refreshPage( $this->title ); }
		$after = (int)( $this->params['after'] ?? 0 );
		$db = $services->getConnectionProvider()->getPrimaryDatabase();
		$ids = $db->newSelectQueryBuilder()->select( 'nws_page_id' )->distinct()->from( 'neowiki_subject' )
			->where( [ 'nws_schema' => $this->title->getText(), 'nws_is_main' => 1 ] )
			->andWhere( $db->expr( 'nws_page_id', '>', $after ) )->orderBy( 'nws_page_id' )->limit( 100 )
			->caller( __METHOD__ )->fetchFieldValues();
		foreach ( $ids as $id ) {
			$title = Title::newFromID( (int)$id );
			if ( $title !== null ) { $services->getJobQueueGroup()->push( new self( $title, [ 'page' => true ] ) ); }
		}
		if ( count( $ids ) === 100 ) {
			$services->getJobQueueGroup()->push( new self( $this->title, [ 'after' => (int)end( $ids ) ] ) );
		}
		return true;
	}
}
