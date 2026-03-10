<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<AsSchemaCanOnlyBeAddedToSchema>
 */
class AsSchemaCanOnlyBeAddedToSchemaTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new AsSchemaCanOnlyBeAddedToSchema;
    }

    #[Test]
    public function it_passes_when_as_schema_is_on_schema(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/ValidSchema.php')], []);
    }

    #[Test]
    public function it_fails_when_as_schema_is_not_on_schema(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/AsSchemaOnly.php')], [
            [
                '[AsSchema] trait can only be used on implementations of [Schema].',
                12,
            ],
        ]);
    }
}
