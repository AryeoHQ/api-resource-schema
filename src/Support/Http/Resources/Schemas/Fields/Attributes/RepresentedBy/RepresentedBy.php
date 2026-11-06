<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy;

use Attribute;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use Support\Http\Resources\Schemas\Fields\Contracts\Versioned;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class RepresentedBy
{
    /** @var class-string<Versioned> */
    public string $versioned;

    public function __construct(string $versioned)
    {
        throw_unless(is_a($versioned, Versioned::class, true), Exceptions\Invalid::class, $versioned);

        $this->versioned = $versioned;
    }

    /**
     * @param  class-string  $class
     * @return Collection<int, class-string<Versioned>>
     */
    public static function resolve(string $class): Collection
    {
        return collect((new ReflectionClass($class))->getAttributes(self::class))
            ->map(fn (ReflectionAttribute $attribute): string => $attribute->newInstance()->versioned)
            ->values();
    }
}
