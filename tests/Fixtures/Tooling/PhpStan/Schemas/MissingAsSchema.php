<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Contracts\Schema;

class MissingAsSchema extends JsonResource implements Schema
{
    public string $name { get => $this->resource->name; }
}
