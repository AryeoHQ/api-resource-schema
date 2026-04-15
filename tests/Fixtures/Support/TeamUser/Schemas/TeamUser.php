<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\TeamUser\Schemas;

use Illuminate\Http\Resources\Json\JsonResource;
use Support\Http\Resources\Schemas;
use Support\Http\Resources\Schemas\Attributes\CollectedBy;
use Support\Http\Resources\Schemas\Provides\AsSchema;

#[CollectedBy(TeamUsers::class)]
class TeamUser extends JsonResource implements Schemas\Contracts\Schema
{
    use AsSchema;

    /** @var array<string, string> */
    public array $mergeUnless { get => $this->mergeUnless(false, ['key' => 'value']); }
}
