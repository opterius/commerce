<?php

namespace App\Gateways\Modules;

use App\Gateways\Contracts\GatewayResult;
use App\Gateways\Contracts\PaymentGatewayModule;
use App\Gateways\GatewayRegistry;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class BankTransferModule implements PaymentGatewayModule
{
    public function name(): string { return 'Bank Transfer'; }
    public function slug(): string { return 'bank_transfer'; }

    public function settingsFields(): array
    {
        return [
            ['key' => 'bank_name',      'label' => 'Bank Name',       'type' => 'text',     'required' => true],
            ['key' => 'account_name',   'label' => 'Account Name',    'type' => 'text',     'required' => true],
            ['key' => 'account_number', 'label' => 'Account Number',  'type' => 'text',     'required' => true],
            ['key' => 'routing_number', 'label' => 'Routing / SWIFT', 'type' => 'text',     'required' => false],
            ['key' => 'iban',           'label' => 'IBAN',            'type' => 'text',     'required' => false],
            ['key' => 'instructions',   'label' => 'Extra Instructions', 'type' => 'textarea', 'required' => false,
             'help' => 'Shown to the client on the payment page. Use the invoice number as payment reference.'],
        ];
    }

    public function isConfigured(): bool
    {
        return filled(GatewayRegistry::config('bank_transfer', 'bank_name'))
            && filled(GatewayRegistry::config('bank_transfer', 'account_name'))
            && filled(GatewayRegistry::config('bank_transfer', 'account_number'));
    }

    public function supportsRedirect(): bool { return false; }

    public function prepareData(Invoice $invoice): array
    {
        return [
            'bankName'      => GatewayRegistry::config('bank_transfer', 'bank_name'),
            'accountName'   => GatewayRegistry::config('bank_transfer', 'account_name'),
            'accountNumber' => GatewayRegistry::config('bank_transfer', 'account_number'),
            'routingNumber' => GatewayRegistry::config('bank_transfer', 'routing_number'),
            'iban'          => GatewayRegistry::config('bank_transfer', 'iban'),
            'instructions'  => GatewayRegistry::config('bank_transfer', 'instructions'),
        ];
    }

    public function formView(): string { return 'gateways.bank_transfer.form'; }

    public function redirectUrl(Invoice $invoice): string { return ''; }

    /**
     * Bank transfer has no automatic confirmation — client submits to notify us,
     * admin confirms payment manually. We record a pending payment here.
     */
    public function charge(Invoice $invoice, Request $request): GatewayResult
    {
        // Record as pending — admin will confirm via manual payment
        $ref = 'BT-' . strtoupper(substr(md5($invoice->id . time()), 0, 8));

        Payment::create([
            'invoice_id'     => $invoice->id,
            'gateway'        => 'bank_transfer',
            'transaction_id' => $ref,
            'amount'         => $invoice->amount_due,
            'currency_code'  => $invoice->currency_code,
            'status'         => 'pending',
            'method'         => 'bank_transfer',
            'notes'          => 'Awaiting manual confirmation by admin.',
        ]);

        return GatewayResult::success($ref);
    }

    public function handleWebhook(Request $request): void
    {
        // Bank transfer has no automated webhooks
    }

    public function supportsRefund(): bool { return false; }

    public function refund(Payment $payment, int $amountCents): GatewayResult
    {
        return GatewayResult::failure('Bank transfer refunds must be processed manually.');
    }
}
