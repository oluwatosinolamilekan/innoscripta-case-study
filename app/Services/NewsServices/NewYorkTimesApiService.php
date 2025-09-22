<?php

namespace App\Services\NewsServices;

use App\Models\Source;
use App\Services\Contracts\BaseNewsSource;
use Illuminate\Support\Facades\Log;

class NewYorkTimesApiService extends BaseNewsSource
{
    /**
     * The base URL for New York Times API.
     *
     * @var string
     */
    private const BASE_URL = 'https://api.nytimes.com/svc';

    /**
     * The API secret.
     *
     * @var string
     */
    protected $apiSecret;

    /**
     * Create a new New York Times API service instance.
     *
     * @param string|null $apiKey
     * @param string|null $apiSecret
     * @return void
     */
    public function __construct(?string $apiKey = null, ?string $apiSecret = null)
    {
        parent::__construct($apiKey ?? config('services.nyt.key'));
        $this->apiSecret = $apiSecret ?? config('services.nyt.secret');
        $this->baseUrl = self::BASE_URL;
    }

    /**
     * Get the name of the news source.
     *
     * @return string
     */
    public function getSourceName(): string
    {
        return 'New York Times';
    }

    /**
     * Get articles from the New York Times API.
     *
     * @param array $params
     * @return array
     */
    public function getArticles(array $params = []): array
    {
        try {
            // NYT API has different endpoints for different types of content
            // We'll use the Top Stories API for this example
            $section = $params['section'] ?? 'home';
            unset($params['section']);

            $response = $this->http->get($this->baseUrl . '/topstories/v2/' . $section . '.json', array_merge([
                'api-key' => $this->apiKey,
                'api-secret' => $this->apiSecret,
            ], $params));

            if ($response->failed()) {
                Log::error('NYT API request failed', [
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
            //         'endpoint' => '/topstories/v2/' . ($params['section'] ?? 'home') . '.json',
            //         'method' => 'GET',
            //         'api_secret' => '***API_SECRET_HIDDEN***'
            //     ]
            // ]);
            $articles = [];

            // Get or create the source
            $source = $this->getOrCreateSource('new-york-times', 'https://www.nytimes.com');

            foreach ($data['results'] as $article) {
                $imageUrl = null;
                if (!empty($article['multimedia'])) {
                    foreach ($article['multimedia'] as $media) {
                        if ($media['format'] === 'mediumThreeByTwo440') {
                            $imageUrl = $media['url'];
                            break;
                        }
                    }
                }

                $articles[] = [
                    'title' => $article['title'],
                    'description' => $article['abstract'] ?? null,
                    'content' => $article['abstract'] ?? null, // NYT API doesn't provide full content
                    'author' => $article['byline'] ?? null,
                    'url' => $article['url'],
                    'url_to_image' => $imageUrl,
                    'category' => $article['section'] ?? null,
                    'published_at' => $this->formatDate($article['published_date']),
                    'external_id' => $article['uri'],
                    'raw_data' => $article,
                ];
            }

            // Save articles to database
            $this->saveArticles($articles, $source);

            return $articles;
        } catch (\Exception $e) {
            Log::error('Error fetching articles from NYT API: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return [];
        }
    }

    /**
     * Get sources from the New York Times API.
     * Note: NYT API doesn't have a specific endpoint for sources,
     * so we'll create a default source.
     *
     * @param array $params
     * @return array
     */
    public function getSources(array $params = []): array
    {
        $sources = [
            [
                'id' => 'new-york-times',
                'name' => 'New York Times',
                'description' => 'The New York Times is an American daily newspaper.',
                'url' => 'https://www.nytimes.com',
                'category' => 'general',
                'language' => 'en',
                'country' => 'us',
            ]
        ];

        // Save sources to database
        $this->saveSources($sources, $this->getSourceName());

        return $sources;
    }

    /**
     * Search for articles from the New York Times API.
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function searchArticles(string $query, array $params = []): array
    {
        try {
            $response = $this->http->get($this->baseUrl . '/search/v2/articlesearch.json', array_merge([
                'api-key' => $this->apiKey,
                'api-secret' => $this->apiSecret,
                'q' => $query,
                'sort' => 'newest',
            ], $params));

            if ($response->failed()) {
                Log::error('NYT API search request failed', [
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
            //         'endpoint' => '/topstories/v2/' . ($params['section'] ?? 'home') . '.json',
            //         'method' => 'GET',
            //         'api_secret' => '***API_SECRET_HIDDEN***'
            //     ]
            // ]);
            $articles = [];

            // Get or create the source
            $source = $this->getOrCreateSource('new-york-times', 'https://www.nytimes.com');

            foreach ($data['response']['docs'] as $article) {
                $imageUrl = null;
                if (!empty($article['multimedia'])) {
                    foreach ($article['multimedia'] as $media) {
                        if ($media['subtype'] === 'mediumThreeByTwo440') {
                            $imageUrl = 'https://www.nytimes.com/' . $media['url'];
                            break;
                        }
                    }
                }

                $articles[] = [
                    'title' => $article['headline']['main'],
                    'description' => $article['abstract'] ?? $article['snippet'] ?? null,
                    'content' => $article['lead_paragraph'] ?? null,
                    'author' => !empty($article['byline']['original']) ? $article['byline']['original'] : null,
                    'url' => $article['web_url'],
                    'url_to_image' => $imageUrl,
                    'category' => $article['section_name'] ?? null,
                    'published_at' => $this->formatDate($article['pub_date']),
                    'external_id' => $article['_id'],
                    'raw_data' => $article,
                ];
            }

            // Save articles to database
            $this->saveArticles($articles, $source);

            return $articles;
        } catch (\Exception $e) {
            Log::error('Error searching articles from NYT API: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return [];
        }
    }
}
