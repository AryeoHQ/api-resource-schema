<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(Tag::class)]
class TagTest extends TestCase
{
    #[Test]
    public function it_hashes_bytes_into_a_strong_tag(): void
    {
        $this->assertSame('"'.hash('xxh128', 'bytes').'"', Tag::hash('bytes')->value);
    }

    #[Test]
    public function it_casts_to_its_quoted_value(): void
    {
        $this->assertSame('"abc"', (string) new Tag('"abc"'));
    }

    #[Test]
    public function it_rejects_a_weak_tag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Tag('W/"abc"');
    }

    #[Test]
    public function it_rejects_an_unquoted_tag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Tag('abc');
    }

    #[Test]
    public function it_matches_the_same_tag(): void
    {
        $this->assertTrue((new Tag('"abc"'))->matches('"abc"'));
    }

    #[Test]
    public function it_does_not_match_a_different_tag(): void
    {
        $this->assertFalse((new Tag('"abc"'))->matches('"abd"'));
    }

    #[Test]
    public function it_matches_any_tag_in_a_list(): void
    {
        $this->assertTrue((new Tag('"abc"'))->matches('"x", "abc" ,"y"'));
    }

    #[Test]
    public function it_ignores_empty_list_elements(): void
    {
        $this->assertTrue((new Tag('"abc"'))->matches(', "x",, "abc",'));
    }

    #[Test]
    public function it_matches_a_tag_that_contains_a_comma(): void
    {
        $this->assertTrue((new Tag('"a,b"'))->matches('"x", "a,b"'));
    }

    #[Test]
    public function it_matches_the_wildcard(): void
    {
        $this->assertTrue((new Tag('"abc"'))->matches('*'));
    }

    #[Test]
    public function it_never_matches_a_weak_tag(): void
    {
        $this->assertFalse((new Tag('"abc"'))->matches('W/"abc"'));
    }

    #[Test]
    public function it_never_matches_a_weak_tag_in_a_list(): void
    {
        $this->assertFalse((new Tag('"abc"'))->matches('"x", W/"abc"'));
    }

    #[Test]
    public function it_matches_a_strong_tag_beside_a_weak_one(): void
    {
        $this->assertTrue((new Tag('"abc"'))->matches('W/"x", "abc"'));
    }

    #[Test]
    public function it_does_not_match_an_unquoted_value(): void
    {
        $this->assertFalse((new Tag('"abc"'))->matches('abc'));
    }

    #[Test]
    public function it_does_not_match_a_malformed_list(): void
    {
        $this->assertFalse((new Tag('"abc"'))->matches('"abc" "x"'));
    }

    #[Test]
    public function it_does_not_match_a_wildcard_inside_a_list(): void
    {
        $this->assertFalse((new Tag('"abc"'))->matches('*, "x"'));
    }
}
