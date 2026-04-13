# Payment Gateway Module System

Opterius Commerce uses a driver-based gateway system. Each gateway is a standalone class implementing a common interface. Built-in gateways are registered automatically. Third-party gateways register via a Laravel Service Provider — no core files are modified.

---

## Architecture

```
app/Gateways/
    Contracts/
        PaymentGatewayModule.php   ← Interface (the contract)
        GatewayResult.php          ← Return value object
    Modules/
        StripeModule.php           ← Built-in
        BankTransferModule.php     ← Built-in
    GatewayRegistry.php            ← Singleton — holds all registered gateways
app/Providers/
    GatewayServiceProvider.php     ← Registers built-ins + config-defined gateways
resources/views/gateways/
    stripe/form.blade.php          ← Stripe inline payment form
    bank_transfer/form.blade.php   ← Bank transfer instructions form
```

---

## How to Build a Third-Party Gateway Module

### 1. Implement the interface

```php
<?php

namespace MyVendor\MollieGateway;

use App\Gateways\Contracts\GatewayResult;
use App\Gateways\Contracts\PaymentGatewayModule;
use App\Gateways\GatewayRegistry;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

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
                'help'     => 'Found in your Mollie dashboard under Developers → API keys',
            ],
            [
                'key'      => 'test_mode',
                'label'    => 'Test Mode',
                'type'     => 'toggle',
                'required' => false,
            ],
        ];
    }

    public function isConfigured(): bool
    {
        return filled(GatewayRegistry::config('mollie', 'api_key'));
    }

    // Mollie uses a redirect flow
    public function supportsRedirect(): bool { return true; }

    public function prepareData(Invoice $invoice): array { return []; }

    public function formView(): string { return ''; }

    public function redirectUrl(Invoice $invoice): string
    {
        // Create a Mollie payment and return the checkout URL
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey(GatewayRegistry::config('mollie', 'api_key'));

        $payment = $mollie->payments->create([
            'amount'      => ['currency' => $invoice->currency_code, 'value' => number_format($invoice->amount_due / 100, 2)],
            'description' => 'Invoice ' . $invoice->invoice_number,
            'redirectUrl' => url('/client/invoices/' . $invoice->id),
            'webhookUrl'  => url('/webhooks/mollie'),
            'metadata'    => ['invoice_id' => $invoice->id],
        ]);

        return $payment->getCheckoutUrl();
    }

    public function charge(Invoice $invoice, Request $request): GatewayResult
    {
        // For redirect gateways, this is called on return from the payment page
        // Verify the payment status via webhook instead (see handleWebhook)
        return GatewayResult::success('pending');
    }

    public function handleWebhook(Request $request): void
    {
        $mollie    = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey(GatewayRegistry::config('mollie', 'api_key'));
        $payment   = $mollie->payments->get($request->input('id'));

        if ($payment->isPaid()) {
            $invoiceId = $payment->metadata->invoice_id ?? null;
            if ($invoiceId) {
                $invoice = \App\Models\Invoice::find($invoiceId);
                if ($invoice && $invoice->status !== 'paid') {
                    app(\App\Services\InvoiceService::class)->markPaid($invoice);
                }
            }
        }
    }

    public function supportsRefund(): bool { return true; }

    public function refund(Payment $payment, int $amountCents): GatewayResult
    {
        try {
            $mollie = new \Mollie\Api\MollieApiClient();
            $mollie->setApiKey(GatewayRegistry::config('mollie', 'api_key'));
            $molliePayment = $mollie->payments->get($payment->transaction_id);
            $refund = $molliePayment->refund([
                'amount' => ['currency' => $payment->currency_code, 'value' => number_format($amountCents / 100, 2)],
            ]);
            return GatewayResult::success($refund->id);
        } catch (\Throwable $e) {
            return GatewayResult::failure($e->getMessage());
        }
    }
}
```

### 2. Create a Service Provider

```php
<?php

namespace MyVendor\MollieGateway;

use App\Gateways\GatewayRegistry;
use Illuminate\Support\ServiceProvider;

class MollieServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        app(GatewayRegistry::class)->register(MollieModule::class);
    }
}
```

### 3. Register the provider

In `bootstrap/providers.php` of the Commerce installation:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\GatewayServiceProvider::class,
    MyVendor\MollieGateway\MollieServiceProvider::class, // ← add this
];
```

Or alternatively, add the class to `config/commerce.php`:

```php
'gateway_modules' => [
    \MyVendor\MollieGateway\MollieModule::class,
],
```

The config approach requires no Service Provider and is simpler for single-gateway packages.

---

## Interface Reference

### `settingsFields()` field types

| `type`     | Rendered as                        |
|------------|------------------------------------|
| `text`     | `<input type="text">`              |
| `password` | `<input type="password">`          |
| `toggle`   | `<input type="checkbox">`          |
| `textarea` | `<textarea>`                       |
| `select`   | `<select>` — requires `options` key |

All fields are stored in the `settings` table under the key `gateway_{slug}_{field_key}`.

Read them anywhere with:

```php
GatewayRegistry::config('your_slug', 'field_key');
// or:
\App\Models\Setting::get('gateway_your_slug_field_key');
```

### `GatewayResult` factory methods

```php
GatewayResult::success(string $transactionId, array $response = [])
GatewayResult::redirect(string $url)
GatewayResult::failure(string $error, array $response = [])
```

---

## Payment Flow

### Inline gateway (e.g. Stripe)

```
Client → GET /client/invoices/{id}/pay/{slug}
       → InvoiceController::pay() calls prepareData()
       → Renders pay.blade.php with @include($gateway->formView())
       → Client submits form
       → POST /client/invoices/{id}/pay/{slug}
       → InvoiceController::processPayment() calls charge()
       → On success: InvoiceService::recordGatewayPayment() → markPaid()
```

### Redirect gateway (e.g. Mollie, PayPal)

```
Client → GET /client/invoices/{id}/pay/{slug}
       → InvoiceController::pay() calls redirectUrl()
       → redirect()->away(...)
       → Client completes payment on external page
       → Gateway sends webhook to POST /webhooks/{slug}
       → GatewayWebhookController → handleWebhook()
       → InvoiceService::markPaid()
```

---

## Webhook URL

Each gateway automatically gets a webhook endpoint:

```
POST /webhooks/{slug}
```

Examples:
- `POST /webhooks/stripe`
- `POST /webhooks/mollie`
- `POST /webhooks/paypal`

CSRF verification is disabled for these routes. Authenticate webhook calls using the gateway's own signature verification (see `handleWebhook()`).

---

## Admin Settings

Gateway credentials and enable/disable toggles are managed at:

```
Admin → Settings → Payment Gateways
```

The settings form is **auto-generated** from `settingsFields()` — no view files needed for new gateways.

---

## Built-in Gateways

| Gateway       | Flow     | Refunds | Notes |
|---------------|----------|---------|-------|
| Stripe        | Inline   | Yes     | Stripe Elements, saved cards, webhooks |
| PayPal        | Inline   | Yes     | Smart Buttons (PayPal JS SDK), Orders API v2, webhooks |
| Bank Transfer | Inline   | No      | Shows bank details + reference number; admin confirms manually |

### PayPal setup

1. Create an app at [developer.paypal.com](https://developer.paypal.com) → My Apps & Credentials
2. Copy **Client ID** and **Client Secret** into Admin → Settings → Payment Gateways → PayPal
3. Register the webhook URL `POST /webhooks/paypal` in your PayPal app, subscribe to `PAYMENT.CAPTURE.COMPLETED`
4. Enable sandbox mode for testing

### Bank Transfer setup

Fill in your bank account details under Admin → Settings → Payment Gateways → Bank Transfer. The client sees these details on the payment page along with the invoice number as the payment reference. After they submit, a pending payment is recorded and the invoice stays unpaid until you confirm manually via Admin → Invoices → Record Payment.
