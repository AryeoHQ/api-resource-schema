<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Versionables;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\Hidden\Hidden;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[Version(ApiVersion::V2)]
class SchemaWithUnsupportedVersionableField extends JsonResource implements Schema
{
    use AsSchema;

    public IncompleteSource|null $status { get => $this->resource->status; }

    #[Hidden]
    public IncompleteSource $internalStatus { get => $this->resource->status; }
}
