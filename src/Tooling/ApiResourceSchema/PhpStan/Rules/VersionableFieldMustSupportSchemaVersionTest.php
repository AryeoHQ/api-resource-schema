<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Tests\Fixtures\Tooling\PhpStan\Versionables\IncompleteSource;
use Tests\Fixtures\Tooling\PhpStan\Versionables\SchemaWithUnsupportedVersionableField;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<VersionableFieldMustSupportSchemaVersion>
 */
class VersionableFieldMustSupportSchemaVersionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new VersionableFieldMustSupportSchemaVersion(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_the_field_supports_the_schema_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/ValidSchema.php')], []);
    }

    #[Test]
    public function it_fails_when_the_field_has_no_target_for_the_schema_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Versionables/SchemaWithUnsupportedVersionableField.php')], [
            [
                '['.class_basename(IncompleteSource::class).'] has no ['.class_basename(RepresentedBy::class).'] target for version ['.ApiVersion::V2->value.'] required by ['.class_basename(SchemaWithUnsupportedVersionableField::class).'].',
                19,
            ],
        ]);
    }
}
