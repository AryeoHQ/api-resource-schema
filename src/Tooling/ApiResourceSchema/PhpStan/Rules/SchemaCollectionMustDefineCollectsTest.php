<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemaCollectionMustDefineCollects>
 */
class SchemaCollectionMustDefineCollectsTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemaCollectionMustDefineCollects;
    }

    #[Test]
    public function it_passes_when_schema_collection_defines_collects(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemaCollections/ValidSchemaCollection.php')], []);
    }

    #[Test]
    public function it_fails_when_schema_collection_does_not_define_collects(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemaCollections/MissingCollects.php')], [
            [
                '[SchemaCollection] must have the [Collects] attribute.',
                11,
            ],
        ]);
    }
}
