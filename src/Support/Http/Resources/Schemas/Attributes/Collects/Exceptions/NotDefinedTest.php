<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Collects\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\Collects\Collects;
use Tests\TestCase;

#[CoversClass(NotDefined::class)]
class NotDefinedTest extends TestCase
{
    #[Test]
    public function it_formats_the_message(): void
    {
        $class = 'App\\SchemaCollections\\V2\\Users';
        $exception = new NotDefined($class);

        $this->assertSame(
            "[$class] does not define #[".Collects::class.'].',
            $exception->getMessage()
        );
    }
}
