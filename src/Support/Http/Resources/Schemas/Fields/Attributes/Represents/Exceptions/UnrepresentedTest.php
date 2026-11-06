<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Attributes\Represents\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Support\Money\Money;
use Tests\Fixtures\Support\Users\Status;
use Tests\Fixtures\Support\Users\Versions\V1\Status as V1Status;
use Tests\TestCase;

#[CoversClass(Unrepresented::class)]
class UnrepresentedTest extends TestCase
{
    #[Test]
    public function it_formats_the_message_for_an_enum_case(): void
    {
        $exception = new Unrepresented(Status::Draft, V1Status::class);

        $this->assertSame(
            'No case of ['.V1Status::class.'] represents ['.Status::class.'::Draft].',
            $exception->getMessage()
        );
    }

    #[Test]
    public function it_formats_the_message_for_an_object(): void
    {
        $exception = new Unrepresented(new Money(1), V1Status::class);

        $this->assertSame(
            'No case of ['.V1Status::class.'] represents ['.Money::class.'].',
            $exception->getMessage()
        );
    }
}
