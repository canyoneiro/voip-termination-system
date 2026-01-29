<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
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

        // Update last used timestamp
        $apiToken->update(['last_used_at' => now()]);

        // Store the token in the request for use in controllers
        $request->attributes->set('api_token', $apiToken);

        return $next($request);
    }
}
