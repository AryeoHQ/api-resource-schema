<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\UseSchemaCollection;

use Attribute;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use ReflectionClass;
use Support\Http\Resources\Schemas\Attributes\UseSchemaCollection\Exceptions\Invalid;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class UseSchemaCollection
{
    public string $schemaCollection;

    public function __construct(string $schemaCollection)
    {
        throw_unless(
            is_a($schemaCollection, SchemaCollection::class, true),
            Invalid::class,
            $schemaCollection
        );

        $this->schemaCollection = $schemaCollection;
    }

    /**
     * @param  class-string  $class
     * @return Collection<int, class-string<SchemaCollection&ResourceCollection>>
     */
    public static function resolve(string $class): Collection
    {
        /** @var Collection<int, class-string<SchemaCollection&ResourceCollection>> $schemaCollections */
        $schemaCollections = collect((new ReflectionClass($class))->getAttributes(self::class))
            ->map(fn (\ReflectionAttribute $attribute): string => $attribute->newInstance()->schemaCollection)
            ->values();

        return $schemaCollections;
    }
}
