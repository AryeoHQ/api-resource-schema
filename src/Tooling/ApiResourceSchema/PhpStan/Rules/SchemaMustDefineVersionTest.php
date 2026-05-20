<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemaMustDefineVersion>
 */
class SchemaMustDefineVersionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemaMustDefineVersion;
    }

    #[Test]
    public function it_passes_when_schema_has_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/ValidSchema.php')], []);
    }

    #[Test]
    public function it_fails_when_schema_does_not_have_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/MissingVersion.php')], [
            [
                '['.class_basename(Schema::class).'] must have the ['.class_basename(Version::class).'] attribute.',
                14,
            ],
        ]);
    }
}
