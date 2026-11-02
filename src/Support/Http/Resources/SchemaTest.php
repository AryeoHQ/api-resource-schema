<?php

declare(strict_types=1);

namespace Support\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Support\Database\Eloquent\Concurrency\ConcurrencyRevision;
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
use Support\Http\Resources\Schemas\Tags\EntityTag;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users\Collection;
use Tests\Fixtures\Support\Users\Schemas;
use Tests\Fixtures\Support\Users\Schemas\Dotted\User as DottedUser;
use Tests\Fixtures\Support\Users\User;
use Tests\Fixtures\Tooling\PhpStan\Schemas\MissingCollectedBy;
use Tests\TestCase;

#[CoversClass(CollectedBy::class)]
#[CoversClass(EntityTag::class)]
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

    private const array SELECTED_COLUMNS = ['id', 'first_name', 'middle_name', 'last_name', 'biography', 'date_of_birth', 'deleted_at'];

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

    #[Test]
    public function a_revisioned_schema_response_carries_an_entity_tag(): void
    {
        User::factory()->state(['first_name' => 'John', 'last_name' => 'Doe'])->create();
        Route::get('user', fn () => User::query()->sole()->toSchema(ApiVersion::V2));

        $response = $this->get('user');

        $response->assertHeader('ETag', '"v2.'.User::query()->sole()->concurrencyRevision(ConcurrencyRevision::Self).'.'.hash('xxh128', (string) $response->baseResponse->getContent()).'"');
        $this->assertSame(json_encode(['data' => ['first_name' => 'John', 'last_name' => 'Doe', 'biography' => null]]), $response->baseResponse->getContent());
    }

    #[Test]
    public function a_non_revisioned_schema_response_carries_no_entity_tag(): void
    {
        Post::factory()->create();
        Route::get('post', fn () => Post::query()->sole()->toSchema(ApiVersion::V2));

        $this->get('post')->assertOk()->assertHeaderMissing('ETag');
    }

    #[Test]
    public function an_unsaved_revisioned_schema_response_carries_no_entity_tag(): void
    {
        Route::get('user', fn () => User::factory()->make()->toSchema(ApiVersion::V2));

        $this->get('user')->assertOk()->assertHeaderMissing('ETag');
    }

    #[Test]
    public function a_schema_response_read_with_a_select_list_carries_no_entity_tag(): void
    {
        User::factory()->create();
        Route::get('user', fn () => User::query()->select(self::SELECTED_COLUMNS)->sole()->toSchema(ApiVersion::V2));

        $this->get('user')->assertOk()->assertHeaderMissing('ETag');
    }

    #[Test]
    public function a_schema_response_read_with_a_select_list_carries_no_entity_tag_when_missing_attributes_are_prevented(): void
    {
        Model::preventAccessingMissingAttributes();
        $this->beforeApplicationDestroyed(fn () => Model::preventAccessingMissingAttributes(false));
        User::factory()->create();
        Route::get('user', fn () => User::query()->select(self::SELECTED_COLUMNS)->sole()->toSchema(ApiVersion::V2));

        $this->get('user')->assertOk()->assertHeaderMissing('ETag');
    }

    #[Test]
    public function a_schema_response_with_a_version_that_is_not_a_token_carries_no_entity_tag(): void
    {
        User::factory()->create();
        Route::get('user', fn () => new DottedUser(User::query()->sole()));

        $this->get('user')->assertOk()->assertHeaderMissing('ETag');
    }

    #[Test]
    public function the_entity_tag_digest_follows_the_body(): void
    {
        User::factory()->state(['first_name' => 'John'])->create();
        Route::get('user', fn () => User::query()->sole()->toSchema(ApiVersion::V2));

        $first = $this->get('user')->headers->get('ETag');
        $repeated = $this->get('user')->headers->get('ETag');
        DB::table('users')->update(['first_name' => 'Joan']);
        $changed = $this->get('user')->headers->get('ETag');

        $this->assertSame($first, $repeated);
        $this->assertNotSame($first, $changed);
    }

    #[Test]
    public function the_entity_tag_own_revision_follows_a_raw_write_to_an_unrendered_column(): void
    {
        User::factory()->create();
        Route::get('user', fn () => User::query()->sole()->toSchema(ApiVersion::V2));

        $first = $this->get('user');
        DB::table('users')->update(['email' => 'changed@example.com']);
        $changed = $this->get('user');

        $this->assertSame($first->baseResponse->getContent(), $changed->baseResponse->getContent());
        $this->assertNotSame($first->headers->get('ETag'), $changed->headers->get('ETag'));
        $changed->assertHeader('ETag', '"v2.'.User::query()->sole()->concurrencyRevision(ConcurrencyRevision::Self).'.'.hash('xxh128', (string) $changed->baseResponse->getContent()).'"');
    }

    #[Test]
    public function the_entity_tag_ignores_a_change_to_the_posts(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        Route::get('user', fn () => User::query()->sole()->toSchema(ApiVersion::V2));

        $first = $this->get('user')->headers->get('ETag');
        $post->delete();
        Post::factory()->count(2)->for($user)->create();
        $changed = $this->get('user')->headers->get('ETag');

        $this->assertSame($first, $changed);
    }
}
