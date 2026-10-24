<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Database\Eloquent\Concurrency\ConcurrencyRevision;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Schemas\DottedApiVersion;
use Tests\Fixtures\Support\Users\User;
use Tests\Fixtures\Support\Users\UserRevision;
use Tests\TestCase;

#[CoversClass(EntityTag::class)]
class EntityTagTest extends TestCase
{
    private const string DIGEST = '9f2c1a0b7e3d5c4a9f2c1a0b7e3d5c4a';

    #[Test]
    public function it_tags_a_nested_index_with_the_api_version_and_the_named_revision(): void
    {
        $owner = User::factory()->state(['concurrency_revision' => 17, 'posts_order_concurrency_revision' => 4])->create();

        $tag = EntityTag::forNamedRevision($owner, UserRevision::PostsOrder, ApiVersion::V2);

        $this->assertSame('"v2.posts_order=4"', (string) $tag);
        $this->assertNull($tag->digest);
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
        $tag = EntityTag::parse('"v2.17.posts_order=4.'.self::DIGEST.'"');

        $this->assertNotNull($tag);
        $this->assertSame('v2', $tag->api);
        $this->assertSame(['self' => 17, 'posts_order' => 4], $tag->revisions);
        $this->assertSame(self::DIGEST, $tag->digest);
        $this->assertSame('"v2.17.posts_order=4.'.self::DIGEST.'"', (string) $tag);
    }

    #[Test]
    public function it_parses_a_nested_index_tag(): void
    {
        $tag = EntityTag::parse('"v2.posts_order=4"');

        $this->assertNotNull($tag);
        $this->assertSame(['posts_order' => 4], $tag->revisions);
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
        ];
    }

    #[Test]
    public function it_refuses_to_compare_no_revisions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        EntityTag::matchesAny('"v2.posts_order=999"', ApiVersion::V2, []);
    }

    #[Test]
    public function it_matches_any_tag_with_a_wildcard(): void
    {
        $this->assertTrue(EntityTag::matchesAny('*', ApiVersion::V2, ['self' => 17]));
    }

    #[Test]
    public function it_matches_the_compared_own_revision_whatever_the_digest(): void
    {
        $this->assertTrue(EntityTag::matchesAny('"v2.17.posts_order=4.'.self::DIGEST.'"', ApiVersion::V2, ['self' => 17]));
        $this->assertTrue(EntityTag::matchesAny('"v2.17.posts_order=9.'.strrev(self::DIGEST).'"', ApiVersion::V2, ['self' => 17]));
    }

    #[Test]
    public function it_matches_the_compared_named_revision(): void
    {
        $this->assertTrue(EntityTag::matchesAny('"v2.posts_order=4"', ApiVersion::V2, ['posts_order' => 4]));
        $this->assertTrue(EntityTag::matchesAny('"v2.16.posts_order=4.'.self::DIGEST.'"', ApiVersion::V2, ['posts_order' => 4]));
    }

    #[Test]
    public function it_matches_every_compared_revision(): void
    {
        $tag = '"v2.17.posts_order=4.'.self::DIGEST.'"';

        $this->assertTrue(EntityTag::matchesAny($tag, ApiVersion::V2, ['self' => 17, 'posts_order' => 4]));
        $this->assertFalse(EntityTag::matchesAny($tag, ApiVersion::V2, ['self' => 17, 'posts_order' => 5]));
    }

    #[Test]
    public function it_matches_any_tag_in_a_list(): void
    {
        $list = '"v2.16.'.self::DIGEST.'", W/"v2.17.'.self::DIGEST.'" ,"v2.17.posts_order=4.'.self::DIGEST.'"';

        $this->assertTrue(EntityTag::matchesAny($list, ApiVersion::V2, ['self' => 17]));
        $this->assertFalse(EntityTag::matchesAny($list, ApiVersion::V2, ['self' => 18]));
    }

    #[Test]
    public function it_does_not_match_a_stale_own_revision(): void
    {
        $this->assertFalse(EntityTag::matchesAny('"v2.16.posts_order=4.'.self::DIGEST.'"', ApiVersion::V2, ['self' => 17]));
    }

    #[Test]
    public function it_does_not_match_a_stale_named_revision(): void
    {
        $this->assertFalse(EntityTag::matchesAny('"v2.posts_order=3"', ApiVersion::V2, ['posts_order' => 4]));
    }

    #[Test]
    public function it_does_not_match_a_tag_missing_the_compared_revision(): void
    {
        $this->assertFalse(EntityTag::matchesAny('"v2.17.'.self::DIGEST.'"', ApiVersion::V2, ['posts_order' => 4]));
        $this->assertFalse(EntityTag::matchesAny('"v2.posts_order=4"', ApiVersion::V2, ['self' => 17]));
    }

    #[Test]
    public function it_does_not_match_another_api_version(): void
    {
        $this->assertFalse(EntityTag::matchesAny('"v1.17.'.self::DIGEST.'"', ApiVersion::V2, ['self' => 17]));
    }

    #[Test]
    public function it_does_not_match_a_weak_or_malformed_tag(): void
    {
        $this->assertFalse(EntityTag::matchesAny('W/"v2.17.'.self::DIGEST.'"', ApiVersion::V2, ['self' => 17]));
        $this->assertFalse(EntityTag::matchesAny('"v2.17"', ApiVersion::V2, ['self' => 17]));
        $this->assertFalse(EntityTag::matchesAny('', ApiVersion::V2, ['self' => 17]));
    }
}
