<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Money\Versions\V1;

use JsonSerializable;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Exceptions\Unrepresented;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;
use Support\Http\Resources\Schemas\Fields\Contracts\Versioned;
use Tests\Fixtures\Support\Money\Money as Source;
use Tests\Fixtures\Support\Schemas\ApiVersion;

#[Version(ApiVersion::V1)]
final class Money implements JsonSerializable, Versioned
{
    public readonly float $amount;

    private function __construct(float $amount)
    {
        $this->amount = $amount;
    }

    public static function representing(Versionable $value): static
    {
        throw_unless($value instanceof Source, Unrepresented::class, $value, self::class);

        return new self($value->cents / 100);
    }

    public function jsonSerialize(): float
    {
        return $this->amount;
    }
}
