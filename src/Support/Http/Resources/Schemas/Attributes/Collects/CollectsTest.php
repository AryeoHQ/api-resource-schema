<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Collects;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\Collects\Exceptions\Invalid;
use Support\Http\Resources\Schemas\Attributes\Collects\Exceptions\NotDefined;
use Tests\Fixtures\Support\Users\Collection;
use Tests\Fixtures\Support\Users\Schemas;
use Tests\Fixtures\Support\Users\User;
use Tests\Fixtures\Tooling\PhpStan\SchemaCollections\MissingCollects;
use Tests\TestCase;

#[CoversClass(Collects::class)]
class CollectsTest extends TestCase
{
    #[Test]
    public function it_resolves_the_schema(): void
    {
        $this->assertSame(Schemas\V2\User::class, Collects::resolve(Collection\Schemas\V2\Users::class));
    }

    #[Test]
    public function it_throws_when_the_attribute_is_missing(): void
    {
        $this->expectException(NotDefined::class);

        Collects::resolve(MissingCollects::class);
    }

    #[Test]
    public function it_throws_for_a_class_that_is_not_a_schema(): void
    {
        $this->expectException(Invalid::class);

        new Collects(User::class);
    }
}
