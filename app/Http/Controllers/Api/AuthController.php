<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\BaseApiController as Controller;

class AuthController extends Controller
{
    /**
     * Login user and create token
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Using 'web' guard explicitly because the default 'api' guard uses Sanctum driver
            // which doesn't support the attempt() method. The 'web' guard uses session driver that has this method.
            if (!Auth::guard('web')->attempt($request->only('email', 'password'))) {
                return $this->errorResponse('Invalid login credentials', 401);
            }
            
            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            return (new AuthResource($user, $token))->response()->setStatusCode(200);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Register a new user
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return (new AuthResource($user, $token, 'User registered successfully'))
                ->response()
                ->setStatusCode(201);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Logout user (revoke the token)
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Successfully logged out', 200);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }
}
