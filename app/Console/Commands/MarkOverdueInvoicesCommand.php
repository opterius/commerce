<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\EmailService;
use Illuminate\Console\Command;

class MarkOverdueInvoicesCommand extends Command
{
    protected $signature   = 'commerce:mark-overdue-invoices';
    protected $description = 'Mark unpaid invoices as overdue and send overdue reminder emails.';

    public function __construct(private EmailService $emailService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $invoices = Invoice::where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString())
            ->with('client')
            ->get();

        $count = 0;
        foreach ($invoices as $invoice) {
            $invoice->update(['status' => 'overdue']);

            // Send overdue reminder only once (guarded by overdue_notified_at)
            if (! $invoice->overdue_notified_at) {
                $this->emailService->sendInvoiceOverdue($invoice);
                $invoice->update(['overdue_notified_at' => now()]);
            }

            $count++;
        }

        $this->info("Marked {$count} invoice(s) as overdue.");

        return Command::SUCCESS;
    }
}
