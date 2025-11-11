<?php

declare(strict_types=1);

namespace Tests\Fixtures\Users;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Support\Http\Resources\Schemas;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Posts\SchemaCollection;

class Schema extends JsonResource implements Schemas\Contracts\Schema
{
    use AsSchema;

    public string $firstName { get => $this->resource->first_name; }

    public null|Discarded $middleInitial { get => $this->whenNull($this->resource->middle_initial); }

    public string $lastName { get => $this->resource->last_name; }

    public string|Discarded $email { get => $this->when(request()->with_email, fn () => $this->resource->email); }

    public string|Discarded $username {
        get => $this->unless(! request()->has('with_username'), fn () => $this->resource->username);
    }

    public string|Discarded|null $biography { get => $this->whenHas('biography', fn () => $this->resource->biography); }

    public string|Discarded $fullName { get => $this->whenAppended('full_name', $this->resource->fullName); }

    public Carbon|Discarded $deletedAt { get => $this->whenNotNull($this->resource->deleted_at); }

    public ResourceCollection|Discarded $posts {
        get => $this->whenLoaded('posts', fn () => SchemaCollection::make($this->resource->posts));
    }

    public int|Discarded $postsCount { get => $this->whenCounted('posts', fn () => $this->resource->posts_count); }

    public float|Discarded $rating { get => $this->whenAggregated('posts', 'rating', 'avg'); }

    public bool|Discarded $hasPosts { get => $this->whenExistsLoaded('posts', fn () => $this->resource->posts_exists); }

    public int|Discarded $teamMembershipId { get => $this->whenPivotLoaded('team_user', fn () => $this->resource->pivot->id); }

    public int|Discarded $age { get => $this->transform($this->resource->date_of_birth, fn (Carbon $dob) => $dob->age); }
}
