<?php

namespace App\Services;

use App\Jobs\CreateHostingAccountJob;
use App\Jobs\RegisterDomainJob;
use App\Jobs\RenewDomainJob;
use App\Jobs\UnsuspendHostingAccountJob;
use App\Models\{Client, ClientCredit, Currency, Domain, Invoice, InvoiceItem, Order, Payment, Product, Service, Setting, Staff};
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function generateInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $prefix      = Setting::get('invoice_prefix', 'INV-');
            $yearlyReset = (bool) Setting::get('invoice_yearly_reset', '1');
            $next        = (int) Setting::get('invoice_next_number', '1');

            if ($yearlyReset) {
                $prefix .= date('Y') . '-';
            }

            $number = $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);

            Setting::set('invoice_next_number', (string) ($next + 1), 'billing');

            return $number;
        });
    }

    public function createForOrder(Order $order): Invoice
    {
        $order->loadMissing(['items.product', 'promoCode']);

        $dueDays = (int) Setting::get('invoice_due_days', '7');
        $dueDate = now()->addDays($dueDays)->toDateString();

        $invoice = Invoice::create([
            'client_id'      => $order->client_id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'status'         => 'unpaid',
            'due_date'       => $dueDate,
            'currency_code'  => $order->currency_code,
            'subtotal'       => $order->subtotal,
            'tax'            => 0,
            'total'          => $order->total,
            'credit_applied' => 0,
        ]);

        foreach ($order->items as $item) {
            $productName  = $item->product ? $item->product->name : 'Product';
            $cycleLabel   = ucfirst(str_replace('_', ' ', $item->billing_cycle));
            $description  = "{$productName} — {$cycleLabel}";

            $lineAmount = ($item->price * $item->qty) + ($item->setup_fee * $item->qty);

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'service_id'  => null,
                'description' => $description,
                'amount'      => $lineAmount,
                'tax_amount'  => 0,
                'quantity'    => $item->qty,
            ]);
        }

        $order->update(['invoice_id' => $invoice->id]);

        return $invoice;
    }

    public function createForDomainRenewal(Domain $domain): Invoice
    {
        $domain->loadMissing('client');

        $dueDays     = (int) Setting::get('invoice_due_days', '7');
        $dueDate     = now()->addDays($dueDays)->toDateString();
        $years       = $domain->registrationYears();
        $description = 'Domain Renewal: ' . $domain->domain_name . ' (' . $years . ' year' . ($years > 1 ? 's' : '') . ')';

        $invoice = Invoice::create([
            'client_id'      => $domain->client_id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'status'         => 'unpaid',
            'due_date'       => $dueDate,
            'currency_code'  => $domain->currency_code,
            'subtotal'       => $domain->amount,
            'tax'            => 0,
            'total'          => $domain->amount,
            'credit_applied' => 0,
        ]);

        InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'domain_id'   => $domain->id,
            'description' => $description,
            'amount'      => $domain->amount,
            'tax_amount'  => 0,
            'quantity'    => 1,
        ]);

        return $invoice;
    }

    /**
     * Generate a renewal invoice for an active service.
     * Uses the service's stored amount/currency so the price is locked at what
     * the client originally agreed to (admin can override on the service record).
     */
    public function createForServiceRenewal(Service $service): Invoice
    {
        $service->loadMissing(['client', 'product']);

        $dueDays     = (int) Setting::get('invoice_due_days', '7');
        $dueDate     = now()->addDays($dueDays)->toDateString();
        $cycleLabel  = ucfirst(str_replace('_', ' ', $service->billing_cycle));
        $description = ($service->product?->name ?? 'Service') . ' — ' . $cycleLabel
                     . ($service->domain ? ' (' . $service->domain . ')' : '');

        $invoice = Invoice::create([
            'client_id'      => $service->client_id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'status'         => 'unpaid',
            'due_date'       => $dueDate,
            'currency_code'  => $service->currency_code,
            'subtotal'       => $service->amount,
            'tax'            => 0,
            'total'          => $service->amount,
            'credit_applied' => 0,
        ]);

        InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'service_id'  => $service->id,
            'description' => $description,
            'amount'      => $service->amount,
            'tax_amount'  => 0,
            'quantity'    => 1,
        ]);

        return $invoice;
    }

    public function createManual(Client $client, array $lineItems, string $currencyCode, string $dueDate, ?string $notes = null): Invoice
    {
        $subtotal = 0;
        foreach ($lineItems as $line) {
            $subtotal += (int) $line['amount'] * (int) ($line['qty'] ?? 1);
        }

        $invoice = Invoice::create([
            'client_id'      => $client->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'status'         => 'unpaid',
            'due_date'       => $dueDate,
            'currency_code'  => $currencyCode,
            'subtotal'       => $subtotal,
            'tax'            => 0,
            'total'          => $subtotal,
            'credit_applied' => 0,
            'notes'          => $notes,
        ]);

        foreach ($lineItems as $line) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $line['description'],
                'amount'      => (int) $line['amount'],
                'tax_amount'  => 0,
                'quantity'    => (int) ($line['qty'] ?? 1),
            ]);
        }

        return $invoice;
    }

    public function recordManualPayment(Invoice $invoice, array $data, Staff $staff): Payment
    {
        $payment = Payment::create([
            'invoice_id'     => $invoice->id,
            'gateway'        => 'manual',
            'transaction_id' => $data['transaction_id'] ?? null,
            'amount'         => (int) $data['amount'],
            'currency_code'  => $invoice->currency_code,
            'status'         => 'completed',
            'method'         => $data['method'],
            'notes'          => $data['notes'] ?? null,
        ]);

        $this->reconcileInvoice($invoice);

        ActivityLogger::log('invoice.payment_recorded', 'invoice', $invoice->id, $invoice->invoice_number, null, [
            'amount' => $data['amount'],
            'method' => $data['method'],
        ]);

        return $payment;
    }

    public function markPaid(Invoice $invoice): void
    {
        $invoice->update(['status' => 'paid', 'paid_date' => now()]);

        // Send payment confirmation email
        app(EmailService::class)->sendInvoicePaid($invoice);

        // Activate pending order if any
        if ($invoice->orders()->exists()) {
            $order = $invoice->orders()->first();
            if ($order && $order->status === 'pending') {
                $order->update(['status' => 'active']);

                // Hosting services
                $pendingServices = Service::where('order_id', $order->id)
                    ->where('status', 'pending')
                    ->with('product')
                    ->get();

                foreach ($pendingServices as $service) {
                    if ($service->needsProvisioning()) {
                        CreateHostingAccountJob::dispatch($service->id);
                    } else {
                        $service->update(['status' => 'active', 'registration_date' => now()->toDateString()]);
                    }
                }

                // Domain registrations
                $pendingDomains = Domain::where('order_id', $order->id)
                    ->where('status', 'pending')
                    ->get();

                foreach ($pendingDomains as $domain) {
                    RegisterDomainJob::dispatch($domain->id);
                }
            }
        }

        // Domain renewals linked to this invoice
        $domainIds = $invoice->items()
            ->where('description', 'like', 'Domain Renewal:%')
            ->pluck('domain_id')
            ->filter()
            ->unique();

        foreach ($domainIds as $domainId) {
            RenewDomainJob::dispatch($domainId);
        }

        // Renewal invoice paid: advance next_due_date for linked active/suspended services
        $renewalServiceIds = $invoice->items()
            ->whereNotNull('service_id')
            ->pluck('service_id')
            ->unique();

        if ($renewalServiceIds->isNotEmpty()) {
            Service::whereIn('id', $renewalServiceIds)
                ->whereIn('status', ['active', 'suspended'])
                ->get()
                ->each(function (Service $service) {
                    $next = $this->advanceNextDueDate($service);
                    $service->update([
                        'last_due_date' => $service->next_due_date,
                        'next_due_date' => $next,
                    ]);

                    // Unsuspend if suspended
                    if ($service->status === 'suspended') {
                        if ($service->needsProvisioning()) {
                            UnsuspendHostingAccountJob::dispatch($service->id);
                        } else {
                            $service->update(['status' => 'active', 'suspended_at' => null]);
                            app(EmailService::class)->sendServiceUnsuspended($service);
                        }
                    }
                });
        }

        ActivityLogger::log('invoice.paid', 'invoice', $invoice->id, $invoice->invoice_number);
    }

    private function advanceNextDueDate(Service $service): string
    {
        $base = $service->next_due_date ?? now();

        return match ($service->billing_cycle) {
            'monthly'     => $base->addMonth()->toDateString(),
            'quarterly'   => $base->addMonths(3)->toDateString(),
            'semi_annual' => $base->addMonths(6)->toDateString(),
            'annual'      => $base->addYear()->toDateString(),
            'biennial'    => $base->addYears(2)->toDateString(),
            'triennial'   => $base->addYears(3)->toDateString(),
            default       => $base->addMonth()->toDateString(),
        };
    }

    public function applyCredit(Invoice $invoice, int $amountCents): void
    {
        ClientCredit::create([
            'client_id'    => $invoice->client_id,
            'invoice_id'   => $invoice->id,
            'amount'       => -$amountCents,
            'currency_code'=> $invoice->currency_code,
            'description'  => 'Applied to invoice #' . $invoice->invoice_number,
            'type'         => 'debit',
        ]);

        $invoice->increment('credit_applied', $amountCents);

        $this->reconcileInvoice($invoice->fresh());

        ActivityLogger::log('invoice.credit_applied', 'invoice', $invoice->id, $invoice->invoice_number, null, [
            'amount' => $amountCents,
        ]);
    }

    public function recordStripePayment(Invoice $invoice, \Stripe\PaymentIntent $pi): Payment
    {
        $payment = Payment::create([
            'invoice_id'      => $invoice->id,
            'gateway'         => 'stripe',
            'transaction_id'  => $pi->id,
            'amount'          => $pi->amount_received,
            'currency_code'   => strtoupper($pi->currency),
            'status'          => 'completed',
            'method'          => 'card',
            'gateway_response'=> ['id' => $pi->id, 'status' => $pi->status],
        ]);

        $this->reconcileInvoice($invoice);

        ActivityLogger::log('invoice.payment_recorded', 'invoice', $invoice->id, $invoice->invoice_number, null, [
            'amount'  => $pi->amount_received,
            'gateway' => 'stripe',
        ]);

        return $payment;
    }

    private function reconcileInvoice(Invoice $invoice): void
    {
        $invoice->refresh();

        $paid = $invoice->payments()->where('status', 'completed')->sum('amount');
        $due  = max(0, $invoice->total - $paid - $invoice->credit_applied);

        if ($due <= 0 && $invoice->status !== 'cancelled') {
            $this->markPaid($invoice);
        }
    }
}
