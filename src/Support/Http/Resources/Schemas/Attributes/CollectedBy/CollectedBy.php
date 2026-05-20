<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\CollectedBy;

use Attribute;
use ReflectionClass;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\Exceptions\Invalid;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;

#[Attribute(Attribute::TARGET_CLASS)]
final class CollectedBy
{
    public string $schemaCollection;

    public function __construct(string $schemaCollection)
    {
        throw_unless(
            is_a($schemaCollection, SchemaCollection::class, true), // @phpstan-ignore-line
            Invalid::class,
            $schemaCollection
        );

        $this->schemaCollection = $schemaCollection;
    }

    /**
     * @param  class-string  $class
     */
    public static function resolve(string $class): string
    {
        $schemaCollection = data_get((new ReflectionClass($class))->getAttributes(self::class), 0)?->newInstance()?->schemaCollection;

        throw_unless($schemaCollection, Exceptions\NotDefined::class, $class);

        return $schemaCollection;
    }
}
