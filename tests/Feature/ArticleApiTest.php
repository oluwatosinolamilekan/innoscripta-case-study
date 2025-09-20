<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting articles.
     */
    public function test_get_articles(): void
    {
        // Create a source
        $source = Source::factory()->create();

        // Create some articles
        Article::factory()->count(5)->create([
            'source_id' => $source->id,
        ]);

        // Make the request
        $response = $this->get('/api/articles');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
            ->assertJsonCount(5, 'data');
    }

    /**
     * Test searching articles.
     */
    public function test_search_articles(): void
    {
        // Create a source
        $source = Source::factory()->create();

        // Create some articles
        Article::factory()->create([
            'source_id' => $source->id,
            'title' => 'Test Article with Laravel',
        ]);

        Article::factory()->create([
            'source_id' => $source->id,
            'title' => 'Another Article',
        ]);

        // Make the search request
        $response = $this->get('/api/articles/search?query=Laravel');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta',
            ])
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test filtering articles by category.
     */
    public function test_filter_articles_by_category(): void
    {
        // Create a source
        $source = Source::factory()->create();

        // Create some articles with different categories
        Article::factory()->create([
            'source_id' => $source->id,
            'category' => 'technology',
        ]);

        Article::factory()->create([
            'source_id' => $source->id,
            'category' => 'sports',
        ]);

        // Make the request with category filter
        $response = $this->get('/api/articles?category=technology');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test getting article categories.
     */
    public function test_get_article_categories(): void
    {
        // Create a source
        $source = Source::factory()->create();

        // Create some articles with different categories
        Article::factory()->create([
            'source_id' => $source->id,
            'category' => 'technology',
        ]);

        Article::factory()->create([
            'source_id' => $source->id,
            'category' => 'sports',
        ]);

        // Make the request
        $response = $this->get('/api/articles/categories');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['technology'])
            ->assertJsonFragment(['sports']);
    }
}
