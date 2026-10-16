<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Tags;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\ContributesTag\ContributesTag;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Tags\Contributors\Posts;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[ContributesTag(Posts::class, relation: 'posts')]
#[Version(ApiVersion::V1)]
class MissingContribution extends JsonResource implements Schema
{
    use AsSchema;

    public mixed $posts { get => $this->whenLoaded('posts'); }

    public mixed $comments { get => $this->whenLoaded('comments'); }

    public mixed $teamMembershipId { get => $this->whenPivotLoaded('team_user', fn () => $this->resource->pivot->id); }

    public mixed $rating { get => $this->whenAggregated(strtolower('Posts'), 'rating', 'avg'); }
}
