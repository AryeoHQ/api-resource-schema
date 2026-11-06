<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\UseSchemaCollection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\Exceptions\Invalid;
use Tests\Fixtures\Support\Users\Collection;
use Tests\Fixtures\Support\Users\User;
use Tests\TestCase;

#[CoversClass(UseSchemaCollection::class)]
class UseSchemaCollectionTest extends TestCase
{
    #[Test]
    public function it_resolves_every_schema_collection(): void
    {
        $this->assertSame(
            [Collection\Schemas\V1\Users::class, Collection\Schemas\V2\Users::class],
            UseSchemaCollection::resolve(Collection\Users::class)->all()
        );
    }

    #[Test]
    public function it_throws_for_a_class_that_is_not_a_schema_collection(): void
    {
        $this->expectException(Invalid::class);

        new UseSchemaCollection(User::class);
    }
}
