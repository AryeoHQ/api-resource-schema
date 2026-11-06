<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Contracts\SchemableCollection;
use Tests\Fixtures\Tooling\PhpStan\Schemables\CollectedByWithMissingVersion;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<UseSchemaMustHaveMatchingUseSchemaCollection>
 */
class UseSchemaMustHaveMatchingUseSchemaCollectionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new UseSchemaMustHaveMatchingUseSchemaCollection(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_all_versions_match(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/ValidSchemable.php')], []);
    }

    #[Test]
    public function it_fails_when_collection_is_missing_a_schema_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/CollectedByWithMissingVersion.php')], [
            [
                '['.class_basename(SchemableCollection::class).'] is missing #['.class_basename(UseSchemaCollection::class).'] for version ['.class_basename(ApiVersion::class).'::'.ApiVersion::V1->name.'] declared by ['.class_basename(CollectedByWithMissingVersion::class).'].',
                14,
            ],
        ]);
    }

    #[Test]
    public function it_skips_when_no_collected_by_attribute(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/MissingCollectedBy.php')], []);
    }
}
