<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Fields\Concerns;

use Support\Http\Resources\Schemas\Attributes;
use Support\Http\Resources\Schemas\Contracts\Version;
use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Contracts;

/**
 * @mixin Contracts\Versionable
 */
trait TransformsToVersion
{
    public function toVersion(Version $version): Contracts\Versioned
    {
        $versioned = RepresentedBy::resolve(static::class)->first(function (string $versioned) use ($version): bool {
            return Attributes\Version\Version::resolve($versioned) === $version;
        });

        throw_unless($versioned, Attributes\Version\Exceptions\NotFound::class, $version, static::class);

        return $versioned::representing($this);
    }
}
