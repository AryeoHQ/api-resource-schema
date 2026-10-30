<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
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
    public function it_passes_when_schema_does_not_define_with_response(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/ValidSchema.php')], []);
    }

    #[Test]
    public function it_passes_when_schema_collection_does_not_define_with_response(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/SchemaCollections/ValidSchemaCollection.php')], []);
    }

    #[Test]
    public function it_fails_when_schema_defines_with_response(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Responses/SchemaWithResponse.php')], [
            [
                '['.class_basename(Schema::class).'] must not define [withResponse()]; it would drop the entity tag.',
                22,
            ],
        ]);
    }

    #[Test]
    public function it_fails_when_schema_collection_defines_with_response(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Responses/SchemaCollectionWithResponse.php')], [
            [
                '['.class_basename(SchemaCollection::class).'] must not define [withResponse()]; it would drop the entity tag.',
                20,
            ],
        ]);
    }
}
