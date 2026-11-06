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
 * @extends RuleTestCase<VersionedEnumMustUseAsVersionedEnum>
 */
class VersionedEnumMustUseAsVersionedEnumTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new VersionedEnumMustUseAsVersionedEnum;
    }

    #[Test]
    public function it_passes_when_versioned_enum_uses_the_trait(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidTarget.php')], []);
    }

    #[Test]
    public function it_fails_when_versioned_enum_does_not_use_the_trait(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/MissingAsVersionedEnum.php')], [
            [
                '['.class_basename(VersionedEnum::class).'] must use the ['.class_basename(AsVersionedEnum::class).'] trait.',
                12,
            ],
        ]);
    }
}
