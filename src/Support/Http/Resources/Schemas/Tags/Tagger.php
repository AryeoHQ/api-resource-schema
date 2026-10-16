<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Tags;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use stdClass;
use Support\Http\Resources\Schemas\Attributes\ContributesTag\ContributesTag;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Contracts\SchemaCollection;

final class Tagger
{
    private const int FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;

    public function tag(Schema&JsonResource $schema): Tag
    {
        $request = Request::create('/');
        $request->attributes->set(self::class, true);

        return Tag::hash(json_encode($this->canonical([
            $this->schema($schema, $request),
            $this->resolve($schema->with($request), $request),
            $this->resolve($schema->additional, $request),
        ]), self::FLAGS));
    }

    public static function tagging(Request $request): bool
    {
        return $request->attributes->get(self::class) === true;
    }

    /**
     * @return array<int, mixed>
     */
    private function schema(Schema&JsonResource $schema, Request $request): array
    {
        return [
            $schema::class,
            $schema->schemaVersion->value,
            $this->resolve($schema->resolve($request), $request),
            $this->contributions($schema),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function contributions(Schema&JsonResource $schema): array
    {
        return ContributesTag::resolve($schema::class)
            ->map(fn (ContributesTag $attribute): string => app($attribute->contributor)->contribute($schema->resource))
            ->all();
    }

    private function resolve(mixed $value, Request $request): mixed
    {
        return match (true) {
            $value instanceof Schema && $value instanceof JsonResource => $this->schema($value, $request),
            $value instanceof SchemaCollection && $value instanceof ResourceCollection => $this->resolve($value->collection, $request),
            $value instanceof JsonResource => $this->resolve($value->resolve($request), $request),
            $value instanceof Collection => $this->resolve($value->all(), $request),
            is_array($value) => array_map(fn (mixed $item): mixed => $this->resolve($item, $request), $value),
            default => $value,
        };
    }

    private function canonical(mixed $value): mixed
    {
        return $this->sort(json_decode(json_encode($value, self::FLAGS), false, 512, JSON_THROW_ON_ERROR));
    }

    private function sort(mixed $value): mixed
    {
        return match (true) {
            $value instanceof stdClass => (object) collect(get_object_vars($value))->sortKeys(SORT_STRING)->map($this->sort(...))->all(),
            is_array($value) => array_map($this->sort(...), $value),
            default => $value,
        };
    }
}
