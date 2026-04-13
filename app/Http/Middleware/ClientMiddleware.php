<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('client')->check()) {
            return redirect()->route('client.login');
        }

        return $next($request);
    }
}
