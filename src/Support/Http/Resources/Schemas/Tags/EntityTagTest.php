<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Database\Eloquent\Concurrency\ConcurrencyRevision;
use Support\Database\Eloquent\Concurrency\Contracts\Revisioned;
use Support\Database\Eloquent\Concurrency\Exceptions\UndeclaredConcurrencyRevision;
use Support\Database\Eloquent\Concurrency\Provides\HasConcurrencyRevisions;
use Support\Database\Eloquent\Concurrency\Revisions;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Schemas\DottedApiVersion;
use Tests\Fixtures\Support\Schemas\LineFeedApiVersion;
use Tests\Fixtures\Support\Users\User;
use Tests\Fixtures\Support\Users\UserRevision;
use Tests\TestCase;

#[CoversClass(EntityTag::class)]
#[CoversClass(EntityTagList::class)]
class EntityTagTest extends TestCase
{
    private const string OWN = '3f1c2b4a5d6e7f8091a2b3c4d5e6f708';

    private const string ORDER = 'a1b2c3d4e5f60718293a4b5c6d7e8f90';

    private const string DIGEST = '9f2c1a0b7e3d5c4a9f2c1a0b7e3d5c4a';

    #[Test]
    public function it_tags_a_representation_with_the_api_version_the_own_revision_and_the_digest(): void
    {
        $created = User::factory()->create();
        Post::factory()->count(2)->for($created)->create();
        $user = User::query()->findOrFail($created->getKey());

        $tag = EntityTag::forRepresentation($user, ApiVersion::V2, '{"data":[]}');

        $this->assertNotNull($tag);
        $this->assertSame('"v2.'.$user->concurrencyRevision(ConcurrencyRevision::Self).'.'.hash('xxh128', '{"data":[]}').'"', (string) $tag);
        $this->assertEquals(Revisions::of($user->concurrencyRevision(ConcurrencyRevision::Self)), $tag->revisions);
    }

    #[Test]
    public function it_parses_and_matches_the_representation_tag_it_issues(): void
    {
        $user = User::factory()->create();
        $issued = (string) EntityTag::forRepresentation($user, ApiVersion::V2, '{"data":[]}');

        $parsed = EntityTag::parse($issued);
        $list = EntityTagList::parse([$issued]);

        $this->assertNotNull($parsed);
        $this->assertSame($issued, (string) $parsed);
        $this->assertEquals($user->concurrencyRevisions(), $parsed->revisions);
        $this->assertNotNull($list);
        $this->assertTrue($list->matchesRevisions(ApiVersion::V2, Revisions::of($user->concurrencyRevision(ConcurrencyRevision::Self))));
        $this->assertFalse($list->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => $user->concurrencyRevision(UserRevision::PostsOrder)])));
        $this->assertTrue($list->matchesWeakly($issued));
    }

    #[Test]
    public function it_keeps_the_representation_tag_when_the_posts_change(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $before = (string) EntityTag::forRepresentation(User::query()->findOrFail($user->getKey()), ApiVersion::V2, '{}');

        $post->user()->associate(User::factory()->create())->save();
        Post::factory()->count(2)->for($user)->create();

        $this->assertSame($before, (string) EntityTag::forRepresentation(User::query()->findOrFail($user->getKey()), ApiVersion::V2, '{}'));
    }

    #[Test]
    public function it_does_not_tag_a_representation_without_its_revisions(): void
    {
        $this->assertNull(EntityTag::forRepresentation(User::factory()->make(), ApiVersion::V2, '{}'));
    }

    #[Test]
    public function it_does_not_tag_a_representation_read_with_a_select_list(): void
    {
        $created = User::factory()->create();

        $this->assertNull(EntityTag::forRepresentation(User::query()->findOrFail($created->getKey(), ['id', 'first_name']), ApiVersion::V2, '{}'));
    }

    #[Test]
    public function it_does_not_tag_a_representation_with_an_api_version_that_ends_in_a_line_feed(): void
    {
        $user = User::factory()->create();

        $this->assertNull(EntityTag::forRepresentation($user, LineFeedApiVersion::V2, '{}'));
    }

    #[Test]
    public function it_tags_a_nested_index_with_the_api_version_and_the_named_revision(): void
    {
        $owner = User::factory()->create();
        Post::factory()->count(2)->for($owner)->create();

        $tag = EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);

        $this->assertSame('"v2.posts_order='.$owner->concurrencyRevision(UserRevision::PostsOrder).'"', (string) $tag);
        $this->assertEquals(Revisions::of(null, ['posts_order' => $owner->concurrencyRevision(UserRevision::PostsOrder)]), $tag->revisions);
        $this->assertNull($tag->digest);
    }

    #[Test]
    public function it_parses_and_matches_the_nested_index_tag_it_issues(): void
    {
        $owner = User::factory()->create();
        Post::factory()->for($owner)->create();
        $issued = (string) EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);

        $this->assertSame($issued, (string) EntityTag::parse($issued));
        $this->assertTrue(EntityTagList::parse([$issued])?->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => $owner->concurrencyRevision(UserRevision::PostsOrder)])));
    }

    #[Test]
    public function it_changes_the_nested_index_tag_when_the_posts_change(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();
        Post::factory()->for($owner)->create();
        $first = (string) EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);
        $repeated = (string) EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);

        $post->update(['title' => 'Renamed']);
        $renamed = (string) EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);
        $post->user()->associate(User::factory()->create())->save();
        $moved = (string) EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);

        $this->assertSame($first, $repeated);
        $this->assertSame($first, $renamed);
        $this->assertNotSame($first, $moved);
        $this->assertSame('"v2.posts_order='.$owner->concurrencyRevision(UserRevision::PostsOrder).'"', $moved);
    }

    #[Test]
    public function it_changes_the_nested_index_tag_but_not_the_representation_tag_when_the_posts_reorder(): void
    {
        $user = User::factory()->create();
        [$first, $second] = Post::factory()->count(2)->for($user)->sequence(['position' => 1], ['position' => 2])->create()->all();
        $nested = (string) EntityTag::forNamedRevision($user, UserRevision::PostsOrder, ApiVersion::V2);
        $representation = (string) EntityTag::forRepresentation(User::query()->findOrFail($user->getKey()), ApiVersion::V2, '{}');

        DB::table('posts')->where('id', $first->getKey())->update(['position' => 3]);

        $this->assertSame([$second->getKey(), $first->getKey()], $user->posts()->orderBy('position')->pluck('id')->all());
        $this->assertNotSame($nested, (string) EntityTag::forNamedRevision($user, UserRevision::PostsOrder, ApiVersion::V2));
        $this->assertSame($representation, (string) EntityTag::forRepresentation(User::query()->findOrFail($user->getKey()), ApiVersion::V2, '{}'));
    }

    #[Test]
    public function it_refuses_a_nested_index_tag_for_an_owner_that_was_never_saved(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('needs an owner that exists');

        EntityTag::forNamedRevision(User::factory()->make(), UserRevision::PostsOrder, ApiVersion::V2);
    }

    #[Test]
    public function it_refuses_a_nested_index_tag_for_an_owner_that_was_deleted(): void
    {
        $owner = User::factory()->create();
        Post::factory()->for($owner)->create();
        $owner->forceDelete();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('needs an owner that exists');

        EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);
    }

    #[Test]
    public function it_refuses_a_nested_index_tag_for_a_revision_the_owner_does_not_declare(): void
    {
        $owner = new class extends Model implements Revisioned
        {
            use HasConcurrencyRevisions;
        };
        $owner->exists = true;

        $this->expectException(UndeclaredConcurrencyRevision::class);

        EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);
    }

    #[Test]
    public function it_refuses_the_own_revision_as_a_nested_index_revision(): void
    {
        $owner = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        EntityTag::forNamedRevision($owner, ConcurrencyRevision::Self, ApiVersion::V2);
    }

    #[Test]
    public function it_refuses_an_api_version_that_is_not_a_token(): void
    {
        $owner = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, DottedApiVersion::V2Point1);
    }

    #[Test]
    public function it_refuses_an_api_version_that_is_not_a_token_before_it_reads_the_named_revision(): void
    {
        $owner = User::factory()->create();
        DB::enableQueryLog();

        try {
            EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, DottedApiVersion::V2Point1);
            $this->fail('A dotted API version was accepted.');
        } catch (InvalidArgumentException) {
            $this->assertSame([], DB::getQueryLog());
        }
    }

    #[Test]
    public function it_refuses_a_nested_index_tag_with_an_api_version_that_ends_in_a_line_feed(): void
    {
        $owner = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, LineFeedApiVersion::V2);
    }

    #[Test]
    public function it_parses_a_representation_tag(): void
    {
        $tag = EntityTag::parse('"v2.'.self::OWN.'.'.self::DIGEST.'"');

        $this->assertNotNull($tag);
        $this->assertSame('v2', $tag->api);
        $this->assertSame(self::OWN, $tag->revisions->own);
        $this->assertSame([], $tag->revisions->named);
        $this->assertSame(self::DIGEST, $tag->digest);
        $this->assertSame('"v2.'.self::OWN.'.'.self::DIGEST.'"', (string) $tag);
    }

    #[Test]
    public function it_parses_a_nested_index_tag(): void
    {
        $tag = EntityTag::parse('"v2.posts_order='.self::ORDER.'"');

        $this->assertNotNull($tag);
        $this->assertNull($tag->revisions->own);
        $this->assertSame(['posts_order' => self::ORDER], $tag->revisions->named);
        $this->assertNull($tag->digest);
        $this->assertSame('"v2.posts_order='.self::ORDER.'"', (string) $tag);
    }

    #[DataProvider('unparseableRepresentationTags')]
    #[DataProvider('unparseableNestedIndexTags')]
    #[Test]
    public function it_does_not_parse_a_weak_malformed_or_listed_tag(string $value): void
    {
        $this->assertNull(EntityTag::parse($value));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unparseableRepresentationTags(): array
    {
        return [
            'weak' => ['W/"v2.'.self::OWN.'.'.self::DIGEST.'"'],
            'unquoted' => ['v2.'.self::OWN.'.'.self::DIGEST],
            'empty' => ['""'],
            'api version only' => ['"v2"'],
            'own revision without a digest' => ['"v2.'.self::OWN.'"'],
            'digest without an own revision' => ['"v2.posts_order='.self::ORDER.'.'.self::DIGEST.'"'],
            'named revision in a representation tag' => ['"v2.'.self::OWN.'.posts_order='.self::ORDER.'.'.self::DIGEST.'"'],
            'short digest' => ['"v2.'.self::OWN.'.9f2c"'],
            'uppercase digest' => ['"v2.'.self::OWN.'.'.strtoupper(self::DIGEST).'"'],
            'short own revision' => ['"v2.3f1c.'.self::DIGEST.'"'],
            'uppercase own revision' => ['"v2.'.strtoupper(self::OWN).'.'.self::DIGEST.'"'],
            'integer own revision' => ['"v2.17.'.self::DIGEST.'"'],
            'trailing segment' => ['"v2.'.self::OWN.'.'.self::DIGEST.'.posts_order='.self::ORDER.'"'],
            'line feed after the digest' => ['"v2.'.self::OWN.'.'.self::DIGEST."\n".'"'],
            'wildcard' => ['*'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unparseableNestedIndexTags(): array
    {
        return [
            'integer named revision' => ['"v2.posts_order=4"'],
            'non-hex named revision' => ['"v2.posts_order=four"'],
            'short named revision' => ['"v2.posts_order=a1b2"'],
            'long named revision' => ['"v2.posts_order='.self::ORDER.'0"'],
            'uppercase named revision' => ['"v2.posts_order='.strtoupper(self::ORDER).'"'],
            'named revision without a value' => ['"v2.posts_order"'],
            'empty segment' => ['"v2..posts_order='.self::ORDER.'"'],
            'self by name' => ['"v2.self='.self::OWN.'"'],
            'repeated name' => ['"v2.posts_order='.self::ORDER.'.posts_order='.self::OWN.'"'],
            'two named revisions' => ['"v2.posts_order='.self::ORDER.'.notes_order='.self::OWN.'"'],
            'list' => ['"v2.posts_order='.self::ORDER.'", "v2.posts_order='.self::OWN.'"'],
            'line feed after the api version' => ["\"v2\n.posts_order=".self::ORDER.'"'],
            'line feed after a revision' => ['"v2.posts_order='.self::ORDER."\n\""],
            'empty name' => ['"v2.='.self::ORDER.'"'],
            'uppercase name' => ['"v2.Posts_order='.self::ORDER.'"'],
            'hyphenated name' => ['"v2.posts-order='.self::ORDER.'"'],
            'name with a leading digit' => ['"v2.1posts='.self::ORDER.'"'],
            'name with a leading underscore' => ['"v2._posts='.self::ORDER.'"'],
            'api version that is not a token' => ['"v,2.posts_order='.self::ORDER.'"'],
        ];
    }
}
