<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Services\ActivityLogger;
use App\Services\InvoiceService;
use Illuminate\Console\Command;

class CheckExpiringDomainsCommand extends Command
{
    protected $signature   = 'commerce:check-expiring-domains';
    protected $description = 'Generate renewal invoices for domains expiring within 30 days.';

    public function handle(): int
    {
        $cutoff = now()->addDays(30)->toDateString();

        $domains = Domain::where('status', 'active')
            ->where('auto_renew', true)
            ->where('next_due_date', '<=', $cutoff)
            ->whereDoesntHave('client.invoices', function ($q) {
                // Skip if an unpaid domain invoice already exists recently
                $q->where('status', 'unpaid')
                  ->where('created_at', '>=', now()->subDays(5));
            })
            ->with(['client', 'client.invoices'])
            ->get();

        $count = 0;
        foreach ($domains as $domain) {
            try {
                InvoiceService::createForDomainRenewal($domain);

                ActivityLogger::log(
                    'domain.renewal_invoice_created',
                    'domain',
                    $domain->id,
                    $domain->domain_name,
                    'Renewal invoice generated. Domain expires: ' . $domain->expiry_date?->toDateString()
                );

                $count++;
            } catch (\Throwable $e) {
                $this->error("Failed for {$domain->domain_name}: {$e->getMessage()}");
            }
        }

        $this->info("Generated {$count} domain renewal invoice(s).");

        return Command::SUCCESS;
    }
}
