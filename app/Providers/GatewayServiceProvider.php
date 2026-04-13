<?php

namespace App\Providers;

use App\Gateways\GatewayRegistry;
use App\Gateways\Modules\AuthorizeNetModule;
use App\Gateways\Modules\BankTransferModule;
use App\Gateways\Modules\MollieModule;
use App\Gateways\Modules\PayPalModule;
use App\Gateways\Modules\StripeModule;
use App\Gateways\Modules\TwoCheckoutModule;
use Illuminate\Support\ServiceProvider;

class GatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GatewayRegistry::class);
    }

    public function boot(): void
    {
        $registry = $this->app->make(GatewayRegistry::class);

        // Built-in gateways
        $registry->register(StripeModule::class);
        $registry->register(PayPalModule::class);
        $registry->register(BankTransferModule::class);
        $registry->register(MollieModule::class);
        $registry->register(TwoCheckoutModule::class);
        $registry->register(AuthorizeNetModule::class);

        // Third-party gateways from config
        // Developers add their class to config/commerce.php under 'gateway_modules'
        foreach (config('commerce.gateway_modules', []) as $class) {
            $registry->register($class);
        }
    }
}
