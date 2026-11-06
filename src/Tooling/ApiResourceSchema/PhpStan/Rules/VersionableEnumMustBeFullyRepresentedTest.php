<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\PhpStan\Versionables\IncompleteSource;
use Tests\Fixtures\Tooling\PhpStan\Versionables\IncompleteTarget;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<VersionableEnumMustBeFullyRepresented>
 */
class VersionableEnumMustBeFullyRepresentedTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new VersionableEnumMustBeFullyRepresented(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_every_case_is_represented_once_per_target(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidSource.php')], []);
    }

    #[Test]
    public function it_fails_when_a_case_is_unrepresented_or_represented_twice(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/IncompleteSource.php')], [
            [
                '['.class_basename(IncompleteSource::class).'::'.IncompleteSource::Draft->name.'] is represented by more than one case of ['.class_basename(IncompleteTarget::class).'].',
                11,
            ],
            [
                '['.class_basename(IncompleteTarget::class).'] does not represent ['.class_basename(IncompleteSource::class).'::'.IncompleteSource::Open->name.'].',
                11,
            ],
        ]);
    }
}
