<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Contracts\Versioned;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<EnumImplementingVersionedMustImplementVersionedEnum>
 */
class EnumImplementingVersionedMustImplementVersionedEnumTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new EnumImplementingVersionedMustImplementVersionedEnum;
    }

    #[Test]
    public function it_passes_when_the_enum_implements_versioned_enum(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidTarget.php')], []);
    }

    #[Test]
    public function it_fails_when_the_enum_implements_only_versioned(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/VersionedOnlyEnum.php')], [
            [
                'An enum implementing ['.class_basename(Versioned::class).'] must implement ['.class_basename(VersionedEnum::class).'].',
                12,
            ],
        ]);
    }
}
