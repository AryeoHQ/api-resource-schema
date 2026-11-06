<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users\Schemas\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users\Collection\Schemas\V1\Users;
use Tests\Fixtures\Support\Users\Status;

#[CollectedBy(Users::class)]
#[Version(ApiVersion::V1)]
class User extends JsonResource implements Schema
{
    /** @use AsSchema<ApiVersion> */
    use AsSchema;

    public string $firstName { get => $this->resource->first_name; }

    public Status|Discarded $status { get => $this->whenNotNull($this->resource->status); }
}
