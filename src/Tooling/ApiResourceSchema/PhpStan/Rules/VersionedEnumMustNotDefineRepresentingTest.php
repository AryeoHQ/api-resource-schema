<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<VersionedEnumMustNotDefineRepresenting>
 */
class VersionedEnumMustNotDefineRepresentingTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new VersionedEnumMustNotDefineRepresenting;
    }

    #[Test]
    public function it_passes_when_versioned_enum_maps_with_attributes(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidTarget.php')], []);
    }

    #[Test]
    public function it_fails_when_versioned_enum_defines_representing(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/TargetDefiningRepresenting.php')], [
            [
                '['.class_basename(VersionedEnum::class).'] must map its cases with the ['.class_basename(Represents::class).'] attribute instead of defining [representing()].',
                17,
            ],
        ]);
    }
}
