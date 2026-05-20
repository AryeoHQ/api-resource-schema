<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\UseSchema;

use Attribute;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use ReflectionClass;
use Support\Http\Resources\Schemas\Attributes\UseSchema\Exceptions\Invalid;
use Support\Http\Resources\Schemas\Contracts\Schema;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class UseSchema
{
    public string $schema;

    public function __construct(string $schema)
    {
        throw_unless(
            is_a($schema, Schema::class, true),
            Invalid::class,
            $schema
        );

        $this->schema = $schema;
    }

    /**
     * @param  class-string  $class
     * @return Collection<int, class-string<Schema&JsonResource>>
     */
    public static function resolve(string $class): Collection
    {
        /** @var Collection<int, class-string<Schema&JsonResource>> $schemas */
        $schemas = collect((new ReflectionClass($class))->getAttributes(self::class))
            ->map(fn (\ReflectionAttribute $attribute): string => $attribute->newInstance()->schema)
            ->values();

        return $schemas;
    }
}
