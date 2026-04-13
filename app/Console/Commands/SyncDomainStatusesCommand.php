<?php

namespace App\Console\Commands;

use App\Jobs\SyncDomainJob;
use App\Models\Domain;
use Illuminate\Console\Command;

class SyncDomainStatusesCommand extends Command
{
    protected $signature   = 'commerce:sync-domain-statuses';
    protected $description = 'Sync all active domains with the registrar API to catch external changes.';

    public function handle(): int
    {
        $domains = Domain::whereIn('status', ['active', 'expired'])
            ->whereNotNull('registrar_order_id')
            ->get();

        $count = 0;
        foreach ($domains as $domain) {
            SyncDomainJob::dispatch($domain->id);
            $count++;
        }

        $this->info("Queued sync for {$count} domain(s).");

        return Command::SUCCESS;
    }
}
