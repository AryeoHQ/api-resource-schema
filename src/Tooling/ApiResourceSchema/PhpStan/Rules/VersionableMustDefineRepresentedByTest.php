<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<VersionableMustDefineRepresentedBy>
 */
class VersionableMustDefineRepresentedByTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new VersionableMustDefineRepresentedBy;
    }

    #[Test]
    public function it_passes_when_versionable_defines_represented_by(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidSource.php')], []);
    }

    #[Test]
    public function it_skips_abstract_classes(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/MissingTransformsToVersion.php')], []);
    }

    #[Test]
    public function it_fails_when_versionable_does_not_define_represented_by(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/MissingRepresentedBy.php')], [
            [
                '['.class_basename(Versionable::class).'] must have the ['.class_basename(RepresentedBy::class).'] attribute.',
                10,
            ],
        ]);
    }
}
