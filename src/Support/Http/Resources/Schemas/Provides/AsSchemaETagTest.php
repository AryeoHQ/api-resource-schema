<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Provides;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Tags\Tagger;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Tags\Schemas\Article;
use Tests\Fixtures\Support\Tags\Schemas\Member;
use Tests\Fixtures\Support\Users\User;
use Tests\TestCase;
use Workbench\App\Providers\WorkbenchServiceProvider;

#[CoversTrait(AsSchema::class)]
class AsSchemaETagTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), WorkbenchServiceProvider::class];
    }

    #[Test]
    public function a_get_returns_the_schema_tag(): void
    {
        $user = User::factory()->create();

        $this->getJson("users/{$user->id}")
            ->assertOk()
            ->assertHeader('ETag', (string) User::findOrFail($user->id)->toTag(ApiVersion::V2));
    }

    #[Test]
    public function the_tag_changes_after_an_update(): void
    {
        $user = User::factory()->create(['first_name' => 'John']);
        $before = $this->getJson("users/{$user->id}")->headers->get('ETag');

        $user->update(['first_name' => 'Jane']);

        $after = $this->getJson("users/{$user->id}")->assertHeader('ETag')->headers->get('ETag');

        $this->assertNotSame($before, $after);
    }

    #[Test]
    public function a_request_flag_keeps_the_tag(): void
    {
        $user = User::factory()->create();
        $without = $this->getJson("users/{$user->id}")->headers->get('ETag');

        $this->getJson("users/{$user->id}?with_email=1")
            ->assertJsonPath('data.email', $user->email)
            ->assertHeader('ETag', (string) $without);
    }

    #[Test]
    public function the_body_is_the_schema(): void
    {
        $user = User::factory()->create();

        $this->getJson("users/{$user->id}")
            ->assertExactJson(['data' => User::findOrFail($user->id)->toSchema(ApiVersion::V2)->resolve()]);
    }

    #[Test]
    public function the_tag_is_the_rendered_schema_not_the_registered_one(): void
    {
        $user = User::factory()->create();

        $etag = $this->getJson("members/{$user->id}")->assertOk()->headers->get('ETag');

        $this->assertSame((string) app(Tagger::class)->tag(Member::make(User::findOrFail($user->id))), $etag);
        $this->assertNotSame((string) User::findOrFail($user->id)->toTag(ApiVersion::V2), $etag);
    }

    #[Test]
    public function a_schema_the_model_does_not_register_carries_a_tag(): void
    {
        $post = Post::factory()->create();

        $this->getJson("posts/{$post->id}/article")
            ->assertOk()
            ->assertHeader('ETag', (string) app(Tagger::class)->tag(Article::make(Post::findOrFail($post->id))));
    }

    #[Test]
    public function the_tag_covers_additional_data(): void
    {
        $user = User::factory()->create();

        $first = $this->getJson("users/{$user->id}/revisions/1")->assertJsonPath('meta.revision', 1)->headers->get('ETag');
        $second = $this->getJson("users/{$user->id}/revisions/2")->assertJsonPath('meta.revision', 2)->headers->get('ETag');

        $this->assertNotSame($first, $second);
    }

    #[Test]
    public function the_tag_covers_with_data(): void
    {
        $post = Post::factory()->create(['rating' => 1]);
        $before = $this->getJson("posts/{$post->id}/article")->assertJsonPath('meta.rating', 1)->headers->get('ETag');

        $post->update(['rating' => 2]);

        $after = $this->getJson("posts/{$post->id}/article")->assertJsonPath('meta.rating', 2)->headers->get('ETag');

        $this->assertNotSame($before, $after);
    }

    #[Test]
    public function a_missing_append_keeps_the_tag(): void
    {
        $user = User::factory()->create();

        $this->getJson("users/{$user->id}/appended")
            ->assertJsonPath('data.full_name', User::findOrFail($user->id)->full_name)
            ->assertHeader('ETag', (string) $this->getJson("users/{$user->id}")->assertJsonMissingPath('data.full_name')->headers->get('ETag'));
    }

    #[Test]
    public function a_nested_schema_contribution_change_gives_a_new_tag(): void
    {
        $post = Post::factory()->create();
        $before = $this->getJson("posts/{$post->id}/article")->headers->get('ETag');

        Post::factory()->for($post->user)->create();

        $this->assertNotSame($before, $this->getJson("posts/{$post->id}/article")->headers->get('ETag'));
    }

    #[Test]
    public function a_when_closure_that_needs_its_condition_never_runs_for_the_tag(): void
    {
        $user = User::factory()->create();

        $this->getJson("tombstones/{$user->id}")->assertOk()->assertJsonMissingPath('data.deleted_on')->assertHeader('ETag');
    }

    #[Test]
    public function a_model_state_flip_gives_a_new_tag(): void
    {
        $user = User::factory()->create();
        $before = $this->getJson("accounts/{$user->id}")->assertJsonMissingPath('data.former_name')->headers->get('ETag');

        $user->delete();

        $after = $this->getJson("accounts/{$user->id}")->assertJsonPath('data.former_name', $user->last_name)->headers->get('ETag');

        $this->assertNotSame($before, $after);
    }

    #[Test]
    public function an_edit_to_a_hidden_model_state_field_keeps_the_tag(): void
    {
        $user = User::factory()->create(['last_name' => 'Doe']);
        $before = $this->getJson("accounts/{$user->id}")->headers->get('ETag');

        $user->update(['last_name' => 'Roe']);

        $this->getJson("accounts/{$user->id}")->assertJsonMissingPath('data.former_name')->assertHeader('ETag', (string) $before);
    }

    #[Test]
    public function a_permission_gated_field_enters_the_tag_only_for_a_permitted_caller(): void
    {
        Gate::define('view-email', fn (null|Authenticatable $viewer): bool => $viewer !== null);
        $user = User::factory()->create();

        $guest = $this->getJson("accounts/{$user->id}")->assertJsonMissingPath('data.email')->headers->get('ETag');

        $this->actingAs(new GenericUser(['id' => 1]));

        $permitted = $this->getJson("accounts/{$user->id}")->assertJsonPath('data.email', $user->email)->headers->get('ETag');

        $this->assertNotSame($guest, $permitted);
    }

    #[Test]
    public function a_partial_select_keeps_the_tag(): void
    {
        $user = User::factory()->biography()->create();

        $this->getJson("members/{$user->id}/partial")
            ->assertJsonMissingPath('data.biography')
            ->assertHeader('ETag', (string) $this->getJson("members/{$user->id}")->assertJsonPath('data.biography', $user->biography)->headers->get('ETag'));
    }
}
