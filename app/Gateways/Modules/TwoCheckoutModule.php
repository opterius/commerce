<?php

namespace App\Gateways\Modules;

use App\Gateways\Contracts\GatewayResult;
use App\Gateways\Contracts\PaymentGatewayModule;
use App\Gateways\GatewayRegistry;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 2Checkout (Verifone) — hosted checkout redirect flow.
 *
 * Flow:
 *   1. prepareData() generates a signed redirect URL.
 *   2. Client is sent to 2Checkout hosted page.
 *   3. After payment, 2Checkout POSTs an IPN to /webhooks/twocheckout.
 *   4. handleWebhook() verifies the MD5 hash and records payment.
 *
 * API reference: https://verifone.cloud/docs/2checkout
 */
class TwoCheckoutModule implements PaymentGatewayModule
{
    public function name(): string { return '2Checkout'; }
    public function slug(): string { return 'twocheckout'; }

    public function settingsFields(): array
    {
        return [
            [
                'key'      => 'account_number',
                'label'    => 'Seller ID (Account Number)',
                'type'     => 'text',
                'required' => true,
                'help'     => 'Your numeric 2Checkout seller ID, found in your dashboard.',
            ],
            [
                'key'      => 'secret_key',
                'label'    => 'Secret Key',
                'type'     => 'password',
                'required' => true,
                'help'     => 'Found in 2Checkout Dashboard → Integrations → Webhooks & API.',
            ],
            [
                'key'      => 'sandbox',
                'label'    => 'Sandbox / Demo Mode',
                'type'     => 'toggle',
                'required' => false,
                'help'     => 'Use 2Checkout sandbox (demo.2checkout.com) for testing.',
            ],
        ];
    }

    public function isConfigured(): bool
    {
        return filled(GatewayRegistry::config('twocheckout', 'account_number'))
            && filled(GatewayRegistry::config('twocheckout', 'secret_key'));
    }

    public function supportsRedirect(): bool { return true; }

    public function prepareData(Invoice $invoice): array { return []; }

    public function formView(): string { return ''; }

    public function redirectUrl(Invoice $invoice): string
    {
        $accountNumber = GatewayRegistry::config('twocheckout', 'account_number');
        $secretKey     = GatewayRegistry::config('twocheckout', 'secret_key');
        $sandbox       = (bool) GatewayRegistry::config('twocheckout', 'sandbox');

        $amount    = number_format($invoice->amount_due / 100, 2, '.', '');
        $currency  = strtoupper($invoice->currency_code);
        $invoiceId = (string) $invoice->id;
        $sid       = $accountNumber;

        // Build the HMAC-MD5 signature: uppercase(secret_key + sid + merchant_order_id + total)
        $signature = strtoupper(md5($secretKey . $sid . $invoiceId . $amount));

        $base = $sandbox
            ? 'https://sandbox.2checkout.com/checkout/purchase'
            : 'https://www.2checkout.com/checkout/purchase';

        $params = http_build_query([
            'sid'               => $sid,
            'mode'              => '2CO',
            'li_0_type'         => 'product',
            'li_0_name'         => 'Invoice ' . $invoice->invoice_number,
            'li_0_quantity'     => '1',
            'li_0_price'        => $amount,
            'currency_code'     => $currency,
            'merchant_order_id' => $invoiceId,
            'x_receipt_link_url' => url('/client/invoices/' . $invoiceId),
            'card_holder_name'  => $invoice->client->full_name ?? '',
            'email'             => $invoice->client->email ?? '',
            '2co_order_id'      => '',
            'key'               => $signature,
        ]);

        return $base . '?' . $params;
    }

    public function charge(Invoice $invoice, Request $request): GatewayResult
    {
        // Redirect gateway — confirmed via IPN webhook
        return GatewayResult::success('pending');
    }

    /**
     * 2Checkout IPN verification.
     * The IPN signature is: uppercase(MD5(secret_key + seller_id + order_number + invoice_id))
     * where order_number is the 2CO order number.
     */
    public function handleWebhook(Request $request): void
    {
        $secretKey     = GatewayRegistry::config('twocheckout', 'secret_key');
        $accountNumber = GatewayRegistry::config('twocheckout', 'account_number');

        $orderNumber = $request->input('order_number') ?? $request->input('sale_id');
        $invoiceId   = $request->input('merchant_order_id');
        $key         = $request->input('key');
        $messageType = strtoupper($request->input('message_type', ''));

        if (! $orderNumber || ! $invoiceId || ! $key) return;

        // Only process completed orders
        if (! in_array($messageType, ['ORDER_CREATED', 'FRAUD_STATUS_CHANGED', 'SHIP_STATUS_CHANGED', ''])) {
            return;
        }

        $invoiceTotal = $request->input('invoice_id') ?? '';
        $expected = strtoupper(md5($secretKey . $accountNumber . $orderNumber . $invoiceTotal));

        if (! hash_equals($expected, strtoupper($key))) {
            Log::warning('2Checkout IPN signature mismatch', ['invoice_id' => $invoiceId]);
            return;
        }

        $invoice = Invoice::find($invoiceId);
        if ($invoice && $invoice->status !== 'paid') {
            app(\App\Services\InvoiceService::class)->recordGatewayPayment(
                $invoice,
                'twocheckout',
                GatewayResult::success($orderNumber, $request->all()),
            );
        }
    }

    public function supportsRefund(): bool { return true; }

    /**
     * 2Checkout refund via API 6.0 (REST).
     */
    public function refund(Payment $payment, int $amountCents): GatewayResult
    {
        try {
            $accountNumber = GatewayRegistry::config('twocheckout', 'account_number');
            $secretKey     = GatewayRegistry::config('twocheckout', 'secret_key');
            $sandbox       = (bool) GatewayRegistry::config('twocheckout', 'sandbox');

            // Generate JWT-style HMAC for API auth
            $date      = gmdate('Y-m-d H:i:s');
            $string    = strlen($accountNumber) . $accountNumber . strlen($date) . $date;
            $hash      = hash_hmac('md5', $string, $secretKey);
            $token     = base64_encode("{$accountNumber}|{$date}|{$hash}");

            $base = $sandbox
                ? 'https://api.sandbox.2checkout.com'
                : 'https://api.2checkout.com';

            $response = Http::withHeaders(['X-Avangate-Authentication' => "code=\"{$accountNumber}\" date=\"{$date}\" hash=\"{$hash}\""])
                ->post("{$base}/rest/6.0/orders/{$payment->transaction_id}/refund/", [
                    'amount'  => number_format($amountCents / 100, 2, '.', ''),
                    'comment' => 'Refund issued via Opterius Commerce',
                ]);

            if (! $response->ok()) {
                return GatewayResult::failure($response->json('message') ?? 'Refund failed.');
            }

            return GatewayResult::success($payment->transaction_id . '-refund', $response->json());
        } catch (\Throwable $e) {
            return GatewayResult::failure($e->getMessage());
        }
    }
}
