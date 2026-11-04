<?php

declare(strict_types=1);
namespace ProfessionalWiki\NeoWiki\Application\Semantic;
use ProfessionalWiki\NeoWiki\Domain\Schema\Schema;
use ProfessionalWiki\NeoWiki\Domain\Subject\Subject;
use ProfessionalWiki\NeoWiki\EntryPoints\Scribunto\SchemaLuaSerializer;

/** Converts authorized domain objects. Authorization/dependency recording stays with the caller. */
final class FieldSnapshot {
    public static function reader(Schema $schema, Subject $subject, \Closure $external, ?\Closure $displayValues = null): FieldValues {
        // The serializer is PHP code, not a Lua invocation; preserve the established schema contract.
        $definitions = (new SchemaLuaSerializer())->toLuaTable($schema);
        $definitions['properties'] = array_values($definitions['properties']);
        foreach ($definitions['properties'] as &$property) {
            if (isset($property['options'])) { $property['options'] = array_values($property['options']); }
        }
        unset($property);
        $statements = [];
        foreach ($subject->getStatements()->asArray() as $statement) {
            $raw = $statement->getValue()->toScalars();
            $parts = is_array($raw) && array_is_list($raw) ? $raw : [$raw];
            if ($displayValues !== null && in_array($statement->getPropertyType(), ['relation','monolingualText'], true)) {
                $parts = array_values($displayValues($statement->getPropertyName()->text) ?? []);
            }
            $statements[$statement->getPropertyName()->text] = ['values'=>$parts];
        }
        return new FieldValues($definitions,['id'=>$subject->id->text,'schema'=>$subject->getSchemaReference()->toJson(),'statements'=>$statements],$external);
    }
}
