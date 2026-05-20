<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[Version(ApiVersion::V2)]
class ValidSchemaV2 extends JsonResource implements Schema
{
    use AsSchema;

    public string $name { get => $this->resource->name; }
}
