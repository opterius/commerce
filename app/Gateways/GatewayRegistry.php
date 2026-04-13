<?php

namespace App\Gateways;

use App\Gateways\Contracts\PaymentGatewayModule;
use App\Models\Setting;

class GatewayRegistry
{
    /** @var array<string, class-string<PaymentGatewayModule>> */
    private array $modules = [];

    /**
     * Register a gateway module class.
     * Called by GatewayServiceProvider for built-ins,
     * and by third-party service providers for custom gateways.
     */
    public function register(string $class): void
    {
        /** @var PaymentGatewayModule $instance */
        $instance = app($class);
        $this->modules[$instance->slug()] = $class;
    }

    /**
     * Get a gateway instance by slug.
     */
    public function get(string $slug): PaymentGatewayModule
    {
        if (! isset($this->modules[$slug])) {
            throw new \InvalidArgumentException("Payment gateway '{$slug}' is not registered.");
        }

        return app($this->modules[$slug]);
    }

    /**
     * All registered gateway instances.
     *
     * @return array<string, PaymentGatewayModule>
     */
    public function all(): array
    {
        return collect($this->modules)
            ->mapWithKeys(fn($class, $slug) => [$slug => app($class)])
            ->all();
    }

    /**
     * Only gateways that are enabled and configured.
     *
     * @return array<string, PaymentGatewayModule>
     */
    public function active(): array
    {
        return collect($this->all())
            ->filter(fn(PaymentGatewayModule $gw) =>
                (bool) Setting::get("gateway_{$gw->slug()}_enabled", false)
                && $gw->isConfigured()
            )
            ->all();
    }

    /**
     * Convenience: get the only active gateway, or null if 0 or >1 are active.
     */
    public function sole(): ?PaymentGatewayModule
    {
        $active = $this->active();
        return count($active) === 1 ? array_values($active)[0] : null;
    }

    /**
     * Read a setting value for a given gateway.
     * Key: gateway_{slug}_{field}
     */
    public static function config(string $slug, string $field, mixed $default = null): mixed
    {
        return Setting::get("gateway_{$slug}_{$field}", $default);
    }
}
