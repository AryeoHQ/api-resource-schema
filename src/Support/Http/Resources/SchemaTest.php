<?php

declare(strict_types=1);

namespace Support\Http\Resources;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\Exceptions\NotDefined;
use Support\Http\Resources\Schemas\Attributes\UseSchema\UseSchema;
use Support\Http\Resources\Schemas\Attributes\Version\Exceptions\NotFound;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Concerns\ConditionallyLoadsAttributesTestCases;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Fields\Exceptions\MergeUnlessNotSupported;
use Support\Http\Resources\Schemas\Fields\Exceptions\MergeWhenNotSupported;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users\Collection;
use Tests\Fixtures\Support\Users\Schemas;
use Tests\Fixtures\Support\Users\User;
use Tests\Fixtures\Tooling\PhpStan\Schemas\MissingCollectedBy;
use Tests\TestCase;

#[CoversClass(CollectedBy::class)]
#[CoversClass(MergeUnlessNotSupported::class)]
#[CoversClass(MergeWhenNotSupported::class)]
#[CoversClass(NotDefined::class)]
#[CoversClass(NotFound::class)]
#[CoversClass(UseSchema::class)]
#[CoversClass(Version::class)]
#[CoversTrait(AsSchema::class)]
#[CoversTrait(TransformsToSchema::class)]

class SchemaTest extends TestCase
{
    use ConditionallyLoadsAttributesTestCases;

    #[Test]
    public function to_array(): void
    {
        $data = ['first_name' => 'John', 'last_name' => 'Doe'];
        $schema = User::factory()->state($data)->create()->toSchema(ApiVersion::V2);

        $result = collect(
            (array) $schema->toArray(request())
        )->reject(fn ($value) => $value instanceof Discarded);

        $this->assertSame($data, $result->toArray());
    }

    #[Test]
    public function to_json(): void
    {
        $data = ['first_name' => 'John', 'last_name' => 'Doe'];
        $schema = User::factory()->state($data)->create()->toSchema(ApiVersion::V2);

        $this->assertSame(json_encode($data), $schema->toJson());
    }

    #[Test]
    public function to_json_contains_discardable_field_when_filled(): void
    {
        request()->merge(['with_email' => true]);
        $data = ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@doe.com'];
        $schema = User::factory()->state($data)->create()->toSchema(ApiVersion::V2);

        $this->assertSame(json_encode($data), $schema->toJson());
    }

    #[Test]
    public function it_is_collected_by(): void
    {
        $schemaCollection = Schemas\V2\User::collection(User::factory()->times(2)->make());

        $this->assertInstanceOf(Collection\Schemas\V2\Users::class, $schemaCollection);
    }

    #[Test]
    public function it_throws_an_exception_when_collected_by_is_missing(): void
    {
        $this->expectException(NotDefined::class);

        MissingCollectedBy::collection([]);
    }

    #[Test]
    public function it_exposes_the_schema_version(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertSame(ApiVersion::V2, $schema->schemaVersion);
    }

    #[Test]
    public function to_schema_resolves_specific_version_when_requested(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V1);

        $this->assertInstanceOf(\Tests\Fixtures\Support\Users\Schemas\V1\User::class, $schema);
    }

    #[Test]
    public function to_schema_throws_for_unavailable_version(): void
    {
        $this->expectException(NotFound::class);

        User::factory()->create()->toSchema(ApiVersion::V3);
    }
}
