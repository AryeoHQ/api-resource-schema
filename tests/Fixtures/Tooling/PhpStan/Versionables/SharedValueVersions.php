<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;

#[RepresentedBy(ValidTargetV2::class)]
#[RepresentedBy(InternalTarget::class)]
class SharedValueVersions implements Versionable
{
    use TransformsToVersion;
}
