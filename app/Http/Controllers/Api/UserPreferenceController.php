<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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
        $user = Auth::user();
        $preferences = $user->preference ?? UserPreference::create(['user_id' => $user->id]);
        
        return $this->successResponse(new UserPreferenceResource($preferences));
    }

    /**
     * Update the authenticated user's preferences.
     *
     * @param UserPreferenceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserPreferenceRequest $request): JsonResponse
    {
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
    }
}
