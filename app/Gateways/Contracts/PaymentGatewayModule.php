<?php

namespace App\Gateways\Contracts;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGatewayModule
{
    /**
     * Human-readable name shown in the admin and client UI.
     * e.g. "Stripe", "PayPal", "Bank Transfer"
     */
    public function name(): string;

    /**
     * Unique identifier used in routes, settings keys, and DB records.
     * Lowercase, underscores only. e.g. "stripe", "bank_transfer"
     */
    public function slug(): string;

    /**
     * Settings fields this gateway needs — shown as an auto-generated form in admin.
     *
     * Each entry:
     *   'key'      => string   — stored as gateway_{slug}_{key} in settings
     *   'label'    => string
     *   'type'     => 'text' | 'password' | 'toggle' | 'select' | 'textarea'
     *   'required' => bool     — isConfigured() checks these
     *   'help'     => string   — optional hint shown below the field
     *   'options'  => array    — only for type=select, ['value' => 'Label']
     */
    public function settingsFields(): array;

    /**
     * Returns true if all required settings fields have values.
     */
    public function isConfigured(): bool;

    // ── Payment flow ──────────────────────────────────────────────────────────

    /**
     * Whether this gateway redirects the client to an external payment page.
     * If true: redirectUrl() is called instead of rendering a local form.
     * If false: the gateway's Blade view is rendered inline.
     */
    public function supportsRedirect(): bool;

    /**
     * Data passed to the gateway's Blade view before rendering the pay form.
     * Only called when supportsRedirect() === false.
     * Typically creates a payment intent / session token here.
     */
    public function prepareData(Invoice $invoice): array;

    /**
     * The Blade view name for the inline payment form.
     * e.g. 'gateways.stripe.form'
     * Only used when supportsRedirect() === false.
     */
    public function formView(): string;

    /**
     * Return the external URL to redirect the client to for payment.
     * Only called when supportsRedirect() === true.
     */
    public function redirectUrl(Invoice $invoice): string;

    /**
     * Handle the payment submission / gateway return.
     * For inline gateways: called on form POST.
     * For redirect gateways: called when the client returns from the gateway.
     */
    public function charge(Invoice $invoice, Request $request): GatewayResult;

    // ── Webhooks ──────────────────────────────────────────────────────────────

    /**
     * Handle an incoming webhook from the gateway.
     * The route POST /webhooks/{slug} is registered automatically.
     */
    public function handleWebhook(Request $request): void;

    // ── Refunds ───────────────────────────────────────────────────────────────

    public function supportsRefund(): bool;

    public function refund(Payment $payment, int $amountCents): GatewayResult;
}
