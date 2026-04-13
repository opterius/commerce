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

class SyncDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $backoff = 120;

    public function __construct(public readonly int $domainId) {}

    public function handle(): void
    {
        $domain = Domain::findOrFail($this->domainId);

        RegistrarService::sync($domain);

        ActivityLogger::log(
            'domain.synced',
            'domain',
            $domain->id,
            $domain->domain_name,
            'Domain info synced from registrar.'
        );
    }

    public function failed(\Throwable $e): void
    {
        $domain = Domain::find($this->domainId);
        if ($domain) {
            ActivityLogger::log(
                'domain.sync_failed',
                'domain',
                $domain->id,
                $domain->domain_name,
                'Sync failed: ' . $e->getMessage()
            );
        }
    }
}
