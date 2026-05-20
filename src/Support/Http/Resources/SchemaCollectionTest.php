<?php

declare(strict_types=1);

namespace Support\Http\Resources;

use Illuminate\Pagination\AbstractPaginator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Support\Http\Resources\Schemas\Attributes\Collects\Exceptions\NotDefined;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\UseSchemaCollection;
use Support\Http\Resources\Schemas\Attributes\Version\Exceptions\NotFound;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchemaCollection;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users\Collection;
use Tests\Fixtures\Support\Users\Collection\Users;
use Tests\Fixtures\Support\Users\Schemas;
use Tests\Fixtures\Support\Users\User;
use Tests\Fixtures\Tooling\PhpStan\SchemaCollections\MissingCollects;
use Tests\TestCase;

#[CoversClass(Collects::class)]
#[CoversClass(NotDefined::class)]
#[CoversClass(NotFound::class)]
#[CoversClass(UseSchemaCollection::class)]
#[CoversClass(Version::class)]
#[CoversTrait(AsSchemaCollection::class)]
#[CoversTrait(TransformsToSchemaCollection::class)]
class SchemaCollectionTest extends TestCase
{
    #[Test]
    public function it_collects_schema(): void
    {
        $collection = new Collection\Schemas\V2\Users(User::factory()->times(2)->make());

        $this->assertSame(Schemas\V2\User::class, $collection->collects);
    }

    #[Test]
    public function it_throws_an_exception_when_collects_is_missing(): void
    {
        $this->expectException(NotDefined::class);

        new MissingCollects([]);
    }

    #[Test]
    public function it_exposes_the_collection_version_from_collects_schema(): void
    {
        $schemaCollection = new Collection\Schemas\V2\Users(User::factory()->times(2)->make());

        $this->assertSame(ApiVersion::V2, $schemaCollection->schemaVersion);
    }

    #[Test]
    public function it_transforms_to_schema_collection_for_requested_version(): void
    {
        $collection = new Users(User::factory()->count(2)->create()->all());

        $this->assertInstanceOf(Collection\Schemas\V2\Users::class, $collection->toSchemaCollection(ApiVersion::V2));
    }

    #[Test]
    public function it_lists_schema_versions_on_schemable_collections(): void
    {
        $collection = new Users(User::factory()->count(1)->create()->all());

        $this->assertSame(
            [ApiVersion::V1, ApiVersion::V2],
            $collection->schemaVersions->values()->all()
        );
    }

    #[Test]
    public function it_lists_schema_versions_on_empty_collections(): void
    {
        $collection = new Users;

        $this->assertSame(
            [ApiVersion::V1, ApiVersion::V2],
            $collection->schemaVersions->values()->all()
        );
    }

    #[Test]
    public function it_transforms_empty_collection_to_empty_schema_collection(): void
    {
        $collection = new Users;

        $schemaCollection = $collection->toSchemaCollection(ApiVersion::V2);

        $this->assertInstanceOf(Collection\Schemas\V2\Users::class, $schemaCollection);
        $this->assertCount(0, $schemaCollection);
    }

    #[Test]
    public function it_preserves_pagination_when_called_through_paginator(): void
    {
        User::factory()->count(3)->create();
        $paginator = User::paginate(2);

        $schemaCollection = $paginator->toSchemaCollection(ApiVersion::V2); // @phpstan-ignore method.notFound

        $this->assertInstanceOf(Collection\Schemas\V2\Users::class, $schemaCollection);
        $this->assertInstanceOf(AbstractPaginator::class, $schemaCollection->resource);
    }

    #[Test]
    public function it_produces_non_paginated_response_from_collection(): void
    {
        $collection = new Users(User::factory()->count(2)->create()->all());

        $schemaCollection = $collection->toSchemaCollection(ApiVersion::V2);

        $this->assertInstanceOf(Collection\Schemas\V2\Users::class, $schemaCollection);
        $this->assertNotInstanceOf(AbstractPaginator::class, $schemaCollection->resource);
    }

    #[Test]
    public function to_schema_collection_throws_for_unavailable_version(): void
    {
        $this->expectException(NotFound::class);

        (new Users)->toSchemaCollection(ApiVersion::V3);
    }
}
