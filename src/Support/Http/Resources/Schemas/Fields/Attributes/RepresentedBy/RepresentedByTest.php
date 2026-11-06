<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\Exceptions\Invalid;
use Tests\Fixtures\Support\Users\Status;
use Tests\Fixtures\Support\Users\Versions;
use Tests\TestCase;

#[CoversClass(RepresentedBy::class)]
class RepresentedByTest extends TestCase
{
    #[Test]
    public function it_resolves_every_target(): void
    {
        $this->assertSame([Versions\V1\Status::class, Versions\V2\Status::class], RepresentedBy::resolve(Status::class)->all());
    }

    #[Test]
    public function it_throws_for_a_class_that_is_not_versioned(): void
    {
        $this->expectException(Invalid::class);

        new RepresentedBy(Status::class);
    }
}
