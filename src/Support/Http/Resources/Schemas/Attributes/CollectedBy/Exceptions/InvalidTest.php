<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\CollectedBy\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;
use Tests\TestCase;

#[CoversClass(Invalid::class)]
class InvalidTest extends TestCase
{
    #[Test]
    public function it_formats_the_message(): void
    {
        $class = 'App\\Schemas\\Users';
        $exception = new Invalid($class);

        $this->assertSame(
            "[$class] must implement [".SchemaCollection::class.'].',
            $exception->getMessage()
        );
    }
}
