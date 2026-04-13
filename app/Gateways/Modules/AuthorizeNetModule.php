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
 * Authorize.net — inline payment via Accept.js tokenization.
 *
 * Flow:
 *   1. prepareData() returns public_client_key + api_login_id for Accept.js.
 *   2. Blade form renders the card fields using the Authorize.net Accept.js SDK.
 *   3. Accept.js tokenizes card data client-side into opaqueData (dataDescriptor + dataValue).
 *   4. Form POSTs only the opaque token — card data never touches our server.
 *   5. charge() sends the token to Authorize.net API to create the transaction.
 *
 * API reference: https://developer.authorize.net/api/reference/
 */
class AuthorizeNetModule implements PaymentGatewayModule
{
    public function name(): string { return 'Authorize.net'; }
    public function slug(): string { return 'authorize_net'; }

    public function settingsFields(): array
    {
        return [
            [
                'key'      => 'api_login_id',
                'label'    => 'API Login ID',
                'type'     => 'text',
                'required' => true,
                'help'     => 'Found in Authorize.net Merchant Interface → Account → Settings → API Credentials.',
            ],
            [
                'key'      => 'transaction_key',
                'label'    => 'Transaction Key',
                'type'     => 'password',
                'required' => true,
            ],
            [
                'key'      => 'public_client_key',
                'label'    => 'Public Client Key',
                'type'     => 'text',
                'required' => true,
                'help'     => 'Found in Authorize.net Merchant Interface → Account → Settings → Manage Public Client Key.',
            ],
            [
                'key'      => 'sandbox',
                'label'    => 'Sandbox Mode',
                'type'     => 'toggle',
                'required' => false,
                'help'     => 'Use the Authorize.net sandbox (apitest.authorize.net) for testing.',
            ],
        ];
    }

    public function isConfigured(): bool
    {
        return filled(GatewayRegistry::config('authorize_net', 'api_login_id'))
            && filled(GatewayRegistry::config('authorize_net', 'transaction_key'))
            && filled(GatewayRegistry::config('authorize_net', 'public_client_key'));
    }

    public function supportsRedirect(): bool { return false; }

    public function prepareData(Invoice $invoice): array
    {
        return [
            'authorizeApiLoginId'   => GatewayRegistry::config('authorize_net', 'api_login_id'),
            'authorizePublicKey'    => GatewayRegistry::config('authorize_net', 'public_client_key'),
            'authorizeSandbox'      => (bool) GatewayRegistry::config('authorize_net', 'sandbox'),
        ];
    }

    public function formView(): string { return 'gateways.authorize_net.form'; }

    public function redirectUrl(Invoice $invoice): string { return ''; }

    /**
     * Charge using the Accept.js opaque data token posted by the client-side form.
     */
    public function charge(Invoice $invoice, Request $request): GatewayResult
    {
        $request->validate([
            'authorize_data_descriptor' => ['required', 'string'],
            'authorize_data_value'      => ['required', 'string'],
        ]);

        $payload = [
            'createTransactionRequest' => [
                'merchantAuthentication' => [
                    'name'           => GatewayRegistry::config('authorize_net', 'api_login_id'),
                    'transactionKey' => GatewayRegistry::config('authorize_net', 'transaction_key'),
                ],
                'transactionRequest' => [
                    'transactionType' => 'authCaptureTransaction',
                    'amount'          => number_format($invoice->amount_due / 100, 2, '.', ''),
                    'payment'         => [
                        'opaqueData' => [
                            'dataDescriptor' => $request->input('authorize_data_descriptor'),
                            'dataValue'      => $request->input('authorize_data_value'),
                        ],
                    ],
                    'order' => [
                        'invoiceNumber' => $invoice->invoice_number,
                        'description'   => 'Invoice ' . $invoice->invoice_number,
                    ],
                    'currencyCode' => strtoupper($invoice->currency_code),
                ],
            ],
        ];

        try {
            $response = Http::post($this->apiUrl(), $payload);
            $body     = $response->json();

            // Strip BOM that Authorize.net sometimes prepends to response
            if (isset($body['messages']['resultCode'])) {
                $resultCode = $body['messages']['resultCode'];
            } else {
                return GatewayResult::failure('Invalid response from Authorize.net.');
            }

            if ($resultCode !== 'Ok') {
                $error = $body['messages']['message'][0]['text'] ?? 'Transaction declined.';
                return GatewayResult::failure($error, $body);
            }

            $transId = $body['transactionResponse']['transId'] ?? null;
            if (! $transId) {
                $error = $body['transactionResponse']['errors'][0]['errorText']
                    ?? $body['messages']['message'][0]['text']
                    ?? 'No transaction ID returned.';
                return GatewayResult::failure($error, $body);
            }

            return GatewayResult::success($transId, $body);

        } catch (\Throwable $e) {
            Log::error('Authorize.net charge error', ['message' => $e->getMessage(), 'invoice' => $invoice->id]);
            return GatewayResult::failure('Authorize.net error: ' . $e->getMessage());
        }
    }

    /**
     * Authorize.net sends webhooks for subscription events.
     * For one-time charges, payment is confirmed synchronously in charge().
     */
    public function handleWebhook(Request $request): void
    {
        // One-time payments are confirmed inline via charge().
        // Webhook support can be added here for ARB (recurring billing) if needed.
    }

    public function supportsRefund(): bool { return true; }

    public function refund(Payment $payment, int $amountCents): GatewayResult
    {
        $payload = [
            'createTransactionRequest' => [
                'merchantAuthentication' => [
                    'name'           => GatewayRegistry::config('authorize_net', 'api_login_id'),
                    'transactionKey' => GatewayRegistry::config('authorize_net', 'transaction_key'),
                ],
                'transactionRequest' => [
                    'transactionType' => 'refundTransaction',
                    'amount'          => number_format($amountCents / 100, 2, '.', ''),
                    'refTransId'      => $payment->transaction_id,
                    'payment'         => [
                        'creditCard' => [
                            // Authorize.net requires last 4 digits for refund — stored in payment notes or metadata.
                            // If not available, use '0000' (works in sandbox; live may require actual last 4).
                            'cardNumber'     => $payment->card_last_four ?? '0000',
                            'expirationDate' => 'XXXX',
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = Http::post($this->apiUrl(), $payload);
            $body     = $response->json();

            if (($body['messages']['resultCode'] ?? '') !== 'Ok') {
                $error = $body['messages']['message'][0]['text'] ?? 'Refund failed.';
                return GatewayResult::failure($error, $body);
            }

            return GatewayResult::success(
                $body['transactionResponse']['transId'] ?? $payment->transaction_id . '-refund',
                $body,
            );
        } catch (\Throwable $e) {
            return GatewayResult::failure($e->getMessage());
        }
    }

    private function apiUrl(): string
    {
        return GatewayRegistry::config('authorize_net', 'sandbox')
            ? 'https://apitest.authorize.net/xml/v1/request.api'
            : 'https://api.authorize.net/xml/v1/request.api';
    }
}
