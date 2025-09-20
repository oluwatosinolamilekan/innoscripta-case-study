<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();
        
        return [
            'source_id' => \App\Models\Source::factory(),
            'title' => $title,
            'description' => $this->faker->paragraph(),
            'content' => $this->faker->paragraphs(3, true),
            'author' => $this->faker->name(),
            'url' => $this->faker->url(),
            'url_to_image' => $this->faker->imageUrl(),
            'category' => $this->faker->randomElement(['general', 'business', 'technology', 'sports', 'entertainment', 'health', 'science']),
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'external_id' => $this->faker->unique()->uuid(),
            'raw_data' => [
                'title' => $title,
                'description' => $this->faker->paragraph(),
                'content' => $this->faker->paragraphs(3, true),
                'author' => $this->faker->name(),
                'url' => $this->faker->url(),
                'urlToImage' => $this->faker->imageUrl(),
                'publishedAt' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
            ],
        ];
    }
}
