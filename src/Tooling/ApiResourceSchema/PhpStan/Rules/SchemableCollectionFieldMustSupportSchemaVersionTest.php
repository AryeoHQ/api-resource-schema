<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Tests\Fixtures\Tooling\PhpStan\SchemableCollections\WithMissingVersion;
use Tests\Fixtures\Tooling\PhpStan\Schemas\SchemaWithUnsupportedSchemableFields;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemableCollectionFieldMustSupportSchemaVersion>
 */
class SchemableCollectionFieldMustSupportSchemaVersionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemableCollectionFieldMustSupportSchemaVersion(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_the_field_supports_the_schema_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/ValidSchema.php')], []);
    }

    #[Test]
    public function it_fails_when_the_field_has_no_schema_collection_for_the_schema_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/SchemaWithUnsupportedSchemableFields.php')], [
            [
                '['.class_basename(WithMissingVersion::class).'] has no ['.class_basename(UseSchemaCollection::class).'] schema collection for version ['.class_basename(ApiVersion::class).'::'.ApiVersion::V1->name.'] required by ['.class_basename(SchemaWithUnsupportedSchemableFields::class).'].',
                23,
            ],
        ]);
    }
}
