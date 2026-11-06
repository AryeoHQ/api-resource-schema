<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Tests\Fixtures\Tooling\PhpStan\SchemaCollections\ValidSchemaCollection;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<UseSchemaTargetMustBeSchema>
 */
class UseSchemaTargetMustBeSchemaTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new UseSchemaTargetMustBeSchema(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_every_target_is_a_schema(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/ValidSchemable.php')], []);
    }

    #[Test]
    public function it_fails_when_a_target_is_not_a_schema(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/UseSchemaWithInvalidTarget.php')], [
            [
                '['.class_basename(UseSchema::class).'] target ['.class_basename(ValidSchemaCollection::class).'] must implement ['.class_basename(Schema::class).'].',
                13,
            ],
        ]);
    }
}
