<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as Controller;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\SourceResource;
use App\Models\Source;
use App\Services\NewsAggregatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    /**
     * The news aggregator service instance.
     *
     * @var NewsAggregatorService
     */
    protected $aggregator;

    /**
     * Create a new controller instance.
     *
     * @param NewsAggregatorService $aggregator
     * @return void
     */
    public function __construct(NewsAggregatorService $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /**
     * Get all sources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $sources = $this->aggregator->getSourcesFromDatabase();
            return $this->successResponse(SourceResource::collection($sources));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get articles from a specific source.
     *
     * @param int $id
     * @param ArticleFilterRequest $request
     * @return \Illuminate\Http\Resources\Json\JsonResource|\Illuminate\Http\JsonResponse
     */
    public function articles(int $id, ArticleFilterRequest $request)
    {
        try {
            // Validate that the source exists
            $this->validateSource($id);
            
            $filters = $request->validated();

            // Add source ID to filters
            $filters['source_id'] = $id;

            $articles = $this->aggregator->getArticlesFromDatabase($filters);

            return new ArticleCollection($articles);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Validate that a source exists.
     *
     * @param int $id
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function validateSource(int $id): void
    {
        $source = Source::findOrFail($id);
    }
}
