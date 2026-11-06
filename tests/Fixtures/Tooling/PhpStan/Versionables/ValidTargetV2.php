<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[Version(ApiVersion::V2)]
enum ValidTargetV2: string implements VersionedEnum
{
    use AsVersionedEnum;

    #[Represents(ValidSource::Draft)]
    case Draft = 'draft';

    #[Represents(ValidSource::Open)]
    case Open = 'open';
}
