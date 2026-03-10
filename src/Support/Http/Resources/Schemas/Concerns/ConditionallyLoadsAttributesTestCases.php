<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Concerns;

use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Exceptions\MergeUnlessNotSupported;
use Support\Http\Resources\Schemas\Fields\Exceptions\MergeWhenNotSupported;
use Tests\Fixtures\Support\Teams\Team;
use Tests\Fixtures\Support\TeamUser\TeamUser;
use Tests\Fixtures\Support\Users\User;
use Tests\TestCase;

/**
 * @mixin TestCase
 */
trait ConditionallyLoadsAttributesTestCases
{
    #[Test]
    public function when_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('username', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_includes(): void
    {
        request()->merge(['with_email' => true]);
        $resource = User::factory()->create()->toResource();

        $this->assertArrayHasKey('email', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function unless_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('username', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function unless_includes(): void
    {
        request()->merge(['with_username' => true]);
        $resource = User::factory()->create()->toResource();

        $this->assertArrayHasKey('username', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function merge_when_not_allowed(): void
    {
        $this->expectException(MergeWhenNotSupported::class);

        Team::factory()->create()->toResource()->toArray(request());
    }

    #[Test]
    public function merge_unless_not_allowed(): void
    {
        $this->expectException(MergeUnlessNotSupported::class);

        TeamUser::factory()->create()->toResource()->toArray(request());
    }

    #[Test]
    public function when_has_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('biography', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_has_includes(): void
    {
        $resource = User::factory()->biography()->create()->toResource();

        $this->assertArrayHasKey('biography', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_null_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('middle_initial', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_null_includes(): void
    {
        $resource = User::factory()->withoutMiddleName()->create()->toResource();

        $this->assertArrayHasKey('middle_initial', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_not_null_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('deleted_at', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_not_null_includes(): void
    {
        $resource = User::factory()->deleted()->create()->toResource();

        $this->assertArrayHasKey('deleted_at', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_appended_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('full_name', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_appended_includes(): void
    {
        $resource = User::factory()->create()->append('full_name')->toResource();

        $this->assertArrayHasKey('full_name', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_loaded_discards(): void
    {
        $resource = User::factory()->hasPosts()->create()->toResource();

        $this->assertArrayNotHasKey('posts', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_loaded_includes(): void
    {
        $resource = User::factory()->hasPosts()->create()->toResource()->load('posts');

        $this->assertArrayHasKey('posts', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_counted_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('posts_count', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_counted_includes(): void
    {
        $resource = User::factory()->hasPosts()->create()->toResource()->loadCount('posts');

        $this->assertArrayHasKey('posts_count', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_aggregated_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('rating', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_aggregated_includes(): void
    {
        $resource = User::factory()->hasPosts()->create()->loadAggregate('posts', 'rating', 'avg')->toResource();

        $this->assertArrayHasKey('rating', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_exists_loaded_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('has_posts', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_exists_loaded_includes(): void
    {
        $resource = User::factory()->hasPosts()->create()->loadExists('posts')->toResource();

        $this->assertArrayHasKey('has_posts', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_pivot_loaded_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('pivot', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function when_pivot_loaded_includes(): void
    {
        $resource = Team::factory()->hasUsers()->create()->users->first()->toResource();

        $this->assertArrayHasKey('team_membership_id', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function transforms_discards(): void
    {
        $resource = User::factory()->create()->toResource();

        $this->assertArrayNotHasKey('age', (array) json_decode($resource->toJson()));
    }

    #[Test]
    public function transforms_includes(): void
    {
        $resource = User::factory()->birthday()->create()->toResource();

        $this->assertArrayHasKey('age', (array) json_decode($resource->toJson()));
    }
}
