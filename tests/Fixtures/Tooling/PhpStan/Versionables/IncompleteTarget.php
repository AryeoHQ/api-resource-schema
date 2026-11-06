<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[Version(ApiVersion::V1)]
enum IncompleteTarget: string implements VersionedEnum
{
    use AsVersionedEnum;

    #[Represents(IncompleteSource::Draft)]
    case A = 'a';

    #[Represents(IncompleteSource::Draft)]
    case B = 'b';
}
