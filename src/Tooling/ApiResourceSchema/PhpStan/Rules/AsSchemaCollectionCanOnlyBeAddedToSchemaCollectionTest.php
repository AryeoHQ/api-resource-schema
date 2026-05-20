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
 * @extends RuleTestCase<AsSchemaCollectionCanOnlyBeAddedToSchemaCollection>
 */
class AsSchemaCollectionCanOnlyBeAddedToSchemaCollectionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new AsSchemaCollectionCanOnlyBeAddedToSchemaCollection;
    }

    #[Test]
    public function it_passes_when_as_schema_collection_is_on_schema_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemaCollections/ValidSchemaCollection.php')], []);
    }

    #[Test]
    public function it_fails_when_as_schema_collection_is_not_on_schema_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemaCollections/AsSchemaCollectionOnly.php')], [
            [
                '['.class_basename(AsSchemaCollection::class).'] trait can only be used on implementations of ['.class_basename(SchemaCollection::class).'].',
                12,
            ],
        ]);
    }
}
