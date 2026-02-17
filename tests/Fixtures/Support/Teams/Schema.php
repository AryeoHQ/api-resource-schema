<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Teams;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas;
use Support\Http\Resources\Schemas\Provides\AsSchema;

class Schema extends JsonResource implements Schemas\Contracts\Schema
{
    use AsSchema;

    public string $name { get => $this->resource->name; }

    /** @var array<string, string> */
    public array $mergeWhen { get => $this->mergeWhen(true, ['key' => 'value']); }
}
