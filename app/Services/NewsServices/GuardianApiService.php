<?php

namespace App\Services\NewsServices;

use App\Models\Source;
use App\Services\Contracts\BaseNewsSource;
use Illuminate\Support\Facades\Log;

class GuardianApiService extends BaseNewsSource
{
    /**
     * The base URL for Guardian API.
     *
     * @var string
     */
    private const BASE_URL = 'https://content.guardianapis.com';

    /**
     * Create a new Guardian API service instance.
     *
     * @param string|null $apiKey
     * @return void
     */
    public function __construct(?string $apiKey = null)
    {
        parent::__construct($apiKey ?? config('services.guardian.key'));
        $this->baseUrl = self::BASE_URL;
    }

    /**
     * Get the name of the news source.
     *
     * @return string
     */
    public function getSourceName(): string
    {
        return 'The Guardian';
    }

    /**
     * Get articles from The Guardian API.
     *
     * @param array $params
     * @return array
     */
    public function getArticles(array $params = []): array
    {
        try {
            $response = $this->http->get($this->baseUrl . '/search', array_merge([
                'api-key' => $this->apiKey,
                'page-size' => 50,
                'show-fields' => 'headline,trailText,body,thumbnail,byline,publication,lastModified,shortUrl',
                'order-by' => 'newest',
            ], $params));

            if ($response->failed()) {
                Log::error('Guardian API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            
            // Log the API response to a JSON file with request parameters
            $this->logResponseToJson('articles', $data, [
                'request' => [
                    'params' => $params,
                    'endpoint' => '/search',
                    'method' => 'GET'
                ]
            ]);
         
            $articles = [];

            // Get or create the source
            $source = $this->getOrCreateSource('the-guardian', 'https://www.theguardian.com');

            foreach ($data['response']['results'] as $article) {
                $articles[] = [
                    'title' => $article['fields']['headline'] ?? $article['webTitle'],
                    'description' => $article['fields']['trailText'] ?? null,
                    'content' => $article['fields']['body'] ?? null,
                    'author' => $article['fields']['byline'] ?? null,
                    'url' => $article['webUrl'],
                    'url_to_image' => $article['fields']['thumbnail'] ?? null,
                    'category' => $article['sectionName'] ?? null,
                    'published_at' => $this->formatDate($article['webPublicationDate']),
                    'external_id' => $article['id'],
                    'raw_data' => $article,
                ];
            }

            // Save articles to database
            $this->saveArticles($articles, $source);

            return $articles;
        } catch (\Exception $e) {
            Log::error('Error fetching articles from Guardian API: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return [];
        }
    }

    /**
     * Get sources from The Guardian API.
     * Note: The Guardian API doesn't have a specific endpoint for sources,
     * so we'll create a default source.
     *
     * @param array $params
     * @return array
     */
    public function getSources(array $params = []): array
    {
        $sources = [
            [
                'id' => 'the-guardian',
                'name' => 'The Guardian',
                'description' => 'The Guardian is a British daily newspaper.',
                'url' => 'https://www.theguardian.com',
                'category' => 'general',
                'language' => 'en',
                'country' => 'gb',
            ]
        ];

        // Save sources to database
        $this->saveSources($sources, $this->getSourceName());

        return $sources;
    }

    /**
     * Search for articles from The Guardian API.
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function searchArticles(string $query, array $params = []): array
    {
        try {
            $response = $this->http->get($this->baseUrl . '/search', array_merge([
                'api-key' => $this->apiKey,
                'q' => $query,
                'page-size' => 50,
                'show-fields' => 'headline,trailText,body,thumbnail,byline,publication,lastModified,shortUrl',
                'order-by' => 'relevance',
            ], $params));

            if ($response->failed()) {
                Log::error('Guardian API search request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            
            // Log the API response to a JSON file with request parameters
            $this->logResponseToJson('articles', $data, [
                'request' => [
                    'params' => $params,
                    'endpoint' => '/search',
                    'method' => 'GET'
                ]
            ]);
            $articles = [];

            // Get or create the source
            $source = $this->getOrCreateSource('the-guardian', 'https://www.theguardian.com');

            foreach ($data['response']['results'] as $article) {
                $articles[] = [
                    'title' => $article['fields']['headline'] ?? $article['webTitle'],
                    'description' => $article['fields']['trailText'] ?? null,
                    'content' => $article['fields']['body'] ?? null,
                    'author' => $article['fields']['byline'] ?? null,
                    'url' => $article['webUrl'],
                    'url_to_image' => $article['fields']['thumbnail'] ?? null,
                    'category' => $article['sectionName'] ?? null,
                    'published_at' => $this->formatDate($article['webPublicationDate']),
                    'external_id' => $article['id'],
                    'raw_data' => $article,
                ];
            }

            // Save articles to database
            $this->saveArticles($articles, $source);

            return $articles;
        } catch (\Exception $e) {
            Log::error('Error searching articles from Guardian API: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return [];
        }
    }
}
