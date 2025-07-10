<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseApiController
{
    private AuthRepositoryInterface $authRepository;
    private JWTService $jwtService;

    public function __construct(
        AuthRepositoryInterface $authRepository,
        JWTService $jwtService
    ) {
        $this->authRepository = $authRepository;
        $this->jwtService = $jwtService;
    }

    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $user = $this->authRepository->createUser($validated);
            $tokenPayload = [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ];

            $tokens = $this->jwtService->generateTokenPair($tokenPayload);

            DB::commit();

            return $this->createdResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'tokens' => $tokens
            ], 'User registered successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed: ' . $e->getMessage());
            return $this->errorResponse('Registration failed', 500);
        }
    }

    /**
     * Login user
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = $this->authRepository->validateCredentials(
                $validated['email'],
                $validated['password']
            );

            if (!$user) {
                return $this->errorResponse('Invalid credentials', 401);
            }

            $tokenPayload = [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ];

            $tokens = $this->jwtService->generateTokenPair($tokenPayload);

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'last_login_at' => $user->last_login_at,
                ],
                'tokens' => $tokens
            ], 'Login successful');
        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            return $this->errorResponse('Login failed', 500);
        }
    }

    /**
     * Refresh access token using refresh token
     *
     * @param RefreshTokenRequest $request
     * @return JsonResponse
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $refreshToken = $validated['refresh_token'];

            if (!$this->jwtService->isValidTokenStructure($refreshToken)) {
                return $this->errorResponse('Invalid token format', 400);
            }

            if ($this->jwtService->isTokenExpired($refreshToken, 'refresh')) {
                return $this->errorResponse('Refresh token expired', 401);
            }

            $decoded = $this->jwtService->decodeRefreshToken($refreshToken);
            $user = $this->authRepository->findById($decoded->user_id);

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            $tokenPayload = [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ];

            $tokens = $this->jwtService->generateTokenPair($tokenPayload);

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'tokens' => $tokens
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            Log::error('Token refresh failed: ' . $e->getMessage());
            return $this->errorResponse('Token refresh failed: ' . $e->getMessage(), 401);
        }
    }

    /**
     * Get user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            return $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ], 'Profile retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Profile retrieval failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve profile', 500);
        }
    }

    /**
     * Logout user (invalidate tokens)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Note: In a production environment, you might want to blacklist the tokens
            // For now, we'll just return a success response
            // You could implement a token blacklist in Redis or database

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return $this->errorResponse('Logout failed', 500);
        }
    }

    /**
     * Validate access token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateToken(Request $request): JsonResponse
    {
        try {
            $authHeader = $request->header('Authorization');

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->errorResponse('Authorization header missing or invalid', 401);
            }

            $token = substr($authHeader, 7);

            if (!$this->jwtService->isValidTokenStructure($token)) {
                return $this->errorResponse('Invalid token format', 400);
            }

            if ($this->jwtService->isTokenExpired($token, 'access')) {
                return $this->errorResponse('Token expired', 401);
            }

            $decoded = $this->jwtService->decodeAccessToken($token);
            $user = $this->authRepository->findById($decoded->user_id);

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            return $this->successResponse([
                'valid' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'expires_at' => $decoded->exp,
            ], 'Token is valid');
        } catch (\Exception $e) {
            Log::error('Token validation failed: ' . $e->getMessage());
            return $this->errorResponse('Token validation failed: ' . $e->getMessage(), 401);
        }
    }
}
