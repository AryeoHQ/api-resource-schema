<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Concerns;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Support\Http\Resources\Schemas\Attributes;
use Support\Http\Resources\Schemas\Contracts;

/**
 * @mixin Contracts\Schemable
 */
trait TransformsToSchema
{
    /** @var Collection<int, class-string<Contracts\Schema&JsonResource>> */
    public Collection $schemas {
        get => $this->schemas ??= Attributes\UseSchema\UseSchema::resolve(static::class);
    }

    /** @var Collection<int, Contracts\Version> */
    public Collection $schemaVersions {
        get => $this->schemaVersions ??= $this->schemas->map(
            fn (string $schema): Contracts\Version => Attributes\Version\Version::resolve($schema)
        );
    }

    public function toSchema(Contracts\Version $version): Contracts\Schema&JsonResource
    {
        $schema = $this->schemas->first(function (string $schema) use ($version): bool {
            return Attributes\Version\Version::resolve($schema) === $version;
        });

        throw_unless($schema, Attributes\Version\Exceptions\NotFound::class, $version, static::class);

        return $schema::make($this);
    }
}
