<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

/**
 * Base controller for all admin controllers.
 * Overrides authorize() to use the 'staff' guard,
 * since admin routes are not on the default auth guard.
 */
abstract class AdminController extends Controller
{
    /**
     * Authorize a staff permission against the currently authenticated staff user.
     *
     * @throws AuthorizationException
     */
    protected function authorize(string $permission, mixed ...$arguments): void
    {
        $staff = auth('staff')->user();

        if (! $staff || ! Gate::forUser($staff)->allows($permission, ...$arguments)) {
            throw new AuthorizationException();
        }
    }

    /**
     * Check without throwing.
     */
    protected function staffCan(string $permission): bool
    {
        $staff = auth('staff')->user();

        return $staff && Gate::forUser($staff)->allows($permission);
    }
}
