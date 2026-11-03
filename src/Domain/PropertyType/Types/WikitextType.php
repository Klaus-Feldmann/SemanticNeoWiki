<?php

declare( strict_types = 1 );
namespace ProfessionalWiki\NeoWiki\Domain\PropertyType\Types;
use ProfessionalWiki\NeoWiki\Domain\Schema\Property\WikitextProperty;
use ProfessionalWiki\NeoWiki\Domain\Schema\PropertyCore;
use ProfessionalWiki\NeoWiki\Domain\Schema\PropertyDefinition;
use ProfessionalWiki\NeoWiki\Domain\Value\NeoValue;

class WikitextType extends TextType {
	public const NAME = 'wikitext';
	public function getTypeName(): string { return self::NAME; }
	public function buildPropertyDefinitionFromJson( PropertyCore $core, array $property ): WikitextProperty {
		return WikitextProperty::fromPartialJson( $core, $property );
	}
	public function validate( NeoValue $value, PropertyDefinition $definition ): array {
		return $definition->isSchemaExecution() ? [] : parent::validate( $value, $definition );
	}
}
