<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SourceApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting all sources.
     */
    public function test_get_sources(): void
    {
        // Create some sources
        Source::factory()->count(3)->create();

        // Make the request
        $response = $this->get('/api/sources');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test getting articles from a specific source.
     */
    public function test_get_source_articles(): void
    {
        // Create sources
        $source1 = Source::factory()->create();
        $source2 = Source::factory()->create();

        // Create articles for each source
        Article::factory()->count(3)->create([
            'source_id' => $source1->id,
        ]);
        
        Article::factory()->count(2)->create([
            'source_id' => $source2->id,
        ]);

        // Make the request for source 1 articles
        $response = $this->get('/api/sources/' . $source1->id . '/articles');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta',
            ])
            ->assertJsonCount(3, 'data');

        // Make the request for source 2 articles
        $response = $this->get('/api/sources/' . $source2->id . '/articles');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
