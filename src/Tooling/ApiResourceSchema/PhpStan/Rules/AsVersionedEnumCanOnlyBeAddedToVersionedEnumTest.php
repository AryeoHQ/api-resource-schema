<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<AsVersionedEnumCanOnlyBeAddedToVersionedEnum>
 */
class AsVersionedEnumCanOnlyBeAddedToVersionedEnumTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new AsVersionedEnumCanOnlyBeAddedToVersionedEnum;
    }

    #[Test]
    public function it_passes_when_the_trait_is_on_a_versioned_enum(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidTarget.php')], []);
    }

    #[Test]
    public function it_fails_when_the_trait_is_on_an_enum_that_is_not_versioned(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/AsVersionedEnumOnly.php')], [
            [
                '['.class_basename(AsVersionedEnum::class).'] trait can only be used on implementations of ['.class_basename(VersionedEnum::class).'].',
                9,
            ],
        ]);
    }
}
