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

class MollieModule implements PaymentGatewayModule
{
    public function name(): string { return 'Mollie'; }
    public function slug(): string { return 'mollie'; }

    public function settingsFields(): array
    {
        return [
            [
                'key'      => 'api_key',
                'label'    => 'API Key',
                'type'     => 'password',
                'required' => true,
                'help'     => 'Use a test key (test_…) for testing or a live key (live_…) for production. Found in Mollie Dashboard → Developers → API keys.',
            ],
        ];
    }

    public function isConfigured(): bool
    {
        return filled(GatewayRegistry::config('mollie', 'api_key'));
    }

    public function supportsRedirect(): bool { return true; }

    public function prepareData(Invoice $invoice): array { return []; }

    public function formView(): string { return ''; }

    public function redirectUrl(Invoice $invoice): string
    {
        $response = Http::withToken(GatewayRegistry::config('mollie', 'api_key'))
            ->post('https://api.mollie.com/v2/payments', [
                'amount'      => [
                    'currency' => strtoupper($invoice->currency_code),
                    'value'    => number_format($invoice->amount_due / 100, 2, '.', ''),
                ],
                'description' => 'Invoice ' . $invoice->invoice_number,
                'redirectUrl' => route('client.invoices.show', $invoice->id),
                'webhookUrl'  => url('/webhooks/mollie'),
                'metadata'    => ['invoice_id' => (string) $invoice->id],
            ]);

        if (! $response->ok()) {
            throw new \RuntimeException('Mollie payment creation failed: ' . $response->body());
        }

        return $response->json('_links.checkout.href');
    }

    public function charge(Invoice $invoice, Request $request): GatewayResult
    {
        // Redirect gateway — payment is confirmed via webhook, not via charge()
        return GatewayResult::success('pending');
    }

    /**
     * Mollie sends a POST with a single 'id' field containing the payment ID.
     * We verify by re-fetching the payment from Mollie's API.
     */
    public function handleWebhook(Request $request): void
    {
        $paymentId = $request->input('id');
        if (! $paymentId) return;

        try {
            $response = Http::withToken(GatewayRegistry::config('mollie', 'api_key'))
                ->get("https://api.mollie.com/v2/payments/{$paymentId}");

            if (! $response->ok()) return;

            $payment   = $response->json();
            $status    = $payment['status'] ?? '';
            $invoiceId = $payment['metadata']['invoice_id'] ?? null;

            if ($status !== 'paid' || ! $invoiceId) return;

            $invoice = Invoice::find($invoiceId);
            if ($invoice && $invoice->status !== 'paid') {
                app(InvoiceService::class)->recordGatewayPayment(
                    $invoice,
                    'mollie',
                    GatewayResult::success($paymentId, $payment),
                );
            }
        } catch (\Throwable $e) {
            Log::error('Mollie webhook error', ['error' => $e->getMessage()]);
        }
    }

    public function supportsRefund(): bool { return true; }

    public function refund(Payment $payment, int $amountCents): GatewayResult
    {
        try {
            $response = Http::withToken(GatewayRegistry::config('mollie', 'api_key'))
                ->post("https://api.mollie.com/v2/payments/{$payment->transaction_id}/refunds", [
                    'amount' => [
                        'currency' => strtoupper($payment->currency_code),
                        'value'    => number_format($amountCents / 100, 2, '.', ''),
                    ],
                ]);

            if (! $response->ok()) {
                return GatewayResult::failure($response->json('detail') ?? 'Refund failed.');
            }

            return GatewayResult::success($response->json('id'), $response->json());
        } catch (\Throwable $e) {
            return GatewayResult::failure($e->getMessage());
        }
    }
}
