<?php

namespace App\Jobs;

use App\Models\Service;
use App\Services\ProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateHostingAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // seconds between retries; doubled each attempt

    public function __construct(
        public readonly int  $serviceId,
        public readonly ?int $triggeredBy = null,
    ) {}

    public function handle(ProvisioningService $provisioningService): void
    {
        $service = Service::with(['product.serverGroup', 'server', 'client'])->find($this->serviceId);

        if (! $service || ! in_array($service->status, ['pending', 'active'])) {
            return;
        }

        $provisioningService->execute($service, 'create', $this->triggeredBy);
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(30);
    }
}
