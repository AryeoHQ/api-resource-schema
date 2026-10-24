<?php

declare(strict_types=1);

namespace Support\Http\Resources;

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Route;
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
use Support\Http\Resources\Schemas\Tags\EntityTag;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Posts\Schemas\Posts;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users\Collection;
use Tests\Fixtures\Support\Users\Collection\Users;
use Tests\Fixtures\Support\Users\Schemas;
use Tests\Fixtures\Support\Users\User;
use Tests\Fixtures\Support\Users\UserRevision;
use Tests\Fixtures\Tooling\PhpStan\SchemaCollections\MissingCollects;
use Tests\TestCase;

#[CoversClass(Collects::class)]
#[CoversClass(EntityTag::class)]
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

    #[Test]
    public function a_tagged_schema_collection_response_carries_its_tag(): void
    {
        $user = User::factory()->state(['concurrency_revision' => 17, 'posts_order_concurrency_revision' => 4])->create();
        Post::factory()->count(2)->for($user)->create();
        Route::get('posts', fn () => Posts::make($user->posts)->withTag(
            EntityTag::forNamedRevision($user, UserRevision::PostsOrder, ApiVersion::V2)
        ));

        $this->get('posts')->assertOk()->assertJsonCount(2, 'data')->assertHeader('ETag', '"v2.posts_order=4"');
    }

    #[Test]
    public function a_tagged_paginated_schema_collection_response_carries_its_tag_on_every_page(): void
    {
        $user = User::factory()->state(['posts_order_concurrency_revision' => 4])->create();
        Post::factory()->count(2)->for($user)->create();
        Route::get('posts', fn () => Posts::make($user->posts()->paginate(1))->withTag(
            EntityTag::forNamedRevision($user, UserRevision::PostsOrder, ApiVersion::V2)
        ));

        $this->get('posts?page=1')->assertJsonPath('meta.current_page', 1)->assertHeader('ETag', '"v2.posts_order=4"');
        $this->get('posts?page=2')->assertJsonPath('meta.current_page', 2)->assertHeader('ETag', '"v2.posts_order=4"');
    }

    #[Test]
    public function an_untagged_schema_collection_response_carries_no_entity_tag(): void
    {
        User::factory()->count(2)->create();
        Route::get('users', fn () => new Collection\Schemas\V2\Users(User::query()->get()));

        $this->get('users')->assertOk()->assertJsonCount(2, 'data')->assertHeaderMissing('ETag');
    }
}
