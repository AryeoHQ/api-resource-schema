<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Posts\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Users;

#[CollectedBy(Posts::class)]
#[Version(ApiVersion::V2)]
class Post extends JsonResource implements Schema
{
    /** @use AsSchema<ApiVersion> */
    use AsSchema;

    public string $title { get => $this->resource->title; }

    public Users\Schemas\V2\User|Discarded $user {
        get => $this->whenLoaded('user', fn () => Users\Schemas\V2\User::make($this->resource->user));
    }
}
