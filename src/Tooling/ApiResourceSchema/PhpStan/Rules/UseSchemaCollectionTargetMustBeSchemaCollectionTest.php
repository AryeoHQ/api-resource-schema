<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchema;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<UseSchemaCollectionTargetMustBeSchemaCollection>
 */
class UseSchemaCollectionTargetMustBeSchemaCollectionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new UseSchemaCollectionTargetMustBeSchemaCollection(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_every_target_is_a_schema_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/ValidSchemableCollectionWithParity.php')], []);
    }

    #[Test]
    public function it_fails_when_a_target_is_not_a_schema_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/UseSchemaCollectionWithInvalidTarget.php')], [
            [
                '['.class_basename(UseSchemaCollection::class).'] target ['.class_basename(ValidSchema::class).'] must implement ['.class_basename(SchemaCollection::class).'].',
                13,
            ],
        ]);
    }
}
