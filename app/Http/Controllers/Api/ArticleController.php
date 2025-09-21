<?php

namespace App\Http\Controllers\Api;

use Throwable;
use Illuminate\Http\Request;
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
     * Get all articles with optional filtering.
     *
     * @param ArticleFilterRequest $request
     * @return \Illuminate\Http\Resources\Json\JsonResource|\Illuminate\Http\JsonResponse
     */
    public function index(ArticleFilterRequest $request)
    {
        try {
            $filters = $request->validated();
            $articles = $this->aggregator->getArticlesFromDatabase($filters);
            return new ArticleCollection($articles);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Search for articles.
     *
     * @param ArticleSearchRequest $request
     * @return \Illuminate\Http\Resources\Json\JsonResource|\Illuminate\Http\JsonResponse
     */
    public function search(ArticleSearchRequest $request)
    {
        try {
            $filters = $request->validated();
            
            // Add search query to filters
            $filters['search'] = $request->query('query');

            $articles = $this->aggregator->getArticlesFromDatabase($filters);

            return new ArticleCollection($articles);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get article categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = $this->aggregator->getCategoriesFromDatabase();
            return $this->successResponse(CategoryResource::collection($categories));
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get article authors.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authors(): JsonResponse
    {
        try {
            $authors = $this->aggregator->getAuthorsFromDatabase();
            return $this->successResponse($authors);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }
}
