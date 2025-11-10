<?php

declare(strict_types=1);

namespace Tests\Fixtures\Posts;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Users;

class Schema extends JsonResource implements Schemas\Contracts\Schema
{
    use AsSchema;

    public string $title { get => $this->resource->title; }

    public Users\Schema|Discarded $user {
        get => $this->whenLoaded('user', fn () => Users\Schema::make($this->resource->user));
    }
}
