<?php

namespace App\Jobs;

use App\Models\Domain;
use App\Services\ActivityLogger;
use App\Services\RegistrarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TransferDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int    $domainId,
        public readonly string $eppCode,
    ) {}

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(30);
    }

    public function handle(): void
    {
        $domain = Domain::with(['contacts'])->findOrFail($this->domainId);

        if ($domain->status !== 'pending') {
            return;
        }

        RegistrarService::transfer($domain, $this->eppCode);

        ActivityLogger::log(
            'domain.transfer_initiated',
            'domain',
            $domain->id,
            $domain->domain_name,
            'Domain transfer initiated via ' . $domain->registrar_module . '.'
        );
    }

    public function failed(\Throwable $e): void
    {
        $domain = Domain::find($this->domainId);
        if ($domain) {
            ActivityLogger::log(
                'domain.transfer_failed',
                'domain',
                $domain->id,
                $domain->domain_name,
                'Transfer failed: ' . $e->getMessage()
            );
        }
    }
}
