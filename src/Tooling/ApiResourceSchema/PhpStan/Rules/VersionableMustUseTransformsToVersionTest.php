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
 * @extends RuleTestCase<VersionableMustUseTransformsToVersion>
 */
class VersionableMustUseTransformsToVersionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new VersionableMustUseTransformsToVersion;
    }

    #[Test]
    public function it_passes_when_versionable_uses_the_trait(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidSource.php')], []);
    }

    #[Test]
    public function it_fails_when_versionable_does_not_use_the_trait(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/MissingTransformsToVersion.php')], [
            [
                '['.class_basename(Versionable::class).'] must use the ['.class_basename(TransformsToVersion::class).'] trait.',
                9,
            ],
        ]);
    }
}
