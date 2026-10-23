<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Tags\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Schemas\ApiVersion;

#[CollectedBy(Feeds::class)]
#[Version(ApiVersion::V2)]
class Feed extends JsonResource implements Schema
{
    /** @use AsSchema<ApiVersion> */
    use AsSchema;

    public string $firstName { get => $this->resource->first_name; }

    /** @var array<string, Collection<int, JsonResource>> */
    public array $timeline { get => ['posts' => $this->resource->posts->map(fn (Post $post) => $post->toSchema(ApiVersion::V2))->toBase()]; }
}
