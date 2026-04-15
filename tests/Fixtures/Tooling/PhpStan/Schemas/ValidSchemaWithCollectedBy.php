<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\CollectedBy;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Tooling\PhpStan\SchemaCollections\ValidSchemaCollection;

#[CollectedBy(ValidSchemaCollection::class)]
class ValidSchemaWithCollectedBy extends JsonResource implements Schema
{
    use AsSchema;

    public string $name { get => $this->resource->name; }
}
