<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Tags\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Untagged\Untagged;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Schemas\ApiVersion;

#[CollectedBy(Members::class)]
#[Version(ApiVersion::V2)]
class Member extends JsonResource implements Schema
{
    /** @use AsSchema<ApiVersion> */
    use AsSchema;

    public string $firstName { get => $this->resource->first_name; }

    #[Untagged]
    public null|string $viewer { get => request()->header('X-Viewer'); }
}
