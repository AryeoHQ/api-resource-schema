<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\Collects;

use Attribute;
use ReflectionClass;
use Support\Http\Resources\Schemas\Attributes\Collects\Exceptions\Invalid;
use Support\Http\Resources\Schemas\Contracts\Schema;

#[Attribute(Attribute::TARGET_CLASS)]
final class Collects
{
    public string $schema;

    public function __construct(string $schema)
    {
        throw_unless(
            is_a($schema, Schema::class, true), // @phpstan-ignore-line
            Invalid::class,
            $schema
        );

        $this->schema = $schema;
    }

    /**
     * @param  class-string  $class
     */
    public static function resolve(string $class): string
    {
        $schema = data_get((new ReflectionClass($class))->getAttributes(self::class), 0)?->newInstance()?->schema;

        throw_unless($schema, Exceptions\NotDefined::class, $class);

        return $schema;
    }
}
