<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemableMustDefineCollectedBy>
 */
class SchemableMustDefineCollectedByTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemableMustDefineCollectedBy;
    }

    #[Test]
    public function it_passes_when_schemable_has_collected_by(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/ValidSchemable.php')], []);
    }

    #[Test]
    public function it_fails_when_schemable_does_not_have_collected_by(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/MissingCollectedBy.php')], [
            [
                '['.class_basename(Schemable::class).'] must have the ['.class_basename(CollectedBy::class).'] attribute.',
                14,
            ],
        ]);
    }
}
