<?php

namespace App\Services\NewsServices;

use App\Models\Source;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\Contracts\BaseNewsSource;

class NewsApiService extends BaseNewsSource
{
    /**
     * The base URL for NewsAPI.
     *
     * @var string
     */
    private const BASE_URL = 'https://newsapi.org/v2';

    /**
     * Create a new NewsAPI service instance.
     *
     * @param string|null $apiKey
     * @return void
     */
    public function __construct(?string $apiKey = null)
    {
        parent::__construct($apiKey ?? config('services.news_api.key'));
        $this->baseUrl = self::BASE_URL;
    }

    /**
     * Get the name of the news source.
     *
     * @return string
     */
    public function getSourceName(): string
    {
        return 'NewsAPI';
    }
    
    /**
     * Get or create a source for a specific publication from NewsAPI.
     *
     * @param array $sourceData Source data from the API
     * @return \App\Models\Source
     */
    protected function getOrCreateArticleSource(array $sourceData): Source
    {
        $sourceName = $sourceData['name'] ?? 'Unknown';
        
        return Source::firstOrCreate([
            'slug' => Str::slug($sourceName),
            'api_name' => $this->getSourceName(),
        ], [
            'name' => $sourceName,
            'api_id' => $sourceData['id'] ?? null,
        ]);
    }

    /**
     * Get articles from NewsAPI.
     *
     * @param array $params
     * @return array
     */
    public function getArticles(array $params = []): array
    {
        try {
            // everything
            $response = $this->http->withHeaders([
                'X-Api-Key' => $this->apiKey,
            ])->get($this->baseUrl . '/top-headlines', array_merge([
                'country' => 'us',
                'pageSize' => 100,
            ], $params));

            if ($response->failed()) {
                Log::error('NewsAPI request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            
            // API response logging has been disabled
            // $this->logResponseToJson('articles', $data, [
            //     'request' => [
            //         'params' => $params,
            //         'endpoint' => '/top-headlines',
            //         'method' => 'GET',
            //         'headers' => ['X-Api-Key' => '***API_KEY_HIDDEN***']
            //     ]
            // ]);
            $articles = [];

            // Get or create the source
            $source = $this->getOrCreateSource('newsapi');

            foreach ($data['articles'] as $article) {
                // Create a source for this specific publication if it doesn't exist
                $articleSource = $this->getOrCreateArticleSource($article['source']);

                $articles[] = [
                    'title' => $article['title'],
                    'description' => $article['description'] ?? null,
                    'content' => $article['content'] ?? null,
                    'author' => $article['author'] ?? null,
                    'url' => $article['url'],
                    'url_to_image' => $article['urlToImage'] ?? null,
                    'published_at' => $this->formatDate($article['publishedAt']),
                    'external_id' => md5($article['url']),
                    'raw_data' => $article,
                ];

                // Save the article
                $this->saveArticles([$articles[count($articles) - 1]], $articleSource);
            }

            return $articles;
        } catch (\Exception $e) {
            Log::error('Error fetching articles from NewsAPI: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return [];
        }
    }



    /**
     * Get sources from NewsAPI.
     *
     * @param array $params
     * @return array
     */
    public function getSources(array $params = []): array
    {
        try {
            $response = $this->http->withHeaders([
                'X-Api-Key' => $this->apiKey,
            ])->get($this->baseUrl . '/sources', $params);

            if ($response->failed()) {
                Log::error('NewsAPI sources request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            
            // API response logging has been disabled
            // $this->logResponseToJson('articles', $data, [
            //     'request' => [
            //         'params' => $params,
            //         'endpoint' => '/top-headlines',
            //         'method' => 'GET',
            //         'headers' => ['X-Api-Key' => '***API_KEY_HIDDEN***']
            //     ]
            // ]);
            $sources = [];

            foreach ($data['sources'] as $source) {
                $sources[] = [
                    'id' => $source['id'],
                    'name' => $source['name'],
                    'description' => $source['description'] ?? null,
                    'url' => $source['url'] ?? null,
                    'category' => $source['category'] ?? null,
                    'language' => $source['language'] ?? null,
                    'country' => $source['country'] ?? null,
                ];
            }

            // Save sources to database
            $this->saveSources($sources, $this->getSourceName());

            return $sources;
        } catch (\Exception $e) {
            Log::error('Error fetching sources from NewsAPI: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return [];
        }
    }

    /**
     * Search for articles from NewsAPI.
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function searchArticles(string $query, array $params = []): array
    {
        try {
            $response = $this->http->withHeaders([
                'X-Api-Key' => $this->apiKey,
            ])->get($this->baseUrl . '/everything', array_merge([
                'q' => $query,
                'pageSize' => 100,
                'sortBy' => 'publishedAt',
            ], $params));

            if ($response->failed()) {
                Log::error('NewsAPI search request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            
            // API response logging has been disabled
            // $this->logResponseToJson('articles', $data, [
            //     'request' => [
            //         'params' => $params,
            //         'endpoint' => '/top-headlines',
            //         'method' => 'GET',
            //         'headers' => ['X-Api-Key' => '***API_KEY_HIDDEN***']
            //     ]
            // ]);
            $articles = [];

            // Get or create the source
            $source = $this->getOrCreateSource('newsapi');

            foreach ($data['articles'] as $article) {
                // Create a source for this specific publication if it doesn't exist
                $articleSource = $this->getOrCreateArticleSource($article['source']);

                $articles[] = [
                    'title' => $article['title'],
                    'description' => $article['description'] ?? null,
                    'content' => $article['content'] ?? null,
                    'author' => $article['author'] ?? null,
                    'url' => $article['url'],
                    'url_to_image' => $article['urlToImage'] ?? null,
                    'published_at' => $this->formatDate($article['publishedAt']),
                    'external_id' => md5($article['url']),
                    'raw_data' => $article,
                ];

                // Save the article
                $this->saveArticles([$articles[count($articles) - 1]], $articleSource);
            }

            return $articles;
        } catch (\Exception $e) {
            Log::error('Error searching articles from NewsAPI: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return [];
        }
    }
}
