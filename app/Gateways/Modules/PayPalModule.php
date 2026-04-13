<?php

namespace App\Gateways\Modules;

use App\Gateways\Contracts\GatewayResult;
use App\Gateways\Contracts\PaymentGatewayModule;
use App\Gateways\GatewayRegistry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalModule implements PaymentGatewayModule
{
    public function name(): string { return 'PayPal'; }
    public function slug(): string { return 'paypal'; }

    public function settingsFields(): array
    {
        return [
            ['key' => 'client_id',     'label' => 'Client ID',     'type' => 'text',     'required' => true,  'help' => 'Found in your PayPal developer dashboard under My Apps & Credentials'],
            ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'required' => true],
            ['key' => 'sandbox',       'label' => 'Sandbox Mode',  'type' => 'toggle',   'required' => false, 'help' => 'Use PayPal sandbox for testing'],
        ];
    }

    public function isConfigured(): bool
    {
        return filled(GatewayRegistry::config('paypal', 'client_id'))
            && filled(GatewayRegistry::config('paypal', 'client_secret'));
    }

    public function supportsRedirect(): bool { return false; }

    /**
     * Create a PayPal Order and return the data needed to render Smart Buttons.
     */
    public function prepareData(Invoice $invoice): array
    {
        $token   = $this->getAccessToken();
        $orderId = $this->createOrder($token, $invoice);

        return [
            'paypalClientId' => GatewayRegistry::config('paypal', 'client_id'),
            'paypalOrderId'  => $orderId,
            'currency'       => $invoice->currency_code,
        ];
    }

    public function formView(): string { return 'gateways.paypal.form'; }

    public function redirectUrl(Invoice $invoice): string { return ''; }

    /**
     * Capture the approved PayPal order and verify it.
     * The form posts 'paypal_order_id' after JS approval.
     */
    public function charge(Invoice $invoice, Request $request): GatewayResult
    {
        $request->validate(['paypal_order_id' => ['required', 'string']]);

        $orderId = $request->input('paypal_order_id');

        try {
            $token    = $this->getAccessToken();
            $capture  = $this->captureOrder($token, $orderId);

            if (($capture['status'] ?? '') !== 'COMPLETED') {
                return GatewayResult::failure('PayPal payment was not completed. Status: ' . ($capture['status'] ?? 'unknown'));
            }

            $captureId = $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? $orderId;

            return GatewayResult::success($captureId, $capture);

        } catch (\Throwable $e) {
            Log::error('PayPal charge error', ['message' => $e->getMessage(), 'invoice' => $invoice->id]);
            return GatewayResult::failure('PayPal error: ' . $e->getMessage());
        }
    }

    /**
     * Handle PayPal webhooks (PAYMENT.CAPTURE.COMPLETED).
     * Verify authenticity by re-fetching the capture from PayPal API.
     */
    public function handleWebhook(Request $request): void
    {
        $event    = $request->json()->all();
        $eventType = $event['event_type'] ?? '';

        if ($eventType !== 'PAYMENT.CAPTURE.COMPLETED') {
            return;
        }

        $captureId = $event['resource']['id'] ?? null;
        $invoiceId = $event['resource']['custom_id'] ?? null;

        if (! $captureId || ! $invoiceId) return;

        // Verify by fetching the capture from PayPal directly
        try {
            $token    = $this->getAccessToken();
            $response = Http::withToken($token)
                ->get($this->baseUrl() . "/v2/payments/captures/{$captureId}");

            if (! $response->ok() || ($response->json('status') !== 'COMPLETED')) {
                return;
            }
        } catch (\Throwable $e) {
            Log::error('PayPal webhook verification failed', ['error' => $e->getMessage()]);
            return;
        }

        $invoice = Invoice::find($invoiceId);
        if ($invoice && $invoice->status !== 'paid') {
            app(InvoiceService::class)->markPaid($invoice);
        }
    }

    public function supportsRefund(): bool { return true; }

    public function refund(Payment $payment, int $amountCents): GatewayResult
    {
        try {
            $token    = $this->getAccessToken();
            $response = Http::withToken($token)
                ->post($this->baseUrl() . "/v2/payments/captures/{$payment->transaction_id}/refund", [
                    'amount' => [
                        'value'         => number_format($amountCents / 100, 2),
                        'currency_code' => $payment->currency_code,
                    ],
                ]);

            if (! $response->ok()) {
                return GatewayResult::failure($response->json('message') ?? 'Refund failed.');
            }

            return GatewayResult::success($response->json('id'), $response->json());

        } catch (\Throwable $e) {
            return GatewayResult::failure($e->getMessage());
        }
    }

    // ── PayPal API helpers ────────────────────────────────────────────────────

    private function baseUrl(): string
    {
        return GatewayRegistry::config('paypal', 'sandbox')
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    private function getAccessToken(): string
    {
        $response = Http::withBasicAuth(
            GatewayRegistry::config('paypal', 'client_id'),
            GatewayRegistry::config('paypal', 'client_secret'),
        )
        ->asForm()
        ->post($this->baseUrl() . '/v1/oauth2/token', [
            'grant_type' => 'client_credentials',
        ]);

        if (! $response->ok()) {
            throw new \RuntimeException('PayPal authentication failed: ' . $response->body());
        }

        return $response->json('access_token');
    }

    private function createOrder(string $token, Invoice $invoice): string
    {
        $response = Http::withToken($token)
            ->post($this->baseUrl() . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $invoice->invoice_number,
                    'custom_id'    => (string) $invoice->id,
                    'description'  => 'Invoice ' . $invoice->invoice_number,
                    'amount'       => [
                        'currency_code' => strtoupper($invoice->currency_code),
                        'value'         => number_format($invoice->amount_due / 100, 2),
                    ],
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                            'user_action'               => 'PAY_NOW',
                        ],
                    ],
                ],
            ]);

        if (! $response->ok()) {
            throw new \RuntimeException('Failed to create PayPal order: ' . $response->body());
        }

        return $response->json('id');
    }

    private function captureOrder(string $token, string $orderId): array
    {
        $response = Http::withToken($token)
            ->post($this->baseUrl() . "/v2/checkout/orders/{$orderId}/capture");

        if (! $response->ok()) {
            throw new \RuntimeException('PayPal capture failed: ' . $response->body());
        }

        return $response->json();
    }
}
