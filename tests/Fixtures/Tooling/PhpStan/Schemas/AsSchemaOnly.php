<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Provides\AsSchema;

class AsSchemaOnly extends JsonResource
{
    use AsSchema;

    public string $name { get => $this->resource->name; }
}
