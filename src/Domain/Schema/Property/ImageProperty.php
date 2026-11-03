<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\NeoWiki\Domain\Schema\Property;

use ProfessionalWiki\NeoWiki\Domain\Schema\PropertyCore;
use ProfessionalWiki\NeoWiki\Domain\Schema\PropertyDefinition;
use ProfessionalWiki\NeoWiki\Domain\PropertyType\Types\ImageType;

class ImageProperty extends PropertyDefinition {

	public function __construct(
		PropertyCore $core,
		private readonly bool $multiple,
		private readonly bool $uniqueItems,
		private readonly string $imageSource = 'local',
	) {
		parent::__construct( $core );
	}

	public function getPropertyType(): string {
		return ImageType::NAME;
	}

	public function allowsMultipleValues(): bool {
		return $this->multiple;
	}

	public function enforcesUniqueValues(): bool {
		return $this->uniqueItems;
	}

	public static function fromPartialJson( PropertyCore $core, array $property ): self {
		$source = $property['imageSource'] ?? $core->externalProperty?->source ?? 'local';
		if ( !in_array( $source, [ 'wikidata', 'wikibase-local', 'local', 'commons' ], true ) ) {
			throw new \InvalidArgumentException( 'Invalid image source' );
		}
		return new self(
			core: $core,
			multiple: $property['multiple'] ?? false,
			uniqueItems: $property['uniqueItems'] ?? false,
			imageSource: $source,
		);
	}

	public function nonCoreToJson(): array {
		return [
			'imageSource' => $this->imageSource,
			'multiple' => $this->allowsMultipleValues(),
			'uniqueItems' => $this->enforcesUniqueValues(),
		];
	}

	public function toJsonSchema(): array {
		return $this->listValueSchema( [ 'type' => 'string', 'pattern' => ImageType::REFERENCE_PATTERN ] )
			+ ( $this->enforcesUniqueValues() ? [ 'uniqueItems' => true ] : [] );
	}

}
