<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'API token is required',
                ],
            ], 401);
        }

        $hashedToken = hash('sha256', $token);

        $apiToken = ApiToken::where('token_hash', $hashedToken)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Invalid or expired API token',
                ],
            ], 401);
        }

        // Rate limiting check
        $rateLimitResponse = $this->checkRateLimit($apiToken);
        if ($rateLimitResponse) {
            return $rateLimitResponse;
        }

        // Update last used timestamp
        $apiToken->update(['last_used_at' => now()]);

        // Store the token in the request for use in controllers
        $request->attributes->set('api_token', $apiToken);

        $response = $next($request);

        // Add rate limit headers to response
        $this->addRateLimitHeaders($response, $apiToken);

        return $response;
    }

    /**
     * Check rate limit for the API token
     */
    protected function checkRateLimit(ApiToken $apiToken): ?Response
    {
        $rateLimit = $apiToken->rate_limit ?? 100; // requests per window
        $rateWindow = $apiToken->rate_limit_window ?? 60; // seconds

        $key = "api_rate_limit:{$apiToken->id}";

        try {
            // Get current request count
            $current = (int) Redis::get($key);

            if ($current >= $rateLimit) {
                // Get TTL for retry-after header
                $ttl = Redis::ttl($key);
                if ($ttl < 0) {
                    $ttl = $rateWindow;
                }

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RATE_LIMIT_EXCEEDED',
                        'message' => "Rate limit of {$rateLimit} requests per {$rateWindow} seconds exceeded",
                    ],
                ], 429)->withHeaders([
                    'X-RateLimit-Limit' => $rateLimit,
                    'X-RateLimit-Remaining' => 0,
                    'X-RateLimit-Reset' => time() + $ttl,
                    'Retry-After' => $ttl,
                ]);
            }

            // Increment counter
            Redis::incr($key);

            // Set expiry on first request
            if ($current == 0) {
                Redis::expire($key, $rateWindow);
            }
        } catch (\Exception $e) {
            // If Redis fails, allow the request but log the error
            \Log::warning('API rate limiting Redis error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Add rate limit headers to response
     */
    protected function addRateLimitHeaders(Response $response, ApiToken $apiToken): void
    {
        $rateLimit = $apiToken->rate_limit ?? 100;
        $rateWindow = $apiToken->rate_limit_window ?? 60;

        $key = "api_rate_limit:{$apiToken->id}";

        try {
            $current = (int) Redis::get($key);
            $remaining = max(0, $rateLimit - $current);
            $ttl = Redis::ttl($key);
            if ($ttl < 0) {
                $ttl = $rateWindow;
            }

            $response->headers->set('X-RateLimit-Limit', (string) $rateLimit);
            $response->headers->set('X-RateLimit-Remaining', (string) $remaining);
            $response->headers->set('X-RateLimit-Reset', (string) (time() + $ttl));
        } catch (\Exception $e) {
            // If Redis fails, just skip headers
        }
    }
}
