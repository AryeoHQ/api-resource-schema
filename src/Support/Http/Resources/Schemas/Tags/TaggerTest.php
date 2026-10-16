<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\ContributesTag\ContributesTag;
use Support\Http\Resources\Schemas\Concerns\ConditionallyLoadsAttributes;
use Support\Http\Resources\Schemas\Concerns\TransformsToSchema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Tags\Schemas\Member;
use Tests\Fixtures\Support\Teams\Team;
use Tests\Fixtures\Support\Users\User;
use Tests\TestCase;

#[CoversClass(ContributesTag::class)]
#[CoversClass(Tagger::class)]
#[CoversTrait(AsSchema::class)]
#[CoversTrait(ConditionallyLoadsAttributes::class)]
#[CoversTrait(TransformsToSchema::class)]
class TaggerTest extends TestCase
{
    #[Test]
    public function the_same_state_gives_the_same_tag(): void
    {
        $user = User::factory()->hasPosts()->create();

        $this->assertEquals(User::findOrFail($user->id)->toTag(ApiVersion::V2), User::findOrFail($user->id)->toTag(ApiVersion::V2));
    }

    #[Test]
    public function an_attribute_change_gives_a_new_tag(): void
    {
        $user = User::factory()->create(['first_name' => 'John']);
        $before = $user->toTag(ApiVersion::V2);

        $user->update(['first_name' => 'Jane']);

        $this->assertNotEquals($before, $user->toTag(ApiVersion::V2));
    }

    #[Test]
    public function request_flags_do_not_change_the_tag(): void
    {
        $user = User::factory()->create();
        $without = $user->toTag(ApiVersion::V2);

        request()->merge(['with_email' => true, 'with_username' => true]);

        $this->assertEquals($without, $user->toTag(ApiVersion::V2));
    }

    #[Test]
    public function a_change_to_a_flagged_field_gives_a_new_tag_without_the_flag(): void
    {
        $user = User::factory()->create(['email' => 'john@doe.com']);
        $before = $user->toTag(ApiVersion::V2);

        $user->update(['email' => 'jane@doe.com']);

        $this->assertNotEquals($before, $user->toTag(ApiVersion::V2));
    }

    #[Test]
    public function a_change_that_when_null_discards_keeps_the_tag(): void
    {
        $user = User::factory()->create(['middle_name' => 'John']);
        $before = $user->toTag(ApiVersion::V2);

        $user->update(['middle_name' => 'Mark']);

        $this->assertEquals($before, $user->toTag(ApiVersion::V2));
    }

    #[Test]
    public function loading_relations_does_not_change_the_tag(): void
    {
        $user = User::factory()->hasPosts()->create();
        $unloaded = $user->toTag(ApiVersion::V2);

        $user->load('posts')->loadCount('posts')->loadExists('posts')->loadAggregate('posts', 'rating', 'avg');

        $this->assertEquals($unloaded, $user->toTag(ApiVersion::V2));
    }

    #[Test]
    public function loading_a_pivot_does_not_change_the_tag(): void
    {
        $team = Team::factory()->hasUsers()->create();
        $user = $team->users->firstOrFail();

        $this->assertEquals(User::findOrFail($user->id)->toTag(ApiVersion::V2), $user->toTag(ApiVersion::V2));
    }

    #[Test]
    public function a_contribution_change_gives_a_new_tag(): void
    {
        $user = User::factory()->create();
        $before = $user->toTag(ApiVersion::V2);

        Post::factory()->for($user)->create();

        $this->assertNotEquals($before, $user->toTag(ApiVersion::V2));
    }

    #[Test]
    public function an_untagged_field_change_keeps_the_tag(): void
    {
        $user = User::factory()->create();
        $before = app(Tagger::class)->tag(Member::make($user));

        request()->headers->set('X-Viewer', 'jane');

        $this->assertEquals($before, app(Tagger::class)->tag(Member::make($user)));
    }

    #[Test]
    public function an_untagged_field_still_renders_in_the_body(): void
    {
        request()->headers->set('X-Viewer', 'jane');

        $schema = Member::make(User::factory()->create());

        $this->assertSame('jane', json_decode($schema->toJson())->viewer);
    }

    #[Test]
    public function each_version_gives_its_own_tag(): void
    {
        $user = User::factory()->create();

        $this->assertNotEquals($user->toTag(ApiVersion::V1), $user->toTag(ApiVersion::V2));
    }

    #[Test]
    public function tagging_leaves_the_schema_body_unchanged(): void
    {
        $user = User::factory()->hasPosts()->create()->load('posts');
        $body = $user->toSchema(ApiVersion::V2)->toJson();

        $user->toTag(ApiVersion::V2);

        $this->assertSame($body, $user->toSchema(ApiVersion::V2)->toJson());
    }
}
