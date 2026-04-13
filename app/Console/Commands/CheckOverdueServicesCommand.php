<?php

namespace App\Console\Commands;

use App\Jobs\SuspendHostingAccountJob;
use App\Models\Service;
use App\Services\ActivityLogger;
use App\Services\EmailService;
use Illuminate\Console\Command;

class CheckOverdueServicesCommand extends Command
{
    protected $signature   = 'commerce:check-overdue-services';
    protected $description = 'Suspend services whose invoices are overdue past the grace period.';

    public function __construct(private EmailService $emailService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $graceDays = (int) \App\Models\Setting::get('grace_period_days', 3);
        $cutoff    = now()->subDays($graceDays);

        // Active services with an overdue invoice older than the grace period
        $services = Service::where('status', 'active')
            ->whereHas('invoiceItems.invoice', function ($q) use ($cutoff) {
                $q->where('status', 'overdue')
                  ->where('due_date', '<=', $cutoff);
            })
            ->with(['client', 'product', 'server'])
            ->get();

        $count = 0;
        foreach ($services as $service) {
            // Find the overdue invoice to include in the email
            $overdueInvoice = $service->invoiceItems()
                ->whereHas('invoice', fn($q) => $q->where('status', 'overdue'))
                ->with('invoice')
                ->first()
                ?->invoice;

            if ($service->needsProvisioning()) {
                SuspendHostingAccountJob::dispatch($service->id);
            } else {
                $service->update(['status' => 'suspended', 'suspended_at' => now()]);
                $this->emailService->sendServiceSuspended($service, $overdueInvoice);
            }

            ActivityLogger::log(
                'service.overdue_suspend',
                'service',
                $service->id,
                $service->domain ?? "service #{$service->id}",
                'Suspended due to overdue invoice.'
            );

            $count++;
        }

        $this->info("Suspended {$count} overdue service(s).");

        return Command::SUCCESS;
    }
}
