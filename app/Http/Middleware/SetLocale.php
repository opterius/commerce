<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        if (auth('staff')->check()) {
            $locale = auth('staff')->user()->locale;
        } elseif (auth('client')->check()) {
            $locale = auth('client')->user()->language;
        }

        if ($locale && array_key_exists($locale, config('commerce.available_locales', []))) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
