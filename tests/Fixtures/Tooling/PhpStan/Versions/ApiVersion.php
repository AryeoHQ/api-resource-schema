<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versions;

use Support\Http\Resources\Schemas\Contracts\Version;

enum ApiVersion: string implements Version
{
    case V1 = 'v1';
    case V2 = 'v2';
}
