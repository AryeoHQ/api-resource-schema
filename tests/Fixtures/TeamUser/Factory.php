<?php

declare(strict_types=1);

namespace Tests\Fixtures\TeamUser;

use Tests\Fixtures\Teams\Team;
use Tests\Fixtures\Users\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TeamUser>
 */
class Factory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = TeamUser::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => User::factory(),
            'team_id' => fn () => Team::factory(),
        ];
    }
}
