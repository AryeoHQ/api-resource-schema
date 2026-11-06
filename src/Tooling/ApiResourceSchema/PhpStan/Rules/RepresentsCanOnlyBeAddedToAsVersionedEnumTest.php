<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<RepresentsCanOnlyBeAddedToAsVersionedEnum>
 */
class RepresentsCanOnlyBeAddedToAsVersionedEnumTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new RepresentsCanOnlyBeAddedToAsVersionedEnum;
    }

    #[Test]
    public function it_passes_when_represents_is_on_an_as_versioned_enum(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidTarget.php')], []);
    }

    #[Test]
    public function it_fails_when_represents_is_on_an_enum_without_as_versioned_enum(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/RepresentsWithoutAsVersionedEnum.php')], [
            [
                '['.class_basename(Represents::class).'] attribute can only be used on cases of an enum using the ['.class_basename(AsVersionedEnum::class).'] trait.',
                11,
            ],
        ]);
    }
}
