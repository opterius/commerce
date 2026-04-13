<?php

namespace App\Services;

use App\Jobs\CreateHostingAccountJob;
use App\Jobs\UnsuspendHostingAccountJob;
use App\Models\{Client, ClientCredit, Invoice, InvoiceItem, Order, Payment, Service, Staff};
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

        // Activate pending order if any
        if ($invoice->orders()->exists()) {
            $order = $invoice->orders()->first();
            if ($order && $order->status === 'pending') {
                $order->update(['status' => 'active']);

                $pendingServices = Service::where('order_id', $order->id)
                    ->where('status', 'pending')
                    ->with('product')
                    ->get();

                foreach ($pendingServices as $service) {
                    if ($service->needsProvisioning()) {
                        // Queue provisioning; job will set status to active on success
                        CreateHostingAccountJob::dispatch($service->id);
                    } else {
                        $service->update(['status' => 'active', 'registration_date' => now()->toDateString()]);
                    }
                }
            }
        }

        // Unsuspend suspended services linked to this invoice's items
        $suspendedServiceIds = $invoice->items()
            ->whereNotNull('service_id')
            ->pluck('service_id')
            ->unique();

        if ($suspendedServiceIds->isNotEmpty()) {
            Service::whereIn('id', $suspendedServiceIds)
                ->where('status', 'suspended')
                ->with('product')
                ->get()
                ->each(function (Service $service) {
                    if ($service->needsProvisioning()) {
                        UnsuspendHostingAccountJob::dispatch($service->id);
                    } else {
                        $service->update(['status' => 'active', 'suspended_at' => null]);
                    }
                });
        }

        ActivityLogger::log('invoice.paid', 'invoice', $invoice->id, $invoice->invoice_number);
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
