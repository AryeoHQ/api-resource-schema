<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Attributes\ContributesTag;

use Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use Support\Http\Resources\Schemas\Tags\Contracts\Contributor;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class ContributesTag
{
    /** @var class-string<Contributor<Model>> */
    public string $contributor;

    public null|string $relation;

    public function __construct(string $contributor, null|string $relation = null)
    {
        throw_unless(
            is_a($contributor, Contributor::class, true),
            InvalidArgumentException::class,
            "[{$contributor}] must implement [".Contributor::class.'].'
        );

        $this->contributor = $contributor;
        $this->relation = $relation;
    }

    /**
     * @param  class-string  $class
     * @return Collection<int, self>
     */
    public static function resolve(string $class): Collection
    {
        return collect((new ReflectionClass($class))->getAttributes(self::class))
            ->map(fn (ReflectionAttribute $attribute): self => $attribute->newInstance())
            ->values();
    }
}
