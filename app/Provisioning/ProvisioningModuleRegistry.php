<?php

namespace App\Provisioning;

use App\Contracts\ProvisioningModule;
use RuntimeException;

/**
 * Discovers and resolves provisioning modules.
 *
 * Built-in modules are registered explicitly in AppServiceProvider.
 * Plugin modules are auto-discovered from app/Provisioning/Modules/.
 * A developer adds a new provider by dropping one class file in that
 * directory — no core code changes required.
 */
class ProvisioningModuleRegistry
{
    /** @var array<string, class-string<ProvisioningModule>> id => FQCN */
    private array $modules = [];

    /**
     * Register a module class.
     * Safe to call multiple times with the same class (idempotent).
     */
    public function register(string $class): void
    {
        if (! is_subclass_of($class, ProvisioningModule::class)) {
            throw new RuntimeException("{$class} must implement ProvisioningModule.");
        }

        $this->modules[$class::moduleId()] = $class;
    }

    /**
     * Resolve a module instance by its id.
     */
    public function resolve(string $id): ProvisioningModule
    {
        $class = $this->modules[$id] ?? null;

        if (! $class) {
            throw new RuntimeException("Unknown provisioning module: \"{$id}\".");
        }

        return app($class);
    }

    public function has(string $id): bool
    {
        return isset($this->modules[$id]);
    }

    /**
     * All registered modules as structured metadata.
     *
     * Returns: [ id => ['id', 'label', 'fields'] ]
     */
    public function all(): array
    {
        $result = [];

        foreach ($this->modules as $id => $class) {
            $result[$id] = [
                'id'          => $id,
                'label'       => $class::moduleLabel(),
                'description' => $class::moduleDescription(),
                'fields'      => $class::moduleFields(),
            ];
        }

        return $result;
    }

    /**
     * Auto-discover all ProvisioningModule implementations in a directory.
     * Called from AppServiceProvider after registering built-in modules.
     */
    public function discoverIn(string $directory, string $namespace): void
    {
        foreach (glob($directory . '/*.php') as $file) {
            $class = $namespace . '\\' . basename($file, '.php');

            if (
                class_exists($class)
                && is_subclass_of($class, ProvisioningModule::class)
                && ! $this->has($class::moduleId())
            ) {
                $this->register($class);
            }
        }
    }
}
