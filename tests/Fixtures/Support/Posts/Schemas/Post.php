<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Posts\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\CollectedBy;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Users;

#[CollectedBy(Posts::class)]
class Post extends JsonResource implements Schema
{
    use AsSchema;

    public string $title { get => $this->resource->title; }

    public Users\Schemas\User|Discarded $user {
        get => $this->whenLoaded('user', fn () => Users\Schemas\User::make($this->resource->user));
    }
}
