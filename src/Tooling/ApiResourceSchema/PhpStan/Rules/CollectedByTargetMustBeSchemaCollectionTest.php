<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Tests\Fixtures\Tooling\PhpStan\Schemas\ValidSchemaV2;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<CollectedByTargetMustBeSchemaCollection>
 */
class CollectedByTargetMustBeSchemaCollectionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new CollectedByTargetMustBeSchemaCollection(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_the_target_is_a_schema_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/ValidSchemaWithCollectedBy.php')], []);
    }

    #[Test]
    public function it_fails_when_the_target_is_not_a_schema_collection(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/CollectedByWithInvalidTarget.php')], [
            [
                '['.class_basename(CollectedBy::class).'] target ['.class_basename(ValidSchemaV2::class).'] must implement ['.class_basename(SchemaCollection::class).'].',
                15,
            ],
        ]);
    }
}
