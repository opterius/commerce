<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\Client;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailService
{
    /**
     * Resolve a template for the given mailable and client locale,
     * substitute variables, and send.
     */
    public function send(string $mailable, Client $client, array $vars = []): void
    {
        $locale   = $client->language ?: config('app.locale', 'en');
        $template = EmailTemplate::findFor($mailable, $locale);

        if (! $template) {
            return; // template disabled or doesn't exist — skip silently
        }

        $vars['{company_name}'] = Setting::get('company_name', config('app.name'));
        $vars['{client_name}']  = $client->full_name;

        $subject = str_replace(array_keys($vars), array_values($vars), $template->subject);
        $body    = str_replace(array_keys($vars), array_values($vars), $template->body);

        Mail::to($client->email)
            ->queue(new NotificationMail($subject, $body));
    }

    // ── Convenience senders ───────────────────────────────────────────────────

    public function sendWelcome(Client $client): void
    {
        $this->send('client.welcome', $client, [
            '{login_url}' => url('/client/login'),
        ]);
    }

    public function sendInvoiceGenerated(Invoice $invoice): void
    {
        $invoice->loadMissing('client');

        $this->send('invoice.generated', $invoice->client, [
            '{invoice_number}'   => $invoice->invoice_number,
            '{invoice_total}'    => $this->formatAmount($invoice->total, $invoice->currency_code),
            '{invoice_due_date}' => $invoice->due_date->format(config('commerce.date_format', 'Y-m-d')),
            '{invoice_url}'      => url('/client/invoices/' . $invoice->id),
        ]);
    }

    public function sendInvoiceOverdue(Invoice $invoice): void
    {
        $invoice->loadMissing('client');

        $this->send('invoice.overdue', $invoice->client, [
            '{invoice_number}'   => $invoice->invoice_number,
            '{invoice_total}'    => $this->formatAmount($invoice->total, $invoice->currency_code),
            '{invoice_due_date}' => $invoice->due_date->format(config('commerce.date_format', 'Y-m-d')),
            '{invoice_url}'      => url('/client/invoices/' . $invoice->id),
            '{grace_period_days}'=> (string) Setting::get('grace_period_days', 5),
        ]);
    }

    public function sendInvoicePaid(Invoice $invoice): void
    {
        $invoice->loadMissing('client');

        $this->send('invoice.paid', $invoice->client, [
            '{invoice_number}' => $invoice->invoice_number,
            '{invoice_total}'  => $this->formatAmount($invoice->total, $invoice->currency_code),
        ]);
    }

    public function sendServiceActivated(Service $service): void
    {
        $service->loadMissing(['client', 'product']);

        $this->send('service.activated', $service->client, [
            '{service_name}'   => $service->product?->name ?? "Service #{$service->id}",
            '{service_domain}' => $service->domain ?? '—',
        ]);
    }

    public function sendServiceSuspended(Service $service, ?Invoice $invoice = null): void
    {
        $service->loadMissing(['client', 'product']);

        $this->send('service.suspended', $service->client, [
            '{service_name}'   => $service->product?->name ?? "Service #{$service->id}",
            '{service_domain}' => $service->domain ?? '—',
            '{invoice_url}'    => $invoice ? url('/client/invoices/' . $invoice->id) : url('/client/invoices'),
        ]);
    }

    public function sendServiceUnsuspended(Service $service): void
    {
        $service->loadMissing(['client', 'product']);

        $this->send('service.unsuspended', $service->client, [
            '{service_name}'   => $service->product?->name ?? "Service #{$service->id}",
            '{service_domain}' => $service->domain ?? '—',
        ]);
    }

    public function sendServiceTerminated(Service $service): void
    {
        $service->loadMissing(['client', 'product']);

        $this->send('service.terminated', $service->client, [
            '{service_name}'   => $service->product?->name ?? "Service #{$service->id}",
            '{service_domain}' => $service->domain ?? '—',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function formatAmount(int $cents, string $currencyCode): string
    {
        $currency = \App\Models\Currency::where('code', $currencyCode)->first();

        if ($currency) {
            return $currency->format($cents);
        }

        return number_format($cents / 100, 2) . ' ' . $currencyCode;
    }
}
