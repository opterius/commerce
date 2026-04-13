<?php

namespace App\Services;

use App\Contracts\ProvisioningModule;
use App\Models\ProvisioningLog;
use App\Models\Server;
use App\Models\Service;
use App\Provisioning\ProvisioningResult;

class ProvisioningService
{
    /** Map provisioning_module slug to class */
    private const MODULE_MAP = [
        'opterius_panel' => \App\Provisioning\Modules\OpteriusPanelModule::class,
    ];

    public function resolveModule(Service $service): ?ProvisioningModule
    {
        $module = $service->product?->provisioning_module;
        if (! $module || ! isset(self::MODULE_MAP[$module])) {
            return null;
        }

        return app(self::MODULE_MAP[$module]);
    }

    /**
     * Dispatch a provisioning action as a queued job.
     */
    public function dispatch(Service $service, string $action, ?int $triggeredBy = null): void
    {
        $jobClass = match ($action) {
            'create'    => \App\Jobs\CreateHostingAccountJob::class,
            'suspend'   => \App\Jobs\SuspendHostingAccountJob::class,
            'unsuspend' => \App\Jobs\UnsuspendHostingAccountJob::class,
            'terminate' => \App\Jobs\TerminateHostingAccountJob::class,
            default     => throw new \InvalidArgumentException("Unknown provisioning action: {$action}"),
        };

        $jobClass::dispatch($service->id, $triggeredBy);
    }

    /**
     * Execute a provisioning action immediately (synchronously).
     */
    public function execute(Service $service, string $action, ?int $triggeredBy = null): ProvisioningResult
    {
        $module = $this->resolveModule($service);
        if (! $module) {
            return ProvisioningResult::failure('No provisioning module configured for this service.');
        }

        // Assign a server if needed
        if (in_array($action, ['create']) && ! $service->server_id) {
            $server = $service->product?->serverGroup?->bestServer();
            if (! $server) {
                return ProvisioningResult::failure('No available server in server group.');
            }
            $service->update(['server_id' => $server->id]);
            $service->refresh();
        }

        $log = ProvisioningLog::create([
            'service_id'   => $service->id,
            'action'       => $action,
            'status'       => 'pending',
            'triggered_by' => $triggeredBy,
        ]);

        $result = match ($action) {
            'create'    => $module->createAccount($service),
            'suspend'   => $module->suspendAccount($service),
            'unsuspend' => $module->unsuspendAccount($service),
            'terminate' => $module->terminateAccount($service),
            'info'      => $module->getAccountInfo($service),
            default     => ProvisioningResult::failure("Unknown action: {$action}"),
        };

        $log->update([
            'status'   => $result->success ? 'success' : 'failed',
            'response' => $result->data,
            'error'    => $result->error ?: null,
        ]);

        if ($result->success) {
            $this->applyResult($service, $action);
        }

        return $result;
    }

    /**
     * Update service status after a successful provisioning action.
     */
    public function applyResult(Service $service, string $action): void
    {
        match ($action) {
            'create'    => $service->update(['status' => 'active', 'registration_date' => now()]),
            'suspend'   => $service->update(['status' => 'suspended', 'suspended_at' => now()]),
            'unsuspend' => $service->update(['status' => 'active', 'suspended_at' => null]),
            'terminate' => $service->update(['status' => 'terminated', 'terminated_at' => now()]),
            default     => null,
        };

        // Increment/decrement server account count
        if ($service->server_id) {
            if ($action === 'create') {
                Server::where('id', $service->server_id)->increment('account_count');
            } elseif ($action === 'terminate') {
                Server::where('id', $service->server_id)->decrement('account_count');
            }
        }
    }
}
