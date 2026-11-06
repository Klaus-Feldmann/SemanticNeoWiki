<?php

declare( strict_types = 1 );
namespace ProfessionalWiki\NeoWiki\Tests\Domain\Schema;

use NeoWikiBaseBridge\GraphFieldPresentation;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\NeoWiki\Domain\Schema\PropertyDefinition;
use ProfessionalWiki\NeoWiki\Domain\Schema\PropertyDefinitions;
use ProfessionalWiki\NeoWiki\Domain\Schema\Schema;
use ProfessionalWiki\NeoWiki\Domain\Schema\SchemaName;
use ProfessionalWiki\NeoWiki\Tests\Data\TestSources;

require_once __DIR__ . '/../../../../SchemaBaseBridge/src/GraphFieldPresentation.php';
require_once __DIR__ . '/../../../../SchemaBaseBridge/src/TimePresentation.php';

class GraphFieldPresentationTest extends TestCase {
	public function testPageTitlesArePreservedAlongsideTheirRdfIris(): void {
		$schema = new Schema( new SchemaName( 'Person' ), '', new PropertyDefinitions( [
			'Bezug' => PropertyDefinition::fromJson( [ 'type' => 'page' ], TestSources::newPropertyTypeRegistry() ),
		] ), false );
		$subject = \ProfessionalWiki\NeoWiki\Tests\Data\TestSubject::build( statements:
			new \ProfessionalWiki\NeoWiki\Domain\Subject\StatementList( [
				\ProfessionalWiki\NeoWiki\Tests\Data\TestStatement::build( 'Bezug',
					new \ProfessionalWiki\NeoWiki\Domain\Value\StringValue( 'Werk:Abhandlung', 'Athen' ), 'page' ),
			] ) );
		$view = GraphFieldPresentation::fromSchema( $schema, 'https://wiki.example/wiki/NEOWIKIGRAPHTITLETOKEN', $subject );
		$this->assertSame( [ 'Werk:Abhandlung', 'Athen' ], $view['definitions'][0]['ownPages'] );
		$this->assertSame( 'page', $view['definitions'][0]['type'] );
	}

	public function testOwnStructuredUrlEntriesArePreservedForTheGraph(): void {
		$entry = [ 'url' => 'https://example.org', 'label' => 'Article', 'caption' => 'Source', 'captionPage' => 'Vermerk:Quelle' ];
		$schema = new Schema( new SchemaName( 'Person' ), '', new PropertyDefinitions( [ 'Quelle' => PropertyDefinition::fromJson( [ 'type' => 'url' ], TestSources::newPropertyTypeRegistry() ) ] ), false );
		$subject = \ProfessionalWiki\NeoWiki\Tests\Data\TestSubject::build( statements: new \ProfessionalWiki\NeoWiki\Domain\Subject\StatementList( [ \ProfessionalWiki\NeoWiki\Tests\Data\TestStatement::build( 'Quelle', new \ProfessionalWiki\NeoWiki\Domain\Value\UrlValue( [ $entry ] ), 'url' ) ] ) );
		$view = GraphFieldPresentation::fromSchema( $schema, 'https://wiki.example/wiki/NEOWIKIGRAPHTITLETOKEN', $subject );
		$this->assertSame( [ $entry ], $view['definitions'][0]['ownUrls'] );
	}

	public function testSchemaOrderLinkPolicyAndSourcesArePreservedWithoutChangingSchema(): void {
		$definitions = [
			'Klassifizierung' => [ 'type' => 'text', 'wikiLink' => [ 'labelTarget' => 'Konzept:Klassifizierung', 'namespace' => 'Forschung', 'externalTarget' => 'local' ], 'externalProperty' => [ 'source' => 'wikidata', 'id' => 'P31' ] ],
			'Tätigkeit' => [ 'type' => 'text', 'wikiLink' => [ 'values' => false ] ],
			'Lokaler Befund' => [ 'type' => 'text', 'externalProperty' => [ 'source' => 'wikibase-local', 'id' => 'P31' ] ],
		];
		$properties = array_map( static fn ( array $json ) => PropertyDefinition::fromJson( $json, TestSources::newPropertyTypeRegistry() ), $definitions );
		$schema = new Schema( new SchemaName( 'Person' ), '', new PropertyDefinitions( $properties ), false );
		$before = array_map( static fn ( $p ) => $p->toJson(), $properties );
		$view = GraphFieldPresentation::fromSchema( $schema, 'https://wiki.example/wiki/NEOWIKIGRAPHTITLETOKEN' );
		$this->assertSame( array_keys( $definitions ), array_column( $view['definitions'], 'name' ) );
		$this->assertSame( $definitions['Klassifizierung']['wikiLink'], $view['definitions'][0]['wikiLink'] );
		$this->assertSame( $definitions['Klassifizierung']['externalProperty'], $view['definitions'][0]['externalProperty'] );
		$this->assertSame( 'Tätigkeit', $view['definitions'][1]['predicateLocalName'] );
		$this->assertSame( 'Lokaler_Befund', $view['definitions'][2]['predicateLocalName'] );
		$this->assertSame( 'wikibase-local', $view['definitions'][2]['externalProperty']['source'] );
		$this->assertNull( $view['definitions'][2]['wikiLink'] );
		$this->assertSame( $before, array_map( static fn ( $p ) => $p->toJson(), $properties ) );
	}
	public function testEmptySchemaIsAValidOrderedPresentation(): void {
		$schema = new Schema( new SchemaName( 'Empty' ), '', new PropertyDefinitions( [] ), false );
		$this->assertSame( [ 'version' => 1, 'language' => 'de', 'pageUrlTemplate' => null, 'definitions' => [] ], GraphFieldPresentation::fromSchema( $schema, null ) );
	}
	public function testCurrentOwnImageSnapshotIsExplicitEvenWhenEmpty(): void {
		$property = PropertyDefinition::fromJson( [ 'type' => 'image' ], TestSources::newPropertyTypeRegistry() );
		$schema = new Schema( new SchemaName( 'Person' ), '', new PropertyDefinitions( [ 'Bild' => $property ] ), false );
		$subject = new \ProfessionalWiki\NeoWiki\Domain\Subject\Subject(
			new \ProfessionalWiki\NeoWiki\Domain\Subject\SubjectId( 's1aaaaaaaaaaaa1' ), null,
			\ProfessionalWiki\NeoWiki\Domain\Schema\SchemaReference::local( new SchemaName( 'Person' ) ),
			new \ProfessionalWiki\NeoWiki\Domain\Subject\StatementList( [ new \ProfessionalWiki\NeoWiki\Domain\Statement(
				new \ProfessionalWiki\NeoWiki\Domain\Schema\PropertyName( 'Bild' ), 'image',
				new \ProfessionalWiki\NeoWiki\Domain\Value\StringValue( 'local:Own.jpg' ) ) ] ) );
		$before = $subject->getStatements();
		$view = GraphFieldPresentation::fromSchema( $schema, null, $subject );
		$this->assertSame( [ 'local:Own.jpg' ], $view['definitions'][0]['ownImages'] );
		$this->assertSame( $before, $subject->getStatements() );
		$subject->setStatements( new \ProfessionalWiki\NeoWiki\Domain\Subject\StatementList() );
		$this->assertSame( [], GraphFieldPresentation::fromSchema( $schema, null, $subject )['definitions'][0]['ownImages'] );
	}

	public function testOwnDatePresentationIsCurrentAndUsesIndependentYearTargets(): void {
		$property = PropertyDefinition::fromJson( [ 'type' => 'historicalDate', 'wikiLink' => [ 'dateBceEn' => 'BCE' ] ], TestSources::newPropertyTypeRegistry() );
		$schema = new Schema( new SchemaName( 'Person' ), '', new PropertyDefinitions( [ 'Datum' => $property ] ), false );
		$subject = new \ProfessionalWiki\NeoWiki\Domain\Subject\Subject(
			new \ProfessionalWiki\NeoWiki\Domain\Subject\SubjectId( 's1aaaaaaaaaaaa1' ), null,
			\ProfessionalWiki\NeoWiki\Domain\Schema\SchemaReference::local( new SchemaName( 'Person' ) ),
			new \ProfessionalWiki\NeoWiki\Domain\Subject\StatementList( [ new \ProfessionalWiki\NeoWiki\Domain\Statement(
				new \ProfessionalWiki\NeoWiki\Domain\Schema\PropertyName( 'Datum' ), 'historicalDate',
				new \ProfessionalWiki\NeoWiki\Domain\Value\StringValue( '-0347' ) ) ] ) );
		$view = GraphFieldPresentation::fromSchema( $schema, null, $subject, 'en' );
		$this->assertSame( [ '-0347' ], $view['definitions'][0]['ownDates'] );
		$this->assertSame( [ [ [ 'text' => '347 BCE', 'yearTitle' => '347 v. Chr.' ] ] ], $view['definitions'][0]['ownDateParts'] );
		$subject->setStatements( new \ProfessionalWiki\NeoWiki\Domain\Subject\StatementList() );
		$this->assertSame( [], GraphFieldPresentation::fromSchema( $schema, null, $subject )['definitions'][0]['ownDateParts'] );
	}

}
