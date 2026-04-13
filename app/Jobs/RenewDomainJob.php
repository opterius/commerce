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

class RenewDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(public readonly int $domainId) {}

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(30);
    }

    public function handle(): void
    {
        $domain = Domain::findOrFail($this->domainId);

        if (! in_array($domain->status, ['active', 'expired'])) {
            return;
        }

        RegistrarService::renew($domain);

        ActivityLogger::log(
            'domain.renewed',
            'domain',
            $domain->id,
            $domain->domain_name,
            'Domain renewed. New expiry: ' . $domain->fresh()->expiry_date?->toDateString()
        );
    }

    public function failed(\Throwable $e): void
    {
        $domain = Domain::find($this->domainId);
        if ($domain) {
            ActivityLogger::log(
                'domain.renew_failed',
                'domain',
                $domain->id,
                $domain->domain_name,
                'Renewal failed: ' . $e->getMessage()
            );
        }
    }
}
