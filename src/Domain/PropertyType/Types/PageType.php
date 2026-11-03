<?php

declare( strict_types = 1 );
namespace ProfessionalWiki\NeoWiki\Domain\PropertyType\Types;

use ProfessionalWiki\NeoWiki\Domain\Schema\Property\PageProperty;
use ProfessionalWiki\NeoWiki\Domain\Schema\PropertyCore;
use ProfessionalWiki\NeoWiki\Domain\Schema\PropertyDefinition;
use ProfessionalWiki\NeoWiki\Domain\Validation\Violation;
use ProfessionalWiki\NeoWiki\Domain\Validation\Severity;
use ProfessionalWiki\NeoWiki\Domain\Value\NeoValue;
use ProfessionalWiki\NeoWiki\Domain\Value\StringValue;

class PageType extends TextType {
	public const NAME = 'page';

	public function getTypeName(): string { return self::NAME; }

	public function buildPropertyDefinitionFromJson( PropertyCore $core, array $property ): PageProperty {
		return PageProperty::fromPartialJson( $core, $property );
	}

	public function validate( NeoValue $value, PropertyDefinition $definition ): array {
		$violations = parent::validate( $value, $definition );
		if ( $value instanceof StringValue && $definition instanceof PageProperty ) {
			foreach ( $value->strings as $index => $part ) {
				if ( trim( $part ) !== '' && LocalPageTitle::normalize( $part ) === null ) {
					$violations[] = new Violation( propertyName: null, code: 'invalid-page', valuePartIndex: $index,
						severity: Severity::Error );
				}
			}
		}
		return $violations;
	}
}
