<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users\Schemas\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users\Collection\Schemas\V1\Users;

#[CollectedBy(Users::class)]
#[Version(ApiVersion::V1)]
class User extends JsonResource implements Schema
{
    /** @use AsSchema<ApiVersion> */
    use AsSchema;

    public string $firstName { get => $this->resource->first_name; }
}
