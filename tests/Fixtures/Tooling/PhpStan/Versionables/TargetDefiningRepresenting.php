<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[Version(ApiVersion::V1)]
enum TargetDefiningRepresenting: string implements VersionedEnum
{
    case A = 'a';

    public static function representing(Versionable $value): static
    {
        return self::A;
    }
}
