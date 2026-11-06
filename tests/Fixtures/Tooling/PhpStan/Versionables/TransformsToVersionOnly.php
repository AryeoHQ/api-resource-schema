<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion;

class TransformsToVersionOnly
{
    use TransformsToVersion;
}
