<?php

declare(strict_types=1);

namespace Tests\Fixtures\Teams;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Team>
 */
class Factory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
        ];
    }
}
