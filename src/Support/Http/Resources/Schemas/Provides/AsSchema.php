<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Provides;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use ReflectionObject;
use Support\Http\Resources\Schemas\Attributes;
use Support\Http\Resources\Schemas\Concerns\ConditionallyLoadsAttributes;
use Support\Http\Resources\Schemas\Contracts;
use Support\Http\Resources\Schemas\Reflection\Property;
use Support\Http\Resources\Schemas\Tags;

/**
 * @template TVersion of Contracts\Version
 *
 * @mixin \Illuminate\Http\Resources\Json\JsonResource
 */
trait AsSchema
{
    use ConditionallyLoadsAttributes;

    /** @var TVersion */
    #[Attributes\Hidden\Hidden]
    public Contracts\Version $schemaVersion;

    #[Attributes\Hidden\Hidden]
    public string $collectedBy;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->schemaVersion = $this->schemaVersion();
        $this->collectedBy = $this->collectedBy();
    }

    /** @return TVersion */
    protected function schemaVersion(): Contracts\Version
    {
        /** @var TVersion */
        return Attributes\Version\Version::resolve(static::class);
    }

    protected function collectedBy(): string
    {
        return Attributes\CollectedBy\CollectedBy::resolve(static::class);
    }

    /** @var Collection<array-key, Property> */
    protected Collection $fields {
        get => $this->fields ??= collect(
            new ReflectionObject($this)->getProperties()
        )->mapInto(Property::class)->filter->isPublic->reject->isStatic->reject->isHidden->filter->isOn(static::class)->keyBy->name; // @phpstan-ignore property.nonObject
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->tagging = Tags\Tagger::tagging($request);

        return $this->fields->reject(
            fn (Property $property): bool => $this->tagging && $property->getAttributes(Attributes\Untagged\Untagged::class) !== []
        )->mapWithKeys(
            fn (Property $property) => [$property->name->snake()->toString() => $this->{$property->name}]
        )->toArray();
    }

    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setEtag((string) app(Tags\Tagger::class)->tag($this));
    }

    protected static function newCollection($resource)
    {
        return new (Attributes\CollectedBy\CollectedBy::resolve(static::class))($resource);
    }
}
