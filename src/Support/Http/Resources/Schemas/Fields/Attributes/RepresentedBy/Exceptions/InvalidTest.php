<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Fields\Contracts\Versioned;
use Tests\TestCase;

#[CoversClass(Invalid::class)]
class InvalidTest extends TestCase
{
    #[Test]
    public function it_formats_the_message(): void
    {
        $class = 'App\\Api\\Status';
        $exception = new Invalid($class);

        $this->assertSame(
            "[$class] must implement [".Versioned::class.'].',
            $exception->getMessage()
        );
    }
}
