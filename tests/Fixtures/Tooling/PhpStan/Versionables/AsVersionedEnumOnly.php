<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;

enum AsVersionedEnumOnly: string
{
    use AsVersionedEnum;

    case A = 'a';
}
