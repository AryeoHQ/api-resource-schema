<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users;

use Support\Http\Resources\Schemas\Fields\Attributes\RepresentedBy\RepresentedBy;
use Support\Http\Resources\Schemas\Fields\Concerns\TransformsToVersion;
use Support\Http\Resources\Schemas\Fields\Contracts\Versionable;

#[RepresentedBy(Versions\V1\Status::class)]
#[RepresentedBy(Versions\V2\Status::class)]
enum Status: string implements Versionable
{
    use TransformsToVersion;

    case Draft = 'draft';

    case Open = 'open';

    case Finalized = 'finalized';

    case Closed = 'closed';

    case Void = 'void';
}
