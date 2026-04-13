<?php

namespace App\Gateways\Modules;

use App\Gateways\Contracts\GatewayResult;
use App\Gateways\Contracts\PaymentGatewayModule;
use App\Gateways\GatewayRegistry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\StripeService;
use Illuminate\Http\Request;

class StripeModule implements PaymentGatewayModule
{
    public function name(): string { return 'Stripe'; }
    public function slug(): string { return 'stripe'; }

    public function settingsFields(): array
    {
        return [
            ['key' => 'publishable_key', 'label' => 'Publishable Key', 'type' => 'text',     'required' => true,  'help' => 'Starts with pk_live_ or pk_test_'],
            ['key' => 'secret_key',      'label' => 'Secret Key',      'type' => 'password',  'required' => true,  'help' => 'Starts with sk_live_ or sk_test_'],
            ['key' => 'webhook_secret',  'label' => 'Webhook Secret',  'type' => 'password',  'required' => false, 'help' => 'From your Stripe webhook endpoint settings'],
        ];
    }

    public function isConfigured(): bool
    {
        return filled(GatewayRegistry::config('stripe', 'publishable_key'))
            && filled(GatewayRegistry::config('stripe', 'secret_key'));
    }

    public function supportsRedirect(): bool { return false; }

    public function prepareData(Invoice $invoice): array
    {
        \Stripe\Stripe::setApiKey($this->secretKey());

        $client     = $invoice->client;
        $customerId = app(StripeService::class)->getOrCreateCustomer($client);

        $intent = \Stripe\PaymentIntent::create([
            'amount'   => $invoice->amount_due,
            'currency' => strtolower($invoice->currency_code),
            'customer' => $customerId,
            'metadata' => ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number],
        ]);

        $savedMethods = $client->paymentMethods()->get();

        return [
            'clientSecret'    => $intent->client_secret,
            'publishableKey'  => GatewayRegistry::config('stripe', 'publishable_key'),
            'savedMethods'    => $savedMethods,
        ];
    }

    public function formView(): string { return 'gateways.stripe.form'; }

    public function redirectUrl(Invoice $invoice): string { return ''; }

    public function charge(Invoice $invoice, Request $request): GatewayResult
    {
        $request->validate(['payment_intent_id' => ['required', 'string']]);

        try {
            \Stripe\Stripe::setApiKey($this->secretKey());
            $pi = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

            if ($pi->status !== 'succeeded') {
                return GatewayResult::failure('Payment not completed. Please try again.');
            }

            // Save payment method if requested
            if ($request->boolean('save_method') && $pi->payment_method) {
                try {
                    app(StripeService::class)->savePaymentMethod($invoice->client, $pi->payment_method);
                } catch (\Throwable) {
                    // Non-fatal — card was charged successfully
                }
            }

            return GatewayResult::success($pi->id, ['status' => $pi->status]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return GatewayResult::failure($e->getMessage());
        }
    }

    public function handleWebhook(Request $request): void
    {
        $webhookSecret = GatewayRegistry::config('stripe', 'webhook_secret');

        if (! $webhookSecret) return;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                $webhookSecret,
            );
        } catch (\Throwable) {
            abort(400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $pi        = $event->data->object;
            $invoiceId = $pi->metadata->invoice_id ?? null;

            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice && $invoice->status !== 'paid') {
                    app(InvoiceService::class)->markPaid($invoice);
                }
            }
        }
    }

    public function supportsRefund(): bool { return true; }

    public function refund(Payment $payment, int $amountCents): GatewayResult
    {
        try {
            \Stripe\Stripe::setApiKey($this->secretKey());

            $refund = \Stripe\Refund::create([
                'payment_intent' => $payment->transaction_id,
                'amount'         => $amountCents,
            ]);

            return GatewayResult::success($refund->id, ['refund_id' => $refund->id]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return GatewayResult::failure($e->getMessage());
        }
    }

    private function secretKey(): string
    {
        return GatewayRegistry::config('stripe', 'secret_key')
            ?: config('services.stripe.secret'); // fallback to env for existing installs
    }
}
