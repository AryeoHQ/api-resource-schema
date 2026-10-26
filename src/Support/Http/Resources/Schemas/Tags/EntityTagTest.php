<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Database\Eloquent\Concurrency\ConcurrencyRevision;
use Support\Database\Eloquent\Concurrency\Revisions;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Schemas\DottedApiVersion;
use Tests\Fixtures\Support\Users\User;
use Tests\Fixtures\Support\Users\UserRevision;
use Tests\TestCase;

#[CoversClass(EntityTag::class)]
#[CoversClass(EntityTagList::class)]
class EntityTagTest extends TestCase
{
    private const string DIGEST = '9f2c1a0b7e3d5c4a9f2c1a0b7e3d5c4a';

    #[Test]
    public function it_tags_a_representation_with_the_api_version_the_revisions_and_the_digest(): void
    {
        $user = User::factory()->state(['concurrency_revision' => 17, 'posts_order_concurrency_revision' => 4])->create();

        $tag = EntityTag::forRepresentation($user, ApiVersion::V2, '{"data":[]}');

        $this->assertNotNull($tag);
        $this->assertSame('"v2.17.posts_order=4.'.hash('xxh128', '{"data":[]}').'"', (string) $tag);
        $this->assertEquals(Revisions::of(17, ['posts_order' => 4]), $tag->revisions);
    }

    #[Test]
    public function it_parses_and_matches_the_representation_tag_it_issues(): void
    {
        $user = User::factory()->state(['concurrency_revision' => 17, 'posts_order_concurrency_revision' => 4])->create();
        $issued = (string) EntityTag::forRepresentation($user, ApiVersion::V2, '{"data":[]}');

        $parsed = EntityTag::parse($issued);
        $list = EntityTagList::parse([$issued]);

        $this->assertNotNull($parsed);
        $this->assertSame($issued, (string) $parsed);
        $this->assertEquals($user->concurrencyRevisions(), $parsed->revisions);
        $this->assertNotNull($list);
        $this->assertTrue($list->matchesRevisions(ApiVersion::V2, Revisions::of($user->concurrencyRevision())));
        $this->assertTrue($list->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => $user->concurrencyRevision(UserRevision::PostsOrder)])));
        $this->assertTrue($list->matchesWeakly($issued));
    }

    #[Test]
    public function it_does_not_tag_a_representation_without_its_revisions(): void
    {
        $this->assertNull(EntityTag::forRepresentation(User::factory()->make(), ApiVersion::V2, '{}'));
    }

    #[Test]
    public function it_tags_a_nested_index_with_the_api_version_and_the_named_revision(): void
    {
        $owner = User::factory()->state(['concurrency_revision' => 17, 'posts_order_concurrency_revision' => 4])->create();

        $tag = EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);

        $this->assertSame('"v2.posts_order=4"', (string) $tag);
        $this->assertEquals(Revisions::of(null, ['posts_order' => 4]), $tag->revisions);
        $this->assertNull($tag->digest);
    }

    #[Test]
    public function it_parses_and_matches_the_nested_index_tag_it_issues(): void
    {
        $owner = User::factory()->state(['posts_order_concurrency_revision' => 4])->create();
        $issued = (string) EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);

        $this->assertSame($issued, (string) EntityTag::parse($issued));
        $this->assertTrue(EntityTagList::parse([$issued])?->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => 4])));
    }

    #[Test]
    public function it_refuses_a_nested_index_tag_for_an_owner_loaded_without_the_named_revision(): void
    {
        $created = User::factory()->state(['posts_order_concurrency_revision' => 4])->create();
        $owner = User::query()->select(['id', 'concurrency_revision'])->findOrFail($created->getKey());

        $this->expectException(LogicException::class);

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
    public function it_parses_a_representation_tag(): void
    {
        $tag = EntityTag::parse('"v2.17.posts_order=4.notes_order=0.'.self::DIGEST.'"');

        $this->assertNotNull($tag);
        $this->assertSame('v2', $tag->api);
        $this->assertSame(17, $tag->revisions->own);
        $this->assertSame(['posts_order' => 4, 'notes_order' => 0], $tag->revisions->named);
        $this->assertSame(self::DIGEST, $tag->digest);
        $this->assertSame('"v2.17.posts_order=4.notes_order=0.'.self::DIGEST.'"', (string) $tag);
    }

    #[Test]
    public function it_parses_a_nested_index_tag(): void
    {
        $tag = EntityTag::parse('"v2.posts_order=4"');

        $this->assertNotNull($tag);
        $this->assertNull($tag->revisions->own);
        $this->assertSame(['posts_order' => 4], $tag->revisions->named);
        $this->assertNull($tag->digest);
        $this->assertSame('"v2.posts_order=4"', (string) $tag);
    }

    #[DataProvider('unparseable')]
    #[Test]
    public function it_does_not_parse_a_weak_malformed_or_listed_tag(string $value): void
    {
        $this->assertNull(EntityTag::parse($value));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unparseable(): array
    {
        return [
            'weak' => ['W/"v2.17.'.self::DIGEST.'"'],
            'unquoted' => ['v2.17.'.self::DIGEST],
            'empty' => ['""'],
            'api version only' => ['"v2"'],
            'own revision without a digest' => ['"v2.17"'],
            'digest without an own revision' => ['"v2.posts_order=4.'.self::DIGEST.'"'],
            'short digest' => ['"v2.17.9f2c"'],
            'uppercase digest' => ['"v2.17.'.strtoupper(self::DIGEST).'"'],
            'non-numeric revision' => ['"v2.posts_order=four"'],
            'negative revision' => ['"v2.posts_order=-4"'],
            'leading zero' => ['"v2.017.'.self::DIGEST.'"'],
            'overflowing revision' => ['"v2.99999999999999999999.'.self::DIGEST.'"'],
            'named revision without a value' => ['"v2.posts_order"'],
            'empty segment' => ['"v2..posts_order=4"'],
            'self by name' => ['"v2.self=17"'],
            'repeated name' => ['"v2.posts_order=4.posts_order=5"'],
            'trailing segment' => ['"v2.17.'.self::DIGEST.'.posts_order=4"'],
            'list' => ['"v2.posts_order=4", "v2.posts_order=5"'],
            'wildcard' => ['*'],
            'empty name' => ['"v2.=4"'],
            'uppercase name' => ['"v2.Posts_order=4"'],
            'hyphenated name' => ['"v2.posts-order=4"'],
            'name with a leading digit' => ['"v2.1posts=4"'],
            'name with a leading underscore' => ['"v2._posts=4"'],
            'api version that is not a token' => ['"v,2.posts_order=4"'],
        ];
    }
}
