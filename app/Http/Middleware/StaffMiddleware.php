<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('staff')->check()) {
            return redirect()->route('staff.login');
        }

        if (!auth('staff')->user()->is_active) {
            auth('staff')->logout();
            return redirect()->route('staff.login')
                ->with('error', __('auth.account_disabled'));
        }

        return $next($request);
    }
}
