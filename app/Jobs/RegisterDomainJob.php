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

class RegisterDomainJob implements ShouldQueue
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
        $domain = Domain::with(['contacts', 'client'])->findOrFail($this->domainId);

        if ($domain->status !== 'pending') {
            return;
        }

        RegistrarService::register($domain);

        ActivityLogger::log(
            'domain.registered',
            'domain',
            $domain->id,
            $domain->domain_name,
            'Domain registered via ' . $domain->registrar_module . '.'
        );
    }

    public function failed(\Throwable $e): void
    {
        $domain = Domain::find($this->domainId);
        if ($domain) {
            ActivityLogger::log(
                'domain.register_failed',
                'domain',
                $domain->id,
                $domain->domain_name,
                'Registration failed: ' . $e->getMessage()
            );
        }
    }
}
