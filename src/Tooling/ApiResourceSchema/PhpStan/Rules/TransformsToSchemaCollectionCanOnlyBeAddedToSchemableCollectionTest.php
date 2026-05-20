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
 * @extends RuleTestCase<TransformsToSchemaCollectionCanOnlyBeAddedToSchemableCollection>
 */
class TransformsToSchemaCollectionCanOnlyBeAddedToSchemableCollectionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new TransformsToSchemaCollectionCanOnlyBeAddedToSchemableCollection;
    }

    #[Test]
    public function it_passes_when_transform_trait_is_on_schemable_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/ValidSchemableCollection.php')], []);
    }

    #[Test]
    public function it_fails_when_transform_trait_is_on_non_schemable_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/TransformsToSchemaCollectionOnly.php')], [
            [
                '['.class_basename(TransformsToSchemaCollection::class).'] trait can only be used on implementations of ['.class_basename(SchemableCollection::class).'].',
                12,
            ],
        ]);
    }
}
