<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Tests\TestCase;

#[CoversClass(MergeUnlessNotSupported::class)]
class MergeUnlessNotSupportedTest extends TestCase
{
    #[Test]
    public function it_formats_the_message(): void
    {
        $exception = new MergeUnlessNotSupported;

        $this->assertSame(
            '`mergeUnless()` is not supported in ['.Schema::class.'] resources.',
            $exception->getMessage()
        );
    }
}
