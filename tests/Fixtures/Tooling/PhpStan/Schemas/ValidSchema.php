<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Tooling\PhpStan\SchemableCollections\ValidSchemableCollectionWithParity;
use Tests\Fixtures\Tooling\PhpStan\Schemables\ValidSchemable;
use Tests\Fixtures\Tooling\PhpStan\Versionables\ValidSource;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[Version(ApiVersion::V1)]
class ValidSchema extends JsonResource implements Schema
{
    use AsSchema;

    public string $name { get => $this->resource->name; }

    public ValidSource $status { get => $this->resource->status; }

    public ValidSchemable $owner { get => $this->resource->owner; }

    public ValidSchemableCollectionWithParity $members { get => $this->resource->members; }
}
