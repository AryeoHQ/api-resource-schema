<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<UseSchemaMustNotDuplicateVersion>
 */
class UseSchemaMustNotDuplicateVersionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new UseSchemaMustNotDuplicateVersion(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_each_schema_has_a_distinct_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/ValidSchemable.php')], []);
    }

    #[Test]
    public function it_fails_when_two_schemas_share_a_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/DuplicateSchemaVersions.php')], [
            [
                '['.class_basename(UseSchema::class).'] declares more than one schema for version ['.class_basename(ApiVersion::class).'::'.ApiVersion::V1->name.'].',
                15,
            ],
        ]);
    }
}
