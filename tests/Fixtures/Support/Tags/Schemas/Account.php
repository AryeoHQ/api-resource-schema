<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Tags\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Support\Http\Resources\Schemas\Attributes\CollectedBy\CollectedBy;
use Support\Http\Resources\Schemas\Attributes\Version\Version;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Support\Http\Resources\Schemas\Fields\Discarded;
use Support\Http\Resources\Schemas\Provides\AsSchema;
use Tests\Fixtures\Support\Schemas\ApiVersion;

#[CollectedBy(Accounts::class)]
#[Version(ApiVersion::V2)]
class Account extends JsonResource implements Schema
{
    /** @use AsSchema<ApiVersion> */
    use AsSchema;

    public string $firstName { get => $this->resource->first_name; }

    public string|Discarded $formerName { get => $this->unless(! $this->resource->trashed(), fn () => $this->resource->last_name); }

    public string|Discarded $email { get => $this->when(Gate::allows('view-email', $this->resource), fn () => $this->resource->email); }
}
