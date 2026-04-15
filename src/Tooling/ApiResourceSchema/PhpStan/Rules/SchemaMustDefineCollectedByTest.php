<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemaMustDefineCollectedBy>
 */
class SchemaMustDefineCollectedByTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemaMustDefineCollectedBy;
    }

    #[Test]
    public function it_passes_when_schema_has_collected_by(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/ValidSchemaWithCollectedBy.php')], []);
    }

    #[Test]
    public function it_fails_when_schema_does_not_have_collected_by(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/MissingCollectedBy.php')], [
            [
                '[Schema] must have the [CollectedBy] attribute.',
                11,
            ],
        ]);
    }
}
