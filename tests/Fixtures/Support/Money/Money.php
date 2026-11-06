<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Money;

use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;

#[RepresentedBy(Versions\V1\Money::class)]
final class Money implements Versionable
{
    use TransformsToVersion;

    public readonly int $cents;

    public function __construct(int $cents)
    {
        $this->cents = $cents;
    }
}
