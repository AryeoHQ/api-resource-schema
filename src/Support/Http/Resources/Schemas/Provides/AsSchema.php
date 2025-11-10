<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Provides;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use ReflectionObject;
use Support\Http\Resources\Schemas\Concerns\ConditionallyLoadsAttributes;
use Support\Http\Resources\Schemas\Reflection\Property;

trait AsSchema
{
    use ConditionallyLoadsAttributes;

    /** @var Collection<array-key, Property> */
    protected Collection $fields {
        get => $this->fields ??= collect(
            new ReflectionObject($this)->getProperties()
        )->mapInto(Property::class)->filter->isPublic->filter->isOn(static::class)->keyBy->name;
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
}
