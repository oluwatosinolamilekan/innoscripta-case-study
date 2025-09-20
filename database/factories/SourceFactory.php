<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Source>
 */
class SourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        
        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'api_id' => $this->faker->unique()->word(),
            'api_name' => $this->faker->randomElement(['NewsAPI', 'The Guardian', 'New York Times']),
            'description' => $this->faker->sentence(),
            'url' => $this->faker->url(),
            'category' => $this->faker->randomElement(['general', 'business', 'technology', 'sports', 'entertainment', 'health', 'science']),
            'language' => $this->faker->randomElement(['en', 'fr', 'de', 'es']),
            'country' => $this->faker->countryCode(),
        ];
    }
}
