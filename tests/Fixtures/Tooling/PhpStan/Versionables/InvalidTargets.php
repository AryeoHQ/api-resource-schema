<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;

#[RepresentedBy(PlainEnum::class)]
class InvalidTargets implements Versionable
{
    use TransformsToVersion;
}
