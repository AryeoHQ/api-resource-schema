<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<SchemaMustUseAsSchema>
 */
class SchemaMustUseAsSchemaTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new SchemaMustUseAsSchema;
    }

    #[Test]
    public function it_passes_when_schema_uses_as_schema(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/ValidSchema.php')], []);
    }

    #[Test]
    public function it_fails_when_schema_does_not_use_as_schema(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Schemas/MissingAsSchema.php')], [
            [
                '['.class_basename(Schema::class).'] must use the ['.class_basename(AsSchema::class).'] trait.',
                10,
            ],
        ]);
    }
}
