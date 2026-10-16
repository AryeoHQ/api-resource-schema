<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Tags\Contributors;

use Illuminate\Database\Eloquent\Model;
use Support\Http\Resources\Schemas\Tags\Contracts\Contributor;
use Tests\Fixtures\Support\Users\User;

/**
 * @implements Contributor<User>
 */
class Teams implements Contributor
{
    public function contribute(Model $resource): string
    {
        return $resource->teams()->orderBy('teams.id')->pluck('teams.id')->join(',');
    }
}
