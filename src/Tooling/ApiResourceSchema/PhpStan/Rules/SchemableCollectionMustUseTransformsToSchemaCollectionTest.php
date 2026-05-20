<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemableCollectionMustUseTransformsToSchemaCollection>
 */
class SchemableCollectionMustUseTransformsToSchemaCollectionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemableCollectionMustUseTransformsToSchemaCollection;
    }

    #[Test]
    public function it_passes_when_schemable_collection_uses_transform_trait(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/ValidSchemableCollection.php')], []);
    }

    #[Test]
    public function it_fails_when_schemable_collection_does_not_use_transform_trait(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/MissingTransformsToSchemaCollection.php')], [
            [
                '['.class_basename(SchemableCollection::class).'] must use the ['.class_basename(TransformsToSchemaCollection::class).'] trait.',
                10,
            ],
        ]);
    }
}
