<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemableMustUseTransformsToSchema>
 */
class SchemableMustUseTransformsToSchemaTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemableMustUseTransformsToSchema;
    }

    #[Test]
    public function it_passes_when_schemable_uses_transform_trait(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/ValidSchemable.php')], []);
    }

    #[Test]
    public function it_fails_when_schemable_does_not_use_transform_trait(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/MissingTransformsToSchema.php')], [
            [
                '['.class_basename(Schemable::class).'] must use the ['.class_basename(TransformsToSchema::class).'] trait.',
                10,
            ],
        ]);
    }
}
