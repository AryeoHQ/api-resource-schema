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
 * @extends RuleTestCase<TransformsToSchemaCanOnlyBeAddedToSchemable>
 */
class TransformsToSchemaCanOnlyBeAddedToSchemableTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new TransformsToSchemaCanOnlyBeAddedToSchemable;
    }

    #[Test]
    public function it_passes_when_transform_trait_is_on_schemable(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/ValidSchemable.php')], []);
    }

    #[Test]
    public function it_fails_when_transform_trait_is_on_non_schemable(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/TransformsToSchemaOnly.php')], [
            [
                '['.class_basename(TransformsToSchema::class).'] trait can only be used on implementations of ['.class_basename(Schemable::class).'].',
                12,
            ],
        ]);
    }
}
