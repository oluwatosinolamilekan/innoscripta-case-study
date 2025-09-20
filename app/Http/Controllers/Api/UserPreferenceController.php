<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as Controller;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Requests\UserPreferenceRequest;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\UserPreferenceResource;
use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserPreferenceController extends Controller
{
    /**
     * Get the authenticated user's preferences.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $preferences = $user->preference ?? UserPreference::create(['user_id' => $user->id]);
            
            return $this->successResponse(new UserPreferenceResource($preferences));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the authenticated user's preferences.
     *
     * @param UserPreferenceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserPreferenceRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $preferences = $user->preference ?? UserPreference::create(['user_id' => $user->id]);

            $validated = $request->validated();
            
            // Map the request fields to the model fields
            $preferenceData = [
                'preferred_sources' => $validated['sources'] ?? [],
                'preferred_categories' => $validated['categories'] ?? [],
                'preferred_authors' => $validated['authors'] ?? [],
            ];

            $preferences->update($preferenceData);

            return $this->successResponse(
                new UserPreferenceResource($preferences),
                'Preferences updated successfully.'
            );
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get articles based on user preferences.
     *
     * @param ArticleFilterRequest $request
     * @return \Illuminate\Http\Resources\Json\JsonResource|\Illuminate\Http\JsonResponse
     */
    public function articles(ArticleFilterRequest $request)
    {
        try {
            $user = Auth::user();
            $preferences = $user->preference;

            if (!$preferences) {
                return $this->successResponse([], 'No preferences set.');
            }

            $query = Article::with('source')->orderBy('published_at', 'desc');

            // Apply user preferences
            if (!empty($preferences->preferred_sources)) {
                $query->whereIn('source_id', $preferences->preferred_sources);
            }

            if (!empty($preferences->preferred_categories)) {
                $query->whereIn('category', $preferences->preferred_categories);
            }

            if (!empty($preferences->preferred_authors)) {
                $query->where(function ($q) use ($preferences) {
                    foreach ($preferences->preferred_authors as $author) {
                        $q->orWhere('author', 'like', '%' . $author . '%');
                    }
                });
            }

            // Apply additional filters
            $filters = $request->validated();

            if (!empty($filters['search'])) {
                $query->whereFullText(['title', 'description'], $filters['search']);
            }

            if (!empty($filters['date_from'])) {
                $query->where('published_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('published_at', '<=', $filters['date_to']);
            }

            // Apply pagination
            $perPage = $filters['per_page'] ?? 15;
            $page = $filters['page'] ?? 1;

            $articles = $query->paginate($perPage, ['*'], 'page', $page);

            return new ArticleCollection($articles);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}
