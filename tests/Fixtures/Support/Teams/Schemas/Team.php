<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Teams\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Schemas\ApiVersion;

#[CollectedBy(Teams::class)]
#[Version(ApiVersion::V2)]
class Team extends JsonResource implements Schemas\Contracts\Schema
{
    /** @use AsSchema<ApiVersion> */
    use AsSchema;

    public string $name { get => $this->resource->name; }

    /** @var array<string, string> */
    public array $merged { get => $this->mergeWhen(true, ['key' => 'value']); }
}
