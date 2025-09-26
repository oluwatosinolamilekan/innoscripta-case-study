<?php

namespace App\Http\Controllers\Api;

use App\Models\Source;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SourceResource;
use App\Services\NewsAggregatorService;
use App\Http\Resources\ArticleCollection;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Controllers\Api\BaseApiController as Controller;

class SourceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected readonly NewsAggregatorService $aggregator
    ) {}

    /**
     * Get all sources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $sources = $this->aggregator->getSourcesFromDatabase();
        return $this->successResponse(SourceResource::collection($sources));
    }

    /**
     * Get articles from a specific source.
     *
     * @param int $id
     * @param ArticleFilterRequest $request
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function articles(int $id, ArticleFilterRequest $request)
    {
        // Validate that the source exists
        $this->validateSource($id);
        
        $filters = $request->validated();

        // Add source ID to filters
        $filters['source_id'] = $id;

        $articles = $this->aggregator->getArticlesFromDatabase($filters);

        return new ArticleCollection($articles);
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
