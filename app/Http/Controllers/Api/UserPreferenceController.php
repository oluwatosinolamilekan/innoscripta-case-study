<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ArticleCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Requests\UserPreferenceRequest;
use App\Http\Resources\UserPreferenceResource;
use App\Http\Controllers\Api\BaseApiController as Controller;

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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }
}
