<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\Hidden\Hidden;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Tooling\PhpStan\SchemableCollections\WithMissingVersion;
use Tests\Fixtures\Tooling\PhpStan\Schemables\CollectedByWithAdditionalVersion;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[Version(ApiVersion::V1)]
class SchemaWithUnsupportedSchemableFields extends JsonResource implements Schema
{
    use AsSchema;

    public CollectedByWithAdditionalVersion $owner { get => $this->resource->owner; }

    public WithMissingVersion $members { get => $this->resource->members; }

    #[Hidden]
    public CollectedByWithAdditionalVersion $internalOwner { get => $this->resource->owner; }

    #[Hidden]
    public WithMissingVersion $internalMembers { get => $this->resource->members; }
}
