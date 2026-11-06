<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Contracts\Versioned;
use Tests\Fixtures\Tooling\PhpStan\Versionables\PlainEnum;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<RepresentedByTargetMustBeVersioned>
 */
class RepresentedByTargetMustBeVersionedTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new RepresentedByTargetMustBeVersioned(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_every_target_is_versioned(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidSource.php')], []);
    }

    #[Test]
    public function it_ignores_targets_that_are_not_class_references(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ConstantTarget.php')], []);
    }

    #[Test]
    public function it_fails_when_a_target_is_not_versioned(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/InvalidTargets.php')], [
            [
                '['.class_basename(RepresentedBy::class).'] target ['.class_basename(PlainEnum::class).'] must implement ['.class_basename(Versioned::class).'].',
                11,
            ],
        ]);
    }
}
