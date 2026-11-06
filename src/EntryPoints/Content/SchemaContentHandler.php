<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\NeoWiki\EntryPoints\Content;

use InvalidArgumentException;
use MediaWiki\Content\Content;
use MediaWiki\Content\JsonContentHandler;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Content\ValidationParams;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use MediaWiki\Parser\ParserOutput;
use ProfessionalWiki\NeoWiki\Domain\Schema\DependentSubjectSchemaRules;
use ProfessionalWiki\NeoWiki\Domain\Schema\PropertyNameRules;
use ProfessionalWiki\NeoWiki\Domain\Schema\Schema;
use ProfessionalWiki\NeoWiki\Domain\Schema\SchemaName;
use ProfessionalWiki\NeoWiki\NeoWikiExtension;
use ProfessionalWiki\NeoWiki\Persistence\MediaWiki\SchemaContentValidator;
use ProfessionalWiki\NeoWiki\Presentation\PropertyNameProblemMessage;
use StatusValue;

class SchemaContentHandler extends JsonContentHandler {

	use ReportsValidationErrors;

	protected function getContentClass(): string {
		return SchemaContent::class;
	}

	public function validateSave( Content $content, ValidationParams $validationParams ): StatusValue {
		$status = parent::validateSave( $content, $validationParams );

		if ( !$status->isOK() ) {
			return $status;
		}

		$title = Title::newFromPageIdentity( $validationParams->getPageIdentity() );

		try {
			$schemaName = new SchemaName( $title->getText() );
		} catch ( InvalidArgumentException $exception ) {
			$schemaName = null;
			$status->fatal( 'neowiki-schema-name-invalid', $exception->getMessage() );
		}

		$validator = SchemaContentValidator::newInstance();

		if ( !$validator->validate( $content->getText() ) ) {
			$this->reportValidationErrors(
				$status,
				'neowiki-schema-invalid',
				'neowiki-schema-invalid-detail',
				$validator->getErrors()
			);
		}

		if ( $status->isOK() ) {
			$this->checkPropertyNames( $status, $content->getText() );
		}

		if ( $schemaName !== null && $status->isOK() ) {
			$this->checkDependentSubjectRules(
				$status,
				NeoWikiExtension::getInstance()->getPersistenceSchemaDeserializer()->deserialize( $schemaName, $content->getText() )
			);
		}

        if ($status->isOK() && class_exists(\NeoWikiBaseBridge\SchemaPropertyBindings::class)) {
            try {
                $old=\MediaWiki\MediaWikiServices::getInstance()->getRevisionLookup()->getRevisionByTitle($title);
                $oldContent=$old?->getContent('main');
                $previous=$oldContent instanceof SchemaContent ? json_decode($oldContent->getText(),true) : [];
                \NeoWikiBaseBridge\SchemaPropertyBindings::validate(json_decode($content->getText(),true),$previous ?? [],\MediaWiki\Context\RequestContext::getMain());
            } catch (\Throwable $e) {
                $status->fatal('neowiki-property-datatype-invalid',$e->getMessage());
            }
        }
        return $status;
    }

    /**
	 * Read from the JSON rather than from the Schema it holds, which leaves out integer names.
	 */
	private function checkPropertyNames( StatusValue $status, string $json ): void {
		/** @var array{propertyDefinitions: array<array-key, mixed>} $schema */
		$schema = json_decode( $json, true );
		$problems = PropertyNameRules::problemsInSchema(
			array_map( strval( ... ), array_keys( $schema['propertyDefinitions'] ) )
		);

		if ( $problems !== [] ) {
			$this->reportErrors(
				$status,
				'neowiki-schema-invalid',
				array_map( PropertyNameProblemMessage::from( ... ), $problems )
			);
		}
	}

	private function checkDependentSubjectRules( StatusValue $status, Schema $schema ): void {
		$rules = new DependentSubjectSchemaRules( NeoWikiExtension::getInstance()->getSchemaResolver() );

		if ( $rules->changesDeclaration( $schema ) ) {
			$status->fatal( 'neowiki-schema-dependence-unchangeable' );
			return;
		}

		$mixingProperties = $rules->propertiesMixingTargetSchemas( $schema );

		if ( $mixingProperties !== [] ) {
			$status->fatal(
				'neowiki-schema-relation-targets-mixed',
				Message::listParam( $mixingProperties ),
				count( $mixingProperties )
			);
		}
	}

	protected function fillParserOutput(
		Content $content,
		ContentParseParams $cpoParams,
		ParserOutput &$parserOutput
	): void {
		$parserOutput->setContentHolderText( '' );
	}

	public function makeEmptyContent(): SchemaContent {
		return new SchemaContent( <<<JSON
{
	"propertyDefinitions": {

	}
}
JSON
		);
	}

	public function canBeUsedOn( Title $title ): bool {
		return $title->getNamespace() === NeoWikiExtension::NS_SCHEMA;
	}

}
