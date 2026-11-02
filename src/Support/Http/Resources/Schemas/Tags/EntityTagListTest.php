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
    private const string OWN = '3f1c2b4a5d6e7f8091a2b3c4d5e6f708';

    private const string STALE = '0123456789abcdef0123456789abcdef';

    private const string OTHER = 'ffffffffffffffffffffffffffffffff';

    private const string ORDER = 'a1b2c3d4e5f60718293a4b5c6d7e8f90';

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
        $list = EntityTagList::parse(['"v2.'.self::STALE.'.'.self::DIGEST.'", W/"v2.'.self::OWN.'.'.self::DIGEST."\" \t,\"v2.posts_order=".self::ORDER.'"']);

        $this->assertNotNull($list);
        $this->assertFalse($list->any);
        $this->assertSame(['"v2.'.self::STALE.'.'.self::DIGEST.'"', 'W/"v2.'.self::OWN.'.'.self::DIGEST.'"', '"v2.posts_order='.self::ORDER.'"'], $list->tags);
    }

    #[Test]
    public function it_joins_every_header_line(): void
    {
        $this->assertSame(
            ['"v2.posts_order='.self::ORDER.'"', '"v2.posts_order='.self::STALE.'"', 'W/"v2.posts_order='.self::OTHER.'"'],
            EntityTagList::parse(['"v2.posts_order='.self::ORDER.'", "v2.posts_order='.self::STALE.'"', 'W/"v2.posts_order='.self::OTHER.'"'])?->tags,
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
        $this->assertTrue($this->list('*')->matchesRevisions(ApiVersion::V2, Revisions::of(self::OWN)));
    }

    #[Test]
    public function it_matches_the_compared_own_revision_whatever_the_digest(): void
    {
        $this->assertTrue($this->list('"v2.'.self::OWN.'.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(self::OWN)));
        $this->assertTrue($this->list('"v2.'.self::OWN.'.'.strrev(self::DIGEST).'"')->matchesRevisions(ApiVersion::V2, Revisions::of(self::OWN)));
    }

    #[Test]
    public function it_matches_the_compared_named_revision(): void
    {
        $this->assertTrue($this->list('"v2.posts_order='.self::ORDER.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => self::ORDER])));
    }

    #[Test]
    public function it_matches_only_when_the_tag_holds_every_compared_revision(): void
    {
        $compared = Revisions::of(self::OWN, ['posts_order' => self::ORDER]);

        $this->assertFalse($this->list('"v2.'.self::OWN.'.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, $compared));
        $this->assertFalse($this->list('"v2.posts_order='.self::ORDER.'"')->matchesRevisions(ApiVersion::V2, $compared));
    }

    #[Test]
    public function it_matches_any_tag_in_a_list(): void
    {
        $list = $this->list('"v2.'.self::STALE.'.'.self::DIGEST.'", W/"v2.'.self::OWN.'.'.self::DIGEST.'" ,"v2.'.self::OWN.'.'.strrev(self::DIGEST).'"');

        $this->assertTrue($list->matchesRevisions(ApiVersion::V2, Revisions::of(self::OWN)));
        $this->assertFalse($list->matchesRevisions(ApiVersion::V2, Revisions::of(self::OTHER)));
    }

    #[Test]
    public function it_does_not_match_a_stale_own_revision(): void
    {
        $this->assertFalse($this->list('"v2.'.self::STALE.'.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(self::OWN)));
    }

    #[Test]
    public function it_does_not_match_a_stale_named_revision(): void
    {
        $this->assertFalse($this->list('"v2.posts_order='.self::STALE.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => self::ORDER])));
    }

    #[Test]
    public function it_does_not_match_a_tag_missing_the_compared_revision(): void
    {
        $this->assertFalse($this->list('"v2.'.self::OWN.'.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(null, ['posts_order' => self::ORDER])));
        $this->assertFalse($this->list('"v2.posts_order='.self::ORDER.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(self::OWN)));
    }

    #[Test]
    public function it_does_not_match_another_api_version(): void
    {
        $this->assertFalse($this->list('"v1.'.self::OWN.'.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(self::OWN)));
    }

    #[Test]
    public function it_does_not_match_revisions_against_a_weak_unparseable_or_empty_list(): void
    {
        $this->assertFalse($this->list('W/"v2.'.self::OWN.'.'.self::DIGEST.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(self::OWN)));
        $this->assertFalse($this->list('"v2.'.self::OWN.'"')->matchesRevisions(ApiVersion::V2, Revisions::of(self::OWN)));
        $this->assertFalse($this->list('')->matchesRevisions(ApiVersion::V2, Revisions::of(self::OWN)));
    }

    #[Test]
    public function it_matches_any_tag_weakly_with_a_wildcard(): void
    {
        $this->assertTrue($this->list('*')->matchesWeakly('"v2.'.self::OWN.'.'.self::DIGEST.'"'));
    }

    #[Test]
    public function it_matches_a_tag_weakly_whatever_either_weak_prefix(): void
    {
        $etag = '"v2.'.self::OWN.'.'.self::DIGEST.'"';

        $this->assertTrue($this->list('"v2.'.self::STALE.'.'.self::DIGEST.'", '.$etag)->matchesWeakly($etag));
        $this->assertTrue($this->list('W/'.$etag)->matchesWeakly($etag));
        $this->assertTrue($this->list($etag)->matchesWeakly('W/'.$etag));
    }

    #[Test]
    public function it_compares_the_whole_tag_weakly_digest_included(): void
    {
        $this->assertFalse($this->list('"v2.'.self::OWN.'.'.strrev(self::DIGEST).'"')->matchesWeakly('"v2.'.self::OWN.'.'.self::DIGEST.'"'));
        $this->assertFalse($this->list('"v2.'.self::OWN.'"')->matchesWeakly('"v2.'.self::OWN.'.'.self::DIGEST.'"'));
        $this->assertFalse($this->list('')->matchesWeakly('"v2.'.self::OWN.'.'.self::DIGEST.'"'));
    }

    private function list(string $field): EntityTagList
    {
        $list = EntityTagList::parse([$field]);

        $this->assertNotNull($list);

        return $list;
    }
}
