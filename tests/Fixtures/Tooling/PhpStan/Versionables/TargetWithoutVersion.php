<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;

enum TargetWithoutVersion: string implements VersionedEnum
{
    use AsVersionedEnum;

    case A = 'a';
}
