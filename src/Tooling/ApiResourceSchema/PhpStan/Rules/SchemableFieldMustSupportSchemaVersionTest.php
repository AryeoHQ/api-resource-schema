<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Tests\Fixtures\Tooling\PhpStan\Schemables\CollectedByWithAdditionalVersion;
use Tests\Fixtures\Tooling\PhpStan\Schemas\SchemaWithUnsupportedSchemableFields;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemableFieldMustSupportSchemaVersion>
 */
class SchemableFieldMustSupportSchemaVersionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemableFieldMustSupportSchemaVersion(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_the_field_supports_the_schema_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/ValidSchema.php')], []);
    }

    #[Test]
    public function it_fails_when_the_field_has_no_schema_for_the_schema_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/SchemaWithUnsupportedSchemableFields.php')], [
            [
                '['.class_basename(CollectedByWithAdditionalVersion::class).'] has no ['.class_basename(UseSchema::class).'] schema for version ['.ApiVersion::V1->value.'] required by ['.class_basename(SchemaWithUnsupportedSchemableFields::class).'].',
                21,
            ],
        ]);
    }
}
