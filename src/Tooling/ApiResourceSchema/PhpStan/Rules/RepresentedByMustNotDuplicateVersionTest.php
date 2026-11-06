<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<RepresentedByMustNotDuplicateVersion>
 */
class RepresentedByMustNotDuplicateVersionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new RepresentedByMustNotDuplicateVersion(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_each_target_has_a_distinct_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidSource.php')], []);
    }

    #[Test]
    public function it_passes_when_targets_share_a_value_from_different_version_enums(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/SharedValueVersions.php')], []);
    }

    #[Test]
    public function it_fails_when_two_targets_share_a_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/DuplicateVersions.php')], [
            [
                '['.class_basename(RepresentedBy::class).'] declares more than one target for version ['.class_basename(ApiVersion::class).'::'.ApiVersion::V1->name.'].',
                12,
            ],
        ]);
    }
}
