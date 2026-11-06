<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\CollectedBy;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\Exceptions\Invalid;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\Exceptions\NotDefined;
use Tests\Fixtures\Support\Users\Collection;
use Tests\Fixtures\Support\Users\Schemas;
use Tests\Fixtures\Support\Users\User;
use Tests\Fixtures\Tooling\PhpStan\Schemas\MissingCollectedBy;
use Tests\TestCase;

#[CoversClass(CollectedBy::class)]
class CollectedByTest extends TestCase
{
    #[Test]
    public function it_resolves_the_schema_collection(): void
    {
        $this->assertSame(Collection\Schemas\V2\Users::class, CollectedBy::resolve(Schemas\V2\User::class));
    }

    #[Test]
    public function it_throws_when_the_attribute_is_missing(): void
    {
        $this->expectException(NotDefined::class);

        CollectedBy::resolve(MissingCollectedBy::class);
    }

    #[Test]
    public function it_throws_for_a_class_that_is_not_a_schema_collection(): void
    {
        $this->expectException(Invalid::class);

        new CollectedBy(User::class);
    }
}
