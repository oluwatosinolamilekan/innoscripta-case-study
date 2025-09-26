<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ArticleCollection;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Controllers\Api\BaseApiController as Controller;

class PersonalizedArticleController extends Controller
{
    /**
     * Get articles based on user preferences.
     *
     * @param ArticleFilterRequest $request
     * @return \Illuminate\Http\Resources\Json\JsonResource|\Illuminate\Http\JsonResponse
     */
    public function index(ArticleFilterRequest $request)
    {
        $user = Auth::user();
        $preferences = $user->preference;

        if (!$preferences) {
            return $this->successResponse([], 'No preferences set.');
        }

        $query = Article::with('source')
            ->applyUserPreferences($preferences)
            ->orderBy('published_at', 'desc');

        // Apply additional filters
        $filters = $request->validated();
        
        // Apply filters using the model scope
        $query->applyFilters($filters);

        // Apply pagination
        $perPage = $filters['per_page'] ?? 15;
        $page = $filters['page'] ?? 1;

        $articles = $query->paginate($perPage, ['*'], 'page', $page);

        return new ArticleCollection($articles);
    }
}
