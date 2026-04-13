<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: enforce a staff permission gate.
 *
 * Usage on a route:
 *   ->middleware('staff.can:invoices.refund')
 *
 * The check is always performed against the 'staff' guard,
 * not the default auth guard.
 */
class StaffCan
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $staff = auth('staff')->user();

        if (! $staff || ! $staff->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            abort(403);
        }

        return $next($request);
    }
}
