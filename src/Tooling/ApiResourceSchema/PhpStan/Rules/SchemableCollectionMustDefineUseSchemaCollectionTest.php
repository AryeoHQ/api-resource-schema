<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemableCollectionMustDefineUseSchemaCollection>
 */
class SchemableCollectionMustDefineUseSchemaCollectionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemableCollectionMustDefineUseSchemaCollection;
    }

    #[Test]
    public function it_passes_when_schemable_collection_defines_use_schema_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/ValidSchemableCollectionWithParity.php')], []);
    }

    #[Test]
    public function it_skips_abstract_classes(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/MissingTransformsToSchemaCollection.php')], []);
    }

    #[Test]
    public function it_fails_when_schemable_collection_does_not_define_use_schema_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/MissingUseSchemaCollection.php')], [
            [
                '['.class_basename(SchemableCollection::class).'] must have the ['.class_basename(UseSchemaCollection::class).'] attribute.',
                11,
            ],
        ]);
    }
}
