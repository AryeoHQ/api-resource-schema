<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;

#[RepresentedBy(ValidTarget::class)]
#[RepresentedBy(ValidTargetV2::class)]
enum ValidSource: string implements Versionable
{
    use TransformsToVersion;

    case Draft = 'draft';
    case Open = 'open';
}
