<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Concerns;

use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Exceptions\MergeUnlessNotSupported;
use Support\Http\Resources\Schemas\Fields\Exceptions\MergeWhenNotSupported;
use Tests\Fixtures\Support\Schemas\ApiVersion;
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
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('username', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_includes(): void
    {
        request()->merge(['with_email' => true]);
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayHasKey('email', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function unless_discards(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('username', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function unless_includes(): void
    {
        request()->merge(['with_username' => true]);
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayHasKey('username', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function merge_when_not_allowed(): void
    {
        $this->expectException(MergeWhenNotSupported::class);

        Team::factory()->create()->toSchema(ApiVersion::V2)->toArray(request());
    }

    #[Test]
    public function merge_unless_not_allowed(): void
    {
        $this->expectException(MergeUnlessNotSupported::class);

        TeamUser::factory()->create()->toSchema(ApiVersion::V2)->toArray(request());
    }

    #[Test]
    public function when_has_discards(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('biography', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_has_includes(): void
    {
        $schema = User::factory()->biography()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayHasKey('biography', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_null_discards(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('middle_initial', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_null_includes(): void
    {
        $schema = User::factory()->withoutMiddleName()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayHasKey('middle_initial', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_not_null_discards(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('deleted_at', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_not_null_includes(): void
    {
        $schema = User::factory()->deleted()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayHasKey('deleted_at', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_appended_discards(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('full_name', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_appended_includes(): void
    {
        $schema = User::factory()->create()->append('full_name')->toSchema(ApiVersion::V2);

        $this->assertArrayHasKey('full_name', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_loaded_discards(): void
    {
        $schema = User::factory()->hasPosts()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('posts', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_loaded_includes(): void
    {
        $schema = User::factory()->hasPosts()->create()->toSchema(ApiVersion::V2)->load('posts');

        $this->assertArrayHasKey('posts', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_counted_discards(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('posts_count', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_counted_includes(): void
    {
        $schema = User::factory()->hasPosts()->create()->toSchema(ApiVersion::V2)->loadCount('posts');

        $this->assertArrayHasKey('posts_count', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_aggregated_discards(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('rating', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_aggregated_includes(): void
    {
        $schema = User::factory()->hasPosts()->create()->loadAggregate('posts', 'rating', 'avg')->toSchema(ApiVersion::V2);

        $this->assertArrayHasKey('rating', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_exists_loaded_discards(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('has_posts', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_exists_loaded_includes(): void
    {
        $schema = User::factory()->hasPosts()->create()->loadExists('posts')->toSchema(ApiVersion::V2);

        $this->assertArrayHasKey('has_posts', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_pivot_loaded_discards(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('pivot', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function when_pivot_loaded_includes(): void
    {
        $schema = Team::factory()->hasUsers()->create()->users->first()->toSchema(ApiVersion::V2);

        $this->assertArrayHasKey('team_membership_id', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function transforms_discards(): void
    {
        $schema = User::factory()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayNotHasKey('age', (array) json_decode($schema->toJson()));
    }

    #[Test]
    public function transforms_includes(): void
    {
        $schema = User::factory()->birthday()->create()->toSchema(ApiVersion::V2);

        $this->assertArrayHasKey('age', (array) json_decode($schema->toJson()));
    }
}
