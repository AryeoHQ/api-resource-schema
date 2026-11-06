<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemableMustDefineUseSchema>
 */
class SchemableMustDefineUseSchemaTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemableMustDefineUseSchema;
    }

    #[Test]
    public function it_passes_when_schemable_defines_use_schema(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/ValidSchemable.php')], []);
    }

    #[Test]
    public function it_skips_abstract_classes(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/MissingTransformsToSchema.php')], []);
    }

    #[Test]
    public function it_fails_when_schemable_does_not_define_use_schema(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/MissingUseSchema.php')], [
            [
                '['.class_basename(Schemable::class).'] must have the ['.class_basename(UseSchema::class).'] attribute.',
                14,
            ],
        ]);
    }
}
