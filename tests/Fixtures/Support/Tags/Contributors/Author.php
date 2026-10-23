<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Tags\Contributors;

use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Tags\Contracts\Contributor;
use Tests\Fixtures\Support\Posts\Post;
use Tests\Fixtures\Support\Schemas\ApiVersion;

/**
 * @implements Contributor<Post>
 */
class Author implements Contributor
{
    public function contribute(Model $resource): string
    {
        return (string) $resource->user?->toTag(ApiVersion::V2);
    }
}
