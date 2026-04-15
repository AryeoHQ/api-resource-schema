<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Provides;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionObject;
use Support\Http\Resources\Schemas\Attributes\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Exceptions\CollectedByNotDefined;
use Support\Http\Resources\Schemas\Concerns\ConditionallyLoadsAttributes;
use Support\Http\Resources\Schemas\Reflection\Property;

/**
 * @mixin \Illuminate\Http\Resources\Json\JsonResource
 */
trait AsSchema
{
    use ConditionallyLoadsAttributes;

    /** @var Collection<array-key, Property> */
    protected Collection $fields {
        get => $this->fields ??= collect(
            new ReflectionObject($this)->getProperties()
        )->mapInto(Property::class)->filter->isPublic->filter->isOn(static::class)->keyBy->name; // @phpstan-ignore property.nonObject
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->fields->mapWithKeys(
            fn (Property $property) => [$property->name->snake()->toString() => $this->{$property->name}]
        )->toArray();
    }

    protected static function newCollection($resource)
    {
        $collectedBy = data_get(new ReflectionClass(static::class)->getAttributes(CollectedBy::class), 0)?->newInstance()->schemaCollection;
        throw_unless( // @phpstan-ignore-line
            $collectedBy,
            CollectedByNotDefined::class,
            static::class
        );

        return new $collectedBy($resource);
    }
}
