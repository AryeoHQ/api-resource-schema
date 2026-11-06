<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Provides;

use ReflectionAttribute;
use ReflectionEnumUnitCase;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Exceptions\Unrepresented;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;

/**
 * @mixin \Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum
 */
trait AsVersionedEnum
{
    public static function representing(Versionable $value): static
    {
        $case = collect(static::cases())->first(
            fn (self $case): bool => collect((new ReflectionEnumUnitCase(static::class, $case->name))->getAttributes(Represents::class))
                ->contains(fn (ReflectionAttribute $attribute): bool => in_array($value, $attribute->newInstance()->cases, true))
        );

        throw_unless($case, Unrepresented::class, $value, static::class);

        return $case;
    }
}
