<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemableCollectedByMustNotBeBaseCollection>
 */
class SchemableCollectedByMustNotBeBaseCollectionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemableCollectedByMustNotBeBaseCollection(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_collected_by_is_custom_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/ValidSchemable.php')], []);
    }

    #[Test]
    public function it_fails_when_collected_by_is_base_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/SchemableWithBaseCollection.php')], [
            [
                '['.class_basename(Schemable::class).'] ['.class_basename(CollectedBy::class).'] must not be the base Eloquent Collection.',
                15,
            ],
        ]);
    }

    #[Test]
    public function it_skips_when_no_collected_by_attribute(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/MissingCollectedBy.php')], []);
    }
}
