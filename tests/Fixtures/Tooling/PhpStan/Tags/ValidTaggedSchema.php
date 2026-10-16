<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Tags;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\ContributesTag\ContributesTag;
use Support\Http\Resources\Schemas\Attributes\Hidden\Hidden;
use Support\Http\Resources\Schemas\Attributes\Untagged\Untagged;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Tags\Contributors\Posts;
use Tests\Fixtures\Support\Tags\Contributors\Teams;
use Tests\Fixtures\Tooling\PhpStan\Versions\ApiVersion;

#[ContributesTag(Posts::class, relation: 'posts')]
#[ContributesTag(Teams::class, 'team_user')]
#[Version(ApiVersion::V1)]
class ValidTaggedSchema extends JsonResource implements Schema
{
    use AsSchema;

    public string|Discarded $email { get => $this->when(request()->boolean('with_email'), fn () => $this->resource->email); }

    public mixed $posts { get => $this->whenLoaded('posts'); }

    public mixed $postsCount { get => $this->whenCounted('posts'); }

    public mixed $membership { get => $this->whenPivotLoadedAs('membership', 'team_user', fn () => $this->resource->membership->id); }

    #[Untagged]
    public bool $canEdit { get => auth()->user()?->can('update', $this->resource) ?? false; }

    #[Hidden]
    public mixed $viewer { get => auth()->id(); }

    protected mixed $comments { get => $this->whenLoaded('comments'); }
}
