<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
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
                '[SchemaCollection] must use the [AsSchemaCollection] trait.',
                10,
            ],
        ]);
    }
}
