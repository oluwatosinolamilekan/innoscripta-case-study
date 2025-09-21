<?php

namespace App\Services;

use Exception;
use App\Models\Source;
use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Services\Factories\NewsSourcesFactory;
use App\Services\Contracts\NewsSourceInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsAggregatorService
{
    /**
     * The news sources.
     *
     * @var array<NewsSourceInterface>
     */
    protected $sources = [];

    /**
     * Create a new news aggregator service instance.
     *
     * @param array $sources
     * @return void
     */
    public function __construct(array $sources = [])
    {
        $this->sources = $sources;
    }
    
    /**
     * Create a new NewsAggregatorService with sources from the factory.
     *
     * @return static
     */
    public static function createWithDefaultSources(): self
    {
        $instance = new static();
        
        // Use the factory to create sources
        foreach (NewsSourcesFactory::createAll() as $source) {
            $instance->addSource($source);
        }
        
        return $instance;
    }

    /**
     * Add a news source to the aggregator.
     *
     * @param NewsSourceInterface $source
     * @return $this
     */
    public function addSource(NewsSourceInterface $source): self
    {
        $this->sources[] = $source;
        return $this;
    }

    /**
     * Get articles from all sources.
     *
     * @param array $params
     * @return array
     */
    public function getArticles(array $params = []): array
    {
        $allArticles = [];

        foreach ($this->sources as $source) {
            try {
                $articles = $source->getArticles($params);
                $allArticles = array_merge($allArticles, $articles);
            } catch (Exception $e) {
                Log::error('Error fetching articles from ' . $source->getSourceName() . ': ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }

        return $allArticles;
    }

    /**
     * Get sources from all news sources.
     *
     * @param array $params
     * @return array
     */
    public function getSources(array $params = []): array
    {
        $allSources = [];
        
        foreach ($this->sources as $source) {
            try {
                $sources = $source->getSources($params);
                $allSources = array_merge($allSources, $sources);
                
            } catch (Exception $e) {
                Log::error('Error fetching sources from ' . $source->getSourceName() . ': ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }

        return $allSources;
    }

    /**
     * Search for articles from all sources.
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function searchArticles(string $query, array $params = []): array
    {
        $allArticles = [];

        foreach ($this->sources as $source) {
            try {
                $articles = $source->searchArticles($query, $params);
                $allArticles = array_merge($allArticles, $articles);
            } catch (Exception $e) {
                Log::error('Error searching articles from ' . $source->getSourceName() . ': ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }

        return $allArticles;
    }

    /**
     * Get articles from the database.
     *
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getArticlesFromDatabase(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Article::with('source')
            ->orderBy('published_at', 'desc');
        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply pagination
        $perPage = $filters['per_page'] ?? 15;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all sources from the database.
     *
     * @return Collection
     */
    public function getSourcesFromDatabase(): Collection
    {
        return Source::all();
    }

    /**
     * Get all categories from the database.
     *
     * @return Collection
     */
    public function getCategoriesFromDatabase(): Collection
    {
        return Article::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');
    }

    /**
     * Get all authors from the database.
     *
     * @return Collection
     */
    public function getAuthorsFromDatabase(): Collection
    {
        return Article::select('author')
            ->whereNotNull('author')
            ->distinct()
            ->pluck('author');
    }

    /**
     * Apply filters to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    protected function applyFilters($query, array $filters): void
    {
        $filterMappings = [
            'search' => function($query, $value) {
                // Use LIKE for SQLite compatibility in tests
                if (config('database.default') === 'sqlite') {
                    $query->where(function($q) use ($value) {
                        $q->where('title', 'like', '%' . $value . '%')
                          ->orWhere('description', 'like', '%' . $value . '%');
                    });
                } else {
                    $query->whereFullText(['title', 'description'], $value);
                }
            },
            'source_id' => function($query, $value) {
                $query->where('source_id', $value);
            },
            'category' => function($query, $value) {
                $query->where('category', $value);
            },
            'author' => function($query, $value) {
                $query->where('author', 'like', '%' . $value . '%');
            },
            'date_from' => function($query, $value) {
                $query->whereDate('published_at', '>=', $value);
            },
            'date_to' => function($query, $value) {
                $query->whereDate('published_at', '<=', $value);
            },
        ];

        foreach ($filterMappings as $filter => $callback) {
            if (!empty($filters[$filter])) {
                $callback($query, $filters[$filter]);
            }
        }
    }
}
