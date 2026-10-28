<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Schemas;

use Support\Http\Resources\Schemas\Contracts\Version;

enum LineFeedApiVersion: string implements Version
{
    case V2 = "v2\n";
}
