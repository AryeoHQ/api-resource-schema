<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Tags\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\ContributesTag\ContributesTag;
use Support\Http\Resources\Schemas\Attributes\Untagged\Untagged;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\Fixtures\Support\Tags\Contributors\Biography;

#[CollectedBy(Members::class)]
#[ContributesTag(Biography::class, relation: 'biography')]
#[Version(ApiVersion::V2)]
class Member extends JsonResource implements Schema
{
    /** @use AsSchema<ApiVersion> */
    use AsSchema;

    public string $firstName { get => $this->resource->first_name; }

    public null|Discarded $middleInitial { get => $this->whenNull($this->resource->middle_initial); }

    public null|string|Discarded $biography { get => $this->whenHas('biography', fn () => $this->resource->biography); }

    #[Untagged]
    public null|string $viewer { get => request()->header('X-Viewer'); }
}
