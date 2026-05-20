<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemaCollectionMustUseAsSchemaCollection>
 */
class SchemaCollectionMustUseAsSchemaCollectionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemaCollectionMustUseAsSchemaCollection;
    }

    #[Test]
    public function it_passes_when_schema_collection_uses_as_schema_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemaCollections/ValidSchemaCollection.php')], []);
    }

    #[Test]
    public function it_fails_when_schema_collection_does_not_use_as_schema_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemaCollections/MissingAsSchemaCollection.php')], [
            [
                '['.class_basename(SchemaCollection::class).'] must use the ['.class_basename(AsSchemaCollection::class).'] trait.',
                10,
            ],
        ]);
    }
}
