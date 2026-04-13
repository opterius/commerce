<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\StripeService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = auth('client')->user()
            ->invoices()
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(config('commerce.pagination', 25))->withQueryString();

        return view('client.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        abort_if($invoice->client_id !== auth('client')->id(), 403);

        $invoice->load(['items', 'payments']);

        return view('client.invoices.show', compact('invoice'));
    }

    public function pay(Invoice $invoice)
    {
        abort_if($invoice->client_id !== auth('client')->id(), 403);

        if ($invoice->amount_due <= 0) {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('info', 'This invoice has already been paid.');
        }

        $client = auth('client')->user();

        $paymentIntent = app(StripeService::class)->createPaymentIntent($invoice);

        $savedMethods = $client->paymentMethods()->get();

        return view('client.invoices.pay', compact('invoice', 'paymentIntent', 'savedMethods'));
    }

    public function processPayment(Request $request, Invoice $invoice)
    {
        abort_if($invoice->client_id !== auth('client')->id(), 403);

        $request->validate([
            'payment_intent_id' => ['required', 'string'],
        ]);

        $client = auth('client')->user();

        // Retrieve the PaymentIntent from Stripe to verify status
        $stripe = app(StripeService::class);
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $pi = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

        if ($pi->status !== 'succeeded') {
            return back()->with('error', 'Payment not completed. Please try again.');
        }

        // Record payment
        app(InvoiceService::class)->recordStripePayment($invoice, $pi);

        // Optionally save payment method
        if ($request->boolean('save_method') && $pi->payment_method) {
            try {
                $stripe->savePaymentMethod($client, $pi->payment_method);
            } catch (\Exception $e) {
                // non-fatal — card still charged
            }
        }

        return redirect()->route('client.invoices.show', $invoice)
            ->with('success', 'Payment successful. Thank you!');
    }
}
