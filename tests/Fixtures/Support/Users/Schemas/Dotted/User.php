<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users\Schemas\Dotted;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Schemas\DottedApiVersion;
use Tests\Fixtures\Support\Users\Collection\Schemas\V2\Users;

#[CollectedBy(Users::class)]
#[Version(DottedApiVersion::V2Point1)]
class User extends JsonResource implements Schema
{
    /** @use AsSchema<DottedApiVersion> */
    use AsSchema;

    public string $firstName { get => $this->resource->first_name; }
}
