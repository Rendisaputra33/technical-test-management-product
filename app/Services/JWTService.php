<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class JWTService
{
    private string $secretKey;
    private string $refreshSecretKey;
    private string $algorithm;
    private int $accessTokenTTL;
    private int $refreshTokenTTL;

    public function __construct()
    {
        $this->secretKey = config('app.jwt_secret', env('JWT_SECRET', 'your-secret-key'));
        $this->refreshSecretKey = config('app.jwt_refresh_secret', env('JWT_REFRESH_SECRET', 'your-refresh-secret-key'));
        $this->algorithm = 'HS256';
        $this->accessTokenTTL = 3600; // 1 hour
        $this->refreshTokenTTL = 604800; // 7 days
    }

    /**
     * Generate access token
     *
     * @param array $payload
     * @return string
     */
    public function generateAccessToken(array $payload): string
    {
        $issuedAt = Carbon::now()->timestamp;
        $expirationTime = $issuedAt + $this->accessTokenTTL;

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'type' => 'access'
        ]);

        return JWT::encode($tokenPayload, $this->secretKey, $this->algorithm);
    }

    /**
     * Generate refresh token
     *
     * @param array $payload
     * @return string
     */
    public function generateRefreshToken(array $payload): string
    {
        $issuedAt = Carbon::now()->timestamp;
        $expirationTime = $issuedAt + $this->refreshTokenTTL;

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'type' => 'refresh'
        ]);

        return JWT::encode($tokenPayload, $this->refreshSecretKey, $this->algorithm);
    }

    /**
     * Decode access token
     *
     * @param string $token
     * @return object
     * @throws \Exception
     */
    public function decodeAccessToken(string $token): object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));

            if ($decoded->type !== 'access') {
                throw new \Exception('Invalid token type');
            }

            return $decoded;
        } catch (\Exception $e) {
            throw new \Exception('Invalid or expired access token: ' . $e->getMessage());
        }
    }

    /**
     * Decode refresh token
     *
     * @param string $token
     * @return object
     * @throws \Exception
     */
    public function decodeRefreshToken(string $token): object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->refreshSecretKey, $this->algorithm));

            if ($decoded->type !== 'refresh') {
                throw new \Exception('Invalid token type');
            }

            return $decoded;
        } catch (\Exception $e) {
            throw new \Exception('Invalid or expired refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Generate both access and refresh tokens
     *
     * @param array $payload
     * @return array
     */
    public function generateTokenPair(array $payload): array
    {
        return [
            'access_token' => $this->generateAccessToken($payload),
            'refresh_token' => $this->generateRefreshToken($payload),
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenTTL
        ];
    }

    /**
     * Validate token structure
     *
     * @param string $token
     * @return bool
     */
    public function isValidTokenStructure(string $token): bool
    {
        $parts = explode('.', $token);
        return count($parts) === 3;
    }

    /**
     * Get token expiration time
     *
     * @param string $token
     * @param string $type
     * @return int|null
     */
    public function getTokenExpiration(string $token, string $type = 'access'): ?int
    {
        try {
            if ($type === 'access') {
                $decoded = $this->decodeAccessToken($token);
            } else {
                $decoded = $this->decodeRefreshToken($token);
            }

            return $decoded->exp ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if token is expired
     *
     * @param string $token
     * @param string $type
     * @return bool
     */
    public function isTokenExpired(string $token, string $type = 'access'): bool
    {
        $expiration = $this->getTokenExpiration($token, $type);

        if (!$expiration) {
            return true;
        }

        return Carbon::now()->timestamp > $expiration;
    }

    /**
     * Extract user ID from token
     *
     * @param string $token
     * @param string $type
     * @return int|null
     */
    public function getUserIdFromToken(string $token, string $type = 'access'): ?int
    {
        try {
            if ($type === 'access') {
                $decoded = $this->decodeAccessToken($token);
            } else {
                $decoded = $this->decodeRefreshToken($token);
            }

            return $decoded->user_id ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
