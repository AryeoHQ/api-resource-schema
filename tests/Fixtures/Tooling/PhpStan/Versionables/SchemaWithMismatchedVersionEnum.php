<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Tooling\PhpStan\Versions\InternalVersion;

#[Version(InternalVersion::V1)]
class SchemaWithMismatchedVersionEnum extends JsonResource implements Schema
{
    use AsSchema;

    public ValidSource $status { get => $this->resource->status; }
}
