<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Tags\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Posts\Schemas\Posts;
use Tests\Fixtures\Support\Schemas\ApiVersion;

#[CollectedBy(Writers::class)]
#[Version(ApiVersion::V2)]
class Writer extends JsonResource implements Schema
{
    /** @use AsSchema<ApiVersion> */
    use AsSchema;

    public string $firstName { get => $this->resource->first_name; }

    public ResourceCollection $articles { get => Posts::make($this->resource->posts); }
}
