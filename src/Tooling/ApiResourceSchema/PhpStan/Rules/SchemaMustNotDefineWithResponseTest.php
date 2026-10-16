<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemaMustNotDefineWithResponse>
 */
class SchemaMustNotDefineWithResponseTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemaMustNotDefineWithResponse;
    }

    #[Test]
    public function it_passes_when_a_schema_keeps_the_trait_response_hook(): void
    {
        $this->analyse([
            $this->getFixturePath('PhpStan/Schemas/ValidSchema.php'),
            $this->getFixturePath('PhpStan/SchemaCollections/ValidSchemaCollection.php'),
        ], []);
    }

    #[Test]
    public function it_passes_when_a_plain_resource_defines_with_response(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Responses/ResourceWithResponse.php')], []);
    }

    #[Test]
    public function it_fails_when_a_schema_defines_with_response(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Responses/SchemaWithResponse.php')], [
            [
                'A class that uses ['.class_basename(AsSchema::class).'] must not define [withResponse], because ['.class_basename(AsSchema::class).'] sets the ETag there.',
                20,
            ],
        ]);
    }

    #[Test]
    public function it_fails_when_a_schema_collection_defines_with_response(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Responses/SchemaCollectionWithResponse.php')], [
            [
                'A class that uses ['.class_basename(AsSchemaCollection::class).'] must not define [withResponse], because ['.class_basename(AsSchemaCollection::class).'] sets the ETag there.',
                20,
            ],
        ]);
    }
}
