<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users\Versions\V1;

use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Fields\Attributes\Represents\Represents;
use Support\Http\Resources\Schemas\Fields\Contracts\VersionedEnum;
use Support\Http\Resources\Schemas\Fields\Provides\AsVersionedEnum;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users;

#[Version(ApiVersion::V1)]
enum Status: string implements VersionedEnum
{
    use AsVersionedEnum;

    #[Represents(Users\Status::Draft, Users\Status::Open)]
    #[Represents(Users\Status::Finalized)]
    case Pending = 'pending';

    #[Represents(Users\Status::Closed)]
    case Paid = 'paid';

    #[Represents(Users\Status::Void)]
    case Cancelled = 'cancelled';
}
