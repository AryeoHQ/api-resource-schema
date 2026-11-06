<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<TransformsToVersionCanOnlyBeAddedToVersionable>
 */
class TransformsToVersionCanOnlyBeAddedToVersionableTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new TransformsToVersionCanOnlyBeAddedToVersionable;
    }

    #[Test]
    public function it_passes_when_the_trait_is_on_a_versionable(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidSource.php')], []);
    }

    #[Test]
    public function it_fails_when_the_trait_is_on_a_non_versionable(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/TransformsToVersionOnly.php')], [
            [
                '['.class_basename(TransformsToVersion::class).'] trait can only be used on implementations of ['.class_basename(Versionable::class).'].',
                9,
            ],
        ]);
    }
}
