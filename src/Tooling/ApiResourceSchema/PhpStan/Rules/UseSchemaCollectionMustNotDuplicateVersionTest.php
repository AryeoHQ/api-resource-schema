<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<UseSchemaCollectionMustNotDuplicateVersion>
 */
class UseSchemaCollectionMustNotDuplicateVersionTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new UseSchemaCollectionMustNotDuplicateVersion(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_each_schema_collection_has_a_distinct_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/ValidSchemableCollectionWithParity.php')], []);
    }

    #[Test]
    public function it_fails_when_two_schema_collections_share_a_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemableCollections/DuplicateSchemaCollectionVersions.php')], [
            [
                '['.class_basename(UseSchemaCollection::class).'] declares more than one schema collection for version ['.class_basename(ApiVersion::class).'::'.ApiVersion::V1->name.'].',
                15,
            ],
        ]);
    }
}
