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
 * @extends RuleTestCase<RepresentedByCanOnlyBeAddedToVersionable>
 */
class RepresentedByCanOnlyBeAddedToVersionableTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new RepresentedByCanOnlyBeAddedToVersionable;
    }

    #[Test]
    public function it_passes_when_represented_by_is_on_a_versionable(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/ValidSource.php')], []);
    }

    #[Test]
    public function it_fails_when_represented_by_is_on_a_non_versionable(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/RepresentedByOnly.php')], [
            [
                '['.class_basename(RepresentedBy::class).'] attribute can only be used on implementations of ['.class_basename(Versionable::class).'].',
                10,
            ],
        ]);
    }
}
