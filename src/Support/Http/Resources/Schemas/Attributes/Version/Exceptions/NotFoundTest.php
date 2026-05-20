<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Version\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\TestCase;

#[CoversClass(NotFound::class)]
class NotFoundTest extends TestCase
{
    #[Test]
    public function it_formats_the_message(): void
    {
        $class = 'App\\Models\\User';
        $exception = new NotFound(ApiVersion::V3, $class);

        $this->assertSame(
            'No schema is defined for version ['.ApiVersion::class.'::V3] on ['.$class.'].',
            $exception->getMessage()
        );
    }
}
