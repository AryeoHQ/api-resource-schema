<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Database\Eloquent\Concurrency\Revisions;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\TestCase;

#[CoversClass(EntityTagList::class)]
class EntityTagListTest extends TestCase
{
    private const string DIGEST = '9f2c1a0b7e3d5c4a9f2c1a0b7e3d5c4a';

    #[Test]
    public function it_parses_a_wildcard(): void
    {
        $list = EntityTagList::parse(['*']);

        $this->assertNotNull($list);
        $this->assertTrue($list->any);
        $this->assertSame([], $list->tags);
    }

    #[Test]
    public function it_parses_a_wildcard_surrounded_by_whitespace(): void
    {
        $this->assertTrue(EntityTagList::parse([" \t* "])?->any);
    }

    #[Test]
    public function it_parses_a_list_of_strong_and_weak_tags(): void
    {
        $list = EntityTagList::parse(['"v2.16.'.self::DIGEST.'", W/"v2.17.'.self::DIGEST."\" \t,\"v2.posts_order=4\""]);

        $this->assertNotNull($list);
        $this->assertFalse($list->any);
        $this->assertSame(['"v2.16.'.self::DIGEST.'"', 'W/"v2.17.'.self::DIGEST.'"', '"v2.posts_order=4"'], $list->tags);
    }

    #[Test]
    public function it_joins_every_header_line(): void
    {
        $this->assertSame(
            ['"v2.posts_order=4"', '"v2.posts_order=5"', 'W/"v2.posts_order=6"'],
            EntityTagList::parse(['"v2.posts_order=4", "v2.posts_order=5"', 'W/"v2.posts_order=6"'])?->tags,
        );
    }

    #[Test]
    public function it_keeps_a_comma_inside_an_opaque_tag(): void
    {
        $this->assertSame(['"a,b"', '"c"'], EntityTagList::parse(['"a,b", "c"'])?->tags);
    }

    #[Test]
    public function it_ignores_empty_list_elements(): void
    {
        $this->assertSame(['"a"', '"b"'], EntityTagList::parse([', "a" ,, "b",'])?->tags);
    }

    /**
     * @param  list<null|string>  $lines
     */
    #[DataProvider('emptyFields')]
    #[Test]
    public function it_parses_an_empty_field_as_no_tags(array $lines): void
    {
        $list = EntityTagList::parse($lines);

        $this->assertNotNull($list);
        $this->assertFalse($list->any);
        $this->assertSame([], $list->tags);
    }

    /**
     * @return array<string, array{list<null|string>}>
     */
    public static function emptyFields(): array
    {
        return [
            'no lines' => [[]],
            'an empty line' => [['']],
            'whitespace' => [[" \t"]],
            'a null line' => [[null]],
        ];
    }

    /**
     * @param  list<string>  $lines
     */
    #[DataProvider('malformed')]
    #[Test]
    public function it_does_not_parse_a_malformed_field(array $lines): void
    {
        $this->assertNull(EntityTagList::parse($lines));
    }

    /**
     * @return array<string, array{list<string>}>
     */
    public static function malformed(): array
    {
        return [
            'a wildcard before a tag' => [['*, "a"']],
            'a wildcard after a tag' => [['"a", *']],
            'a wildcard on a later line' => [['"a"', '*']],
            'a wildcard on two lines' => [['*', '*']],
            'an unquoted element' => [['"a", b']],
            'an unterminated tag' => [['"a']],
            'a lowercase weak prefix' => [['w/"a"']],
            'a space inside a tag' => [['"a b"']],
            'two tags without a comma' => [['"a" "b"']],
            'a malformed element on another line' => [['"a"', 'garbage']],
        ];
    }

    #[Test]
    public function it_matches_any_revisions_with_a_wildcard(): void
    {
        $this->assertTrue($this->list('*')->matchesRevisions(ApiVersion::V2, Revisions::of(17)));
    }

    #[Test]
    public function it_matches_the_compared_own_revision_whatever_the_digest(): void
    {
        $this->assertTrue($this->list('"v2.17.posts_order=4.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(17)));
        $this->assertTrue($this->list('"v2.17.posts_order=9.'.strrev(self::DIGEST).'"')->matchesRevisions(ApiVersion::V2, Revisions::of(17)));
    }

    #[Test]
    public function it_matches_the_compared_named_revision(): void
    {
        $this->assertTrue($this->list('"v2.posts_order=4"')->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => 4])));
        $this->assertTrue($this->list('"v2.16.posts_order=4.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => 4])));
    }

    #[Test]
    public function it_matches_every_compared_revision(): void
    {
        $list = $this->list('"v2.17.posts_order=4.'.self::DIGEST.'"');

        $this->assertTrue($list->matchesRevisions(ApiVersion::V2, Revisions::of(17, ['posts_order' => 4])));
        $this->assertFalse($list->matchesRevisions(ApiVersion::V2, Revisions::of(17, ['posts_order' => 5])));
    }

    #[Test]
    public function it_matches_any_tag_in_a_list(): void
    {
        $list = $this->list('"v2.16.'.self::DIGEST.'", W/"v2.17.'.self::DIGEST.'" ,"v2.17.posts_order=4.'.self::DIGEST.'"');

        $this->assertTrue($list->matchesRevisions(ApiVersion::V2, Revisions::of(17)));
        $this->assertFalse($list->matchesRevisions(ApiVersion::V2, Revisions::of(18)));
    }

    #[Test]
    public function it_does_not_match_a_stale_own_revision(): void
    {
        $this->assertFalse($this->list('"v2.16.posts_order=4.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(17)));
    }

    #[Test]
    public function it_does_not_match_a_stale_named_revision(): void
    {
        $this->assertFalse($this->list('"v2.posts_order=3"')->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => 4])));
    }

    #[Test]
    public function it_does_not_match_a_tag_missing_the_compared_revision(): void
    {
        $this->assertFalse($this->list('"v2.17.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => 4])));
        $this->assertFalse($this->list('"v2.posts_order=4"')->matchesRevisions(ApiVersion::V2, Revisions::of(17)));
    }

    #[Test]
    public function it_does_not_match_another_api_version(): void
    {
        $this->assertFalse($this->list('"v1.17.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(17)));
    }

    #[Test]
    public function it_does_not_match_revisions_against_a_weak_unparseable_or_empty_list(): void
    {
        $this->assertFalse($this->list('W/"v2.17.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(17)));
        $this->assertFalse($this->list('"v2.17"')->matchesRevisions(ApiVersion::V2, Revisions::of(17)));
        $this->assertFalse($this->list('')->matchesRevisions(ApiVersion::V2, Revisions::of(17)));
    }

    #[Test]
    public function it_matches_any_tag_weakly_with_a_wildcard(): void
    {
        $this->assertTrue($this->list('*')->matchesWeakly('"v2.17.'.self::DIGEST.'"'));
    }

    #[Test]
    public function it_matches_a_tag_weakly_whatever_either_weak_prefix(): void
    {
        $etag = '"v2.17.'.self::DIGEST.'"';

        $this->assertTrue($this->list('"v2.16.'.self::DIGEST.'", '.$etag)->matchesWeakly($etag));
        $this->assertTrue($this->list('W/'.$etag)->matchesWeakly($etag));
        $this->assertTrue($this->list($etag)->matchesWeakly('W/'.$etag));
    }

    #[Test]
    public function it_compares_the_whole_tag_weakly_digest_included(): void
    {
        $this->assertFalse($this->list('"v2.17.'.strrev(self::DIGEST).'"')->matchesWeakly('"v2.17.'.self::DIGEST.'"'));
        $this->assertFalse($this->list('"v2.17"')->matchesWeakly('"v2.17.'.self::DIGEST.'"'));
        $this->assertFalse($this->list('')->matchesWeakly('"v2.17.'.self::DIGEST.'"'));
    }

    private function list(string $field): EntityTagList
    {
        $list = EntityTagList::parse([$field]);

        $this->assertNotNull($list);

        return $list;
    }
}
