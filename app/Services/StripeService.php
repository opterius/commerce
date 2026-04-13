<?php

namespace App\Services;

use App\Models\{Client, Invoice, PaymentMethod};

class StripeService
{
    public function __construct()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function getOrCreateCustomer(Client $client): string
    {
        if ($client->stripe_customer_id) {
            return $client->stripe_customer_id;
        }

        $customer = \Stripe\Customer::create([
            'email'    => $client->email,
            'name'     => $client->full_name,
            'metadata' => ['commerce_client_id' => $client->id],
        ]);

        $client->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    public function createPaymentIntent(Invoice $invoice, ?string $paymentMethodId = null): array
    {
        $customerId = $this->getOrCreateCustomer($invoice->client);

        $params = [
            'amount'   => $invoice->amount_due,
            'currency' => strtolower($invoice->currency_code),
            'customer' => $customerId,
            'metadata' => ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number],
        ];

        if ($paymentMethodId) {
            $params['payment_method']    = $paymentMethodId;
            $params['confirmation_method'] = 'manual';
        }

        $intent = \Stripe\PaymentIntent::create($params);

        return [
            'client_secret'     => $intent->client_secret,
            'payment_intent_id' => $intent->id,
        ];
    }

    public function savePaymentMethod(Client $client, string $pmId): PaymentMethod
    {
        $customerId = $this->getOrCreateCustomer($client);

        $stripePm = \Stripe\PaymentMethod::retrieve($pmId);
        $stripePm->attach(['customer' => $customerId]);

        $isDefault = !$client->paymentMethods()->exists();

        return PaymentMethod::create([
            'client_id'    => $client->id,
            'stripe_pm_id' => $pmId,
            'brand'        => $stripePm->card->brand,
            'last4'        => $stripePm->card->last4,
            'exp_month'    => $stripePm->card->exp_month,
            'exp_year'     => $stripePm->card->exp_year,
            'is_default'   => $isDefault,
        ]);
    }

    public function deletePaymentMethod(PaymentMethod $pm): void
    {
        $stripePm = \Stripe\PaymentMethod::retrieve($pm->stripe_pm_id);
        $stripePm->detach();
        $pm->delete();
    }
}
