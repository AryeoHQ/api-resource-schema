<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\UseSchema;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\UseSchema\Exceptions\Invalid;
use Tests\Fixtures\Support\Users\Schemas;
use Tests\Fixtures\Support\Users\User;
use Tests\TestCase;

#[CoversClass(UseSchema::class)]
class UseSchemaTest extends TestCase
{
    #[Test]
    public function it_resolves_every_schema(): void
    {
        $this->assertSame([Schemas\V1\User::class, Schemas\V2\User::class], UseSchema::resolve(User::class)->all());
    }

    #[Test]
    public function it_throws_for_a_class_that_is_not_a_schema(): void
    {
        $this->expectException(Invalid::class);

        new UseSchema(User::class);
    }
}
