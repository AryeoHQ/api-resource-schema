<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PhpParser\ParserFactory;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Contracts\Schemable;
use Tests\Fixtures\Tooling\PhpStan\SchemableCollections\ValidSchemableCollectionWithParity;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<UseSchemaCollectionMustHaveMatchingUseSchema>
 */
class UseSchemaCollectionMustHaveMatchingUseSchemaTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new UseSchemaCollectionMustHaveMatchingUseSchema(
            new SchemaVersionResolver((new ParserFactory)->createForNewestSupportedVersion())
        );
    }

    #[Test]
    public function it_passes_when_all_versions_match(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/ValidSchemable.php')], []);
    }

    #[Test]
    public function it_fails_when_model_is_missing_a_schema_version(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/CollectedByWithAdditionalVersion.php')], [
            [
                '['.class_basename(Schemable::class).'] is missing #['.class_basename(UseSchema::class).'] for version ['.class_basename(ApiVersion::class).'::'.ApiVersion::V1->name.'] declared by ['.class_basename(ValidSchemableCollectionWithParity::class).'].',
                17,
            ],
        ]);
    }

    #[Test]
    public function it_skips_when_no_collected_by_attribute(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemables/MissingCollectedBy.php')], []);
    }
}
