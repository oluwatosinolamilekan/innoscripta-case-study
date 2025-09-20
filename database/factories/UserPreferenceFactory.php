<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'preferred_sources' => $this->faker->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(1, 3)),
            'preferred_categories' => $this->faker->randomElements(['general', 'business', 'technology', 'sports', 'entertainment', 'health', 'science'], $this->faker->numberBetween(1, 3)),
            'preferred_authors' => $this->faker->randomElements([$this->faker->name(), $this->faker->name(), $this->faker->name(), $this->faker->name()], $this->faker->numberBetween(1, 3)),
        ];
    }
}
