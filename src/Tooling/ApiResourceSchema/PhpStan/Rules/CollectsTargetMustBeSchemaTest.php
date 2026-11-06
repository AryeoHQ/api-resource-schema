<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Tests\Fixtures\Tooling\PhpStan\SchemaCollections\ValidSchemaCollection;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<CollectsTargetMustBeSchema>
 */
class CollectsTargetMustBeSchemaTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new CollectsTargetMustBeSchema(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_the_target_is_a_schema(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemaCollections/ValidSchemaCollection.php')], []);
    }

    #[Test]
    public function it_fails_when_the_target_is_not_a_schema(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemaCollections/CollectsWithInvalidTarget.php')], [
            [
                '['.class_basename(Collects::class).'] target ['.class_basename(ValidSchemaCollection::class).'] must implement ['.class_basename(Schema::class).'].',
                12,
            ],
        ]);
    }
}
