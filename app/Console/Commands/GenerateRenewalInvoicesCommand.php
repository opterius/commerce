<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\Setting;
use App\Services\EmailService;
use App\Services\InvoiceService;
use Illuminate\Console\Command;

class GenerateRenewalInvoicesCommand extends Command
{
    protected $signature   = 'commerce:generate-renewal-invoices';
    protected $description = 'Generate renewal invoices for services due within the configured advance window.';

    public function __construct(
        private InvoiceService $invoiceService,
        private EmailService   $emailService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $advanceDays = (int) Setting::get('invoice_advance_days', 7);
        $window      = now()->addDays($advanceDays)->toDateString();

        // Active services whose next_due_date falls within the window
        // and do not already have an unpaid/pending renewal invoice for this service
        $services = Service::where('status', 'active')
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '<=', $window)
            ->whereDoesntHave('invoiceItems', function ($q) {
                $q->whereHas('invoice', fn($q) => $q->whereIn('status', ['unpaid', 'overdue']));
            })
            ->with(['client', 'product'])
            ->get();

        $count = 0;
        foreach ($services as $service) {
            try {
                $invoice = $this->invoiceService->createForServiceRenewal($service);
                $this->emailService->sendInvoiceGenerated($invoice);
                $count++;
            } catch (\Throwable $e) {
                $this->error("Service #{$service->id}: {$e->getMessage()}");
            }
        }

        $this->info("Generated {$count} renewal invoice(s).");

        return Command::SUCCESS;
    }
}
