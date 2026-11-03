<?php

declare( strict_types = 1 );
namespace ProfessionalWiki\NeoWiki\Tests\Domain\Schema\Property;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\NeoWiki\Domain\Schema\Property\WikitextProperty;
use ProfessionalWiki\NeoWiki\Domain\Schema\PropertyCore;
use ProfessionalWiki\NeoWiki\Domain\PropertyType\Types\WikitextType;
use ProfessionalWiki\NeoWiki\Domain\Value\StringValue;
use ProfessionalWiki\NeoWiki\Persistence\MediaWiki\SchemaContentValidator;

class WikitextPropertyTest extends TestCase {
	public function testSchemaProgramIsSeparateFromSubjectValueAndSurvivesSerialization(): void {
		$p = WikitextProperty::fromPartialJson( new PropertyCore( '', true, [ '{{AlterStandard}}' ] ), [ 'execute' => true, 'wikitext' => " {{NeoWiki}}\n" ] );
		$this->assertTrue( $p->isSchemaExecution() );
		$this->assertFalse( $p->isRequired() );
		$this->assertSame( " {{NeoWiki}}\n", $p->toJson()['wikitext'] );
		$this->assertSame( [], ( new WikitextType() )->validate( new StringValue( '{{NichtAusfuehren}}' ), $p ) );
	}
	public function testOrdinaryWikitextRetainsTextConstraints(): void {
		$p = WikitextProperty::fromPartialJson( new PropertyCore( '', true, null ), [ 'minLength' => 4 ] );
		$this->assertFalse( $p->isSchemaExecution() );
		$this->assertCount( 1, ( new WikitextType() )->validate( new StringValue( 'Ab' ), $p ) );
		$this->assertCount( 1, ( new WikitextType() )->validate( new StringValue(), $p ) );
	}
	public function testSchemaValidatorRejectsEmptyOrMalformedExecutionPrograms(): void {
		$validator = SchemaContentValidator::newInstance();
		foreach ( [ [ 'execute' => true ], [ 'execute' => true, 'wikitext' => ' ' ], [ 'execute' => 'true', 'wikitext' => '{{X}}' ] ] as $bad ) {
			$this->assertFalse( $validator->validate( json_encode( [ 'propertyDefinitions' => [ 'Auswertung' => [ 'type' => 'wikitext' ] + $bad ] ] ) ) );
		}
		$this->assertTrue( $validator->validate( '{"propertyDefinitions":{"Auswertung":{"type":"wikitext","execute":true,"wikitext":"{{NeoWiki}}"}}}' ) );
		$this->assertTrue( $validator->validate( '{"propertyDefinitions":{"Notiz":{"type":"wikitext","execute":false}}}' ) );
	}
}
