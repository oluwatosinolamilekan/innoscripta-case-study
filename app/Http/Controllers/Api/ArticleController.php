<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Http\Resources\ArticleResource;
use App\Services\NewsAggregatorService;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ArticleCollection;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Requests\ArticleSearchRequest;
use App\Http\Controllers\Api\BaseApiController as Controller;

class ArticleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected readonly NewsAggregatorService $aggregator
    ) {}

    /**
     * Get all articles with optional filtering.
     *
     * @param ArticleFilterRequest $request
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function index(ArticleFilterRequest $request)
    {
        $filters = $request->validated();
        $articles = $this->aggregator->getArticlesFromDatabase($filters);
        return new ArticleCollection($articles);
    }

    /**
     * Search for articles.
     *
     * @param ArticleSearchRequest $request
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function search(ArticleSearchRequest $request)
    {
        $filters = $request->validated();
        
        // Add search query to filters
        $filters['search'] = $request->query('query');

        $articles = $this->aggregator->getArticlesFromDatabase($filters);

        return new ArticleCollection($articles);
    }

    /**
     * Get article categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories(): JsonResponse
    {
        $categories = $this->aggregator->getCategoriesFromDatabase();
        return $this->successResponse(CategoryResource::collection($categories));
    }

    /**
     * Get article authors.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authors(): JsonResponse
    {
        $authors = $this->aggregator->getAuthorsFromDatabase();
        return $this->successResponse($authors);
    }
}
