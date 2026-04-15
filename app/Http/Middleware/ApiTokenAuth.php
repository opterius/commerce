<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');

        if (! str_starts_with($header, 'Bearer ')) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $plaintext = substr($header, 7);
        $token     = PersonalAccessToken::findByPlaintext($plaintext);

        if (! $token || $token->isExpired()) {
            return response()->json(['error' => 'Invalid or expired token.'], 401);
        }

        $token->update(['last_used_at' => now()]);

        $request->merge(['_api_client' => $token->client]);

        return $next($request);
    }
}
