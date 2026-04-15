<?php

declare(strict_types=1);

namespace Support\Http\Resources;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\Exceptions\CollectsNotDefined;
use Support\Http\Resources\Schemas\Provides\AsSchemaCollection;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Posts\Schemas;
use Tests\Fixtures\Tooling\PhpStan\SchemaCollections\MissingCollects;
use Tests\TestCase;

#[CoversTrait(AsSchemaCollection::class)]
class SchemaCollectionTest extends TestCase
{
    #[Test]
    public function it_collects_schema(): void
    {
        $this->assertSame(
            Schemas\Post::class,
            new Schemas\Posts(Post::factory()->times(2)->make())->collects
        );
    }

    #[Test]
    public function it_throws_an_exception_when_collects_is_missing(): void
    {
        $this->expectException(CollectsNotDefined::class);

        new MissingCollects([]);
    }
}
