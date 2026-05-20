<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Concerns;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Support\Http\Resources\Schemas\Attributes;
use Support\Http\Resources\Schemas\Contracts;

/**
 * @mixin Contracts\SchemableCollection
 */
trait TransformsToSchemaCollection
{
    /** @var Collection<int, class-string<Contracts\SchemaCollection&ResourceCollection>> */
    public Collection $schemas {
        get => $this->schemas ??= Attributes\UseSchemaCollection\UseSchemaCollection::resolve(static::class);
    }

    /** @var Collection<int, Contracts\Version> */
    public Collection $schemaVersions {
        get {
            return $this->schemas->map(
                fn (string $schemaCollection): Contracts\Version => Attributes\Version\Version::resolve(
                    Attributes\Collects\Collects::resolve($schemaCollection)
                )
            );
        }
    }

    public function toSchemaCollection(Contracts\Version $version): Contracts\SchemaCollection&ResourceCollection
    {
        $schemaCollectionClass = $this->schemas->first(
            fn (string $schemaCollection): bool => Attributes\Version\Version::resolve(
                Attributes\Collects\Collects::resolve($schemaCollection)
            ) === $version
        );

        throw_unless(
            $schemaCollectionClass,
            Attributes\Version\Exceptions\NotFound::class,
            $version,
            static::class
        );

        // When called via a paginator's ForwardsCalls::forwardCallTo(), the paginator
        // is the caller two frames up. Passing it through preserves pagination metadata
        // in the ResourceCollection response (links, meta, per_page, etc.).
        $caller = data_get(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3), '2.object');

        $source = match ($caller instanceof AbstractPaginator || $caller instanceof AbstractCursorPaginator) {
            true => $caller,
            false => $this,
        };

        return new $schemaCollectionClass($source);
    }
}
