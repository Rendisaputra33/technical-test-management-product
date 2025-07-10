<?php

namespace App\Http\Middleware;

use App\Services\JWTService;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class JWTAuth
{
    private JWTService $jwtService;
    private AuthRepositoryInterface $authRepository;

    public function __construct(
        JWTService $jwtService,
        AuthRepositoryInterface $authRepository
    ) {
        $this->jwtService = $jwtService;
        $this->authRepository = $authRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $authHeader = $request->header('Authorization');
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization token required'
                ], 401);
            }

            $token = substr($authHeader, 7);
            if (!$this->jwtService->isValidTokenStructure($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token format'
                ], 401);
            }

            if ($this->jwtService->isTokenExpired($token, 'access')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token expired'
                ], 401);
            }

            $decoded = $this->jwtService->decodeAccessToken($token);
            $user = $this->authRepository->findById($decoded->user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 401);
            }

            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            $request->attributes->set('jwt_payload', $decoded);
            $request->attributes->set('jwt_token', $token);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('JWT Authentication failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 401);
        }
    }
}
