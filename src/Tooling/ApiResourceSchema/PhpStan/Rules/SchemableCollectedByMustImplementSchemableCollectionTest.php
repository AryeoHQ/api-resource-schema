<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tests\Fixtures\Tooling\PhpStan\SchemableCollections\PlainCustomCollection;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemableCollectedByMustImplementSchemableCollection>
 */
class SchemableCollectedByMustImplementSchemableCollectionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemableCollectedByMustImplementSchemableCollection(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion()),
        );
    }

    #[Test]
    public function it_passes_when_collection_implements_schemable_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/ValidSchemable.php')], []);
    }

    #[Test]
    public function it_fails_when_collection_does_not_implement_schemable_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/SchemableWithNonSchemableCollection.php')], [
            [
                '['.class_basename(PlainCustomCollection::class).'] must implement ['.class_basename(SchemableCollection::class).'].',
                9,
            ],
        ]);
    }

    #[Test]
    public function it_skips_when_no_collected_by_attribute(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/MissingCollectedBy.php')], []);
    }

    #[Test]
    public function it_skips_when_collected_by_is_base_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/SchemableWithBaseCollection.php')], []);
    }
}
