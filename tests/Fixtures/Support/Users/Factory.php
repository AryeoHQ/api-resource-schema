<?php

declare(strict_types=1);

namespace Tests\Fixtures\Support\Users;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<User>
 */
class Factory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'username' => fake()->userName(),
        ];
    }

    public function biography(): static
    {
        return $this->state(
            fn () => ['biography' => fake()->paragraph()]
        );
    }

    public function withoutMiddleName(): static
    {
        return $this->state(
            fn () => ['middle_name' => null]
        );
    }

    public function birthday(): static
    {
        return $this->state(
            fn () => ['date_of_birth' => fake()->date()]
        );
    }

    public function deleted(): static
    {
        return $this->state(
            fn () => ['deleted_at' => now()]
        );
    }
}
