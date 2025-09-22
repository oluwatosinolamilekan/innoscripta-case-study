<?php

namespace App\Services\Contracts;

use Exception;
use Carbon\Carbon;
use App\Models\Source;
use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

abstract class BaseNewsSource implements NewsSourceInterface
{
    /**
     * The HTTP client instance.
     *
     * @var \Illuminate\Http\Client\Factory
     */
    protected $http;

    /**
     * The base URL for the API.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * The API key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Create a new news source instance.
     *
     * @param string $apiKey
     * @return void
     */
    public function __construct(?string $apiKey = null)
    {
        $this->http = Http::timeout(30);
        $this->apiKey = $apiKey;
    }

    /**
     * Save articles to the database.
     *
     * @param array $articles
     * @param Source $source
     * @return void
     */
    protected function saveArticles(array $articles, Source $source): void
    {
        DB::transaction(function () use ($articles, $source) {
            foreach ($articles as $articleData) {
                try {
                    // Skip if article already exists (based on URL or external ID)
                    $exists = Article::where('url', $articleData['url'])
                        ->orWhere('external_id', $articleData['external_id'] ?? null)
                        ->exists();
                    
                    if (!$exists) {
                        Article::create([
                            'source_id' => $source->id,
                            'title' => $articleData['title'],
                            'description' => $articleData['description'] ?? null,
                            'content' => $articleData['content'] ?? null,
                            'author' => $articleData['author'] ?? null,
                            'url' => $articleData['url'],
                            'url_to_image' => $articleData['url_to_image'] ?? null,
                            'category' => $articleData['category'] ?? null,
                            'published_at' => $articleData['published_at'],
                            'external_id' => $articleData['external_id'] ?? null,
                            'raw_data' => $articleData['raw_data'] ?? null,
                        ]);
                    }
                } catch (Exception $e) {
                    Log::error('Error saving article: ' . $e->getMessage(), [
                        'source' => $source->name,
                        'article' => $articleData['title'] ?? 'Unknown',
                        'exception' => $e,
                    ]);
                    throw $e; // Re-throw to trigger transaction rollback
                }
            }
        });
    }

    /**
     * Save sources to the database.
     *
     * @param array $sources
     * @param string $apiName
     * @return void
     */
    protected function saveSources(array $sources, string $apiName): void
    {
        DB::transaction(function () use ($sources, $apiName) {
            foreach ($sources as $sourceData) {
                try {
                    Source::updateOrCreate(
                        [
                            'slug' => Str::slug($sourceData['name']),
                            'api_name' => $apiName,
                        ],
                        [
                            'name' => $sourceData['name'],
                            'api_id' => $sourceData['id'] ?? null,
                            'description' => $sourceData['description'] ?? null,
                            'url' => $sourceData['url'] ?? null,
                            'category' => $sourceData['category'] ?? null,
                            'language' => $sourceData['language'] ?? null,
                            'country' => $sourceData['country'] ?? null,
                        ]
                    );
                } catch (Exception $e) {
                    Log::error('Error saving source: ' . $e->getMessage(), [
                        'source' => $sourceData['name'] ?? 'Unknown',
                        'exception' => $e,
                    ]);
                    throw $e; // Re-throw to trigger transaction rollback
                }
            }
        });
    }

    /**
     * Format the date to a consistent format.
     *
     * @param string $date
     * @return string
     */
    protected function formatDate(string $date): string
    {
        try {
            return Carbon::parse($date)->toDateTimeString();
        } catch (Exception $e) {
            Log::warning('Error parsing date: ' . $e->getMessage(), [
                'date' => $date,
                'exception' => $e,
            ]);
            return now()->toDateTimeString();
        }
    }

    /**
     * Get or create a source for this news service.
     *
     * @param string|null $slug Custom slug (optional)
     * @param string|null $url Source URL (optional)
     * @return \App\Models\Source
     */
    protected function getOrCreateSource(?string $slug = null, ?string $url = null): Source
    {
        $sourceName = $this->getSourceName();
        $sourceSlug = $slug ?? Str::slug($sourceName);
        
        return Source::firstOrCreate([
            'slug' => $sourceSlug,
            'api_name' => $sourceName,
        ], [
            'name' => $sourceName,
            'url' => $url,
        ]);
    }

    /**
     * Log API response data to a JSON file with source metadata.
     *
     * @param string $endpoint The API endpoint or identifier
     * @param array $data The data to log
     * @param array $additionalMetadata Additional metadata to include in the log
     * @return bool Whether the log was successful
     */
    protected function logResponseToJson(string $endpoint, array $data, array $additionalMetadata = []): bool
    {
        // API response logging has been disabled
        return true;
        
        /* Original implementation commented out
        try {
            $sourceName = $this->getSourceName();
            $timestamp = now()->format('Y-m-d_H-i-s');
            $directory = storage_path('logs/api_responses');
            
            // Create directory if it doesn't exist
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            $filename = "{$directory}/{$sourceName}_{$endpoint}_{$timestamp}.json";
            
            // Prepare metadata
            $metadata = [
                'source' => [
                    'name' => $sourceName,
                    'base_url' => $this->baseUrl,
                    'endpoint' => $endpoint,
                ],
                'timestamp' => now()->toIso8601String(),
                'request_time' => now()->format('Y-m-d H:i:s'),
            ];
            
            // Add any additional metadata
            if (!empty($additionalMetadata)) {
                $metadata = array_merge($metadata, $additionalMetadata);
            }
            
            // Combine metadata with the response data
            $logData = [
                'metadata' => $metadata,
                'response_data' => $data,
            ];
            
            // Write data to file with pretty print
            file_put_contents(
                $filename, 
                json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
            
            $relativePath = str_replace(base_path() . '/', '', $filename);
            Log::info("API response logged to file: {$filename}");
            
            // Output to console if running in CLI
            if (app()->runningInConsole()) {
                $this->outputToConsole("API response logged to: {$relativePath}");
            }
            
            return true;
        } catch (Exception $e) {
            Log::error("Failed to log API response: {$e->getMessage()}", [
                'exception' => $e,
                'source' => $this->getSourceName(),
                'endpoint' => $endpoint
            ]);
            return false;
        }
        */
    }

    /**
     * Output a message to the console if running in CLI mode.
     *
     * @param string $message The message to output
     * @return void
     */
    protected function outputToConsole(string $message): void
    {
        if (app()->runningInConsole()) {
            // Fallback to plain echo since we can't reliably access the command instance
            echo PHP_EOL . $message . PHP_EOL;
        }
    }
}
