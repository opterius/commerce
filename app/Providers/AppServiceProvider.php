<?php

namespace App\Providers;

use App\Models\Staff;
use App\Provisioning\Modules\OpteriusPanelModule;
use App\Provisioning\ProvisioningModuleRegistry;
use App\Support\StaffPermissions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProvisioningModuleRegistry::class, function () {
            $registry = new ProvisioningModuleRegistry();

            // ── Built-in module (always available) ───────────────────────────
            $registry->register(OpteriusPanelModule::class);

            // ── Auto-discover plugin modules ──────────────────────────────────
            // Drop any class implementing ProvisioningModule into
            // app/Provisioning/Modules/ and it will be picked up automatically.
            $registry->discoverIn(
                app_path('Provisioning/Modules'),
                'App\\Provisioning\\Modules'
            );

            return $registry;
        });
    }

    public function boot(): void
    {
        $this->registerStaffGates();
        $this->registerStaffBladeDirectives();
    }

    /**
     * Register a Gate for every permission slug.
     * super_admin bypasses all via Gate::before().
     * All other staff are checked against their stored permissions array.
     */
    private function registerStaffBladeDirectives(): void
    {
        Blade::if('staffcan', function (string $permission) {
            $staff = auth('staff')->user();
            return $staff && $staff->hasPermission($permission);
        });
    }

    private function registerStaffGates(): void
    {
        // Before hook — super_admin always passes
        Gate::before(function ($user) {
            if ($user instanceof Staff && $user->role === 'super_admin') {
                return true;
            }
        });

        foreach (StaffPermissions::all() as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user instanceof Staff && $user->hasPermission($permission);
            });
        }
    }
}
