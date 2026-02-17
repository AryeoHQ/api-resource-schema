<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Posts;

use Tests\Fixtures\Support\Users\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Post>
 */
class Factory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'rating' => fake()->numberBetween(1, 5),
            'user_id' => User::factory(),
        ];
    }

    public function rated(): static
    {
        return $this->state(
            fn () => [
                'rating' => fake()->numberBetween(1, 5),
            ]
        );
    }
}
