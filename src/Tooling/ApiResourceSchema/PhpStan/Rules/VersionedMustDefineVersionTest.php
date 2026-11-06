<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Fields\Contracts\Versioned;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<VersionedMustDefineVersion>
 */
class VersionedMustDefineVersionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new VersionedMustDefineVersion;
    }

    #[Test]
    public function it_passes_when_versioned_has_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidTarget.php')], []);
    }

    #[Test]
    public function it_fails_when_versioned_does_not_have_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/TargetWithoutVersion.php')], [
            [
                '['.class_basename(Versioned::class).'] must have the ['.class_basename(Version::class).'] attribute.',
                10,
            ],
        ]);
    }
}
