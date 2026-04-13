<?php

namespace App\Jobs;

use App\Models\Service;
use App\Services\ProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UnsuspendHostingAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int  $serviceId,
        public readonly ?int $triggeredBy = null,
    ) {}

    public function handle(ProvisioningService $provisioningService): void
    {
        $service = Service::with(['product', 'server'])->find($this->serviceId);

        if (! $service || $service->status !== 'suspended') {
            return;
        }

        $provisioningService->execute($service, 'unsuspend', $this->triggeredBy);
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(30);
    }
}
