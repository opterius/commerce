<?php

namespace App\Http\Controllers\Client;

use App\Gateways\GatewayRegistry;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private GatewayRegistry $gateways,
        private InvoiceService  $invoiceService,
    ) {}

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

    public function pay(Invoice $invoice, ?string $gatewaySlug = null)
    {
        abort_if($invoice->client_id !== auth('client')->id(), 403);

        if ($invoice->amount_due <= 0) {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('info', __('invoices.already_paid'));
        }

        $gateways = $this->gateways->active();

        if (empty($gateways)) {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('error', __('invoices.no_gateways'));
        }

        // Resolve which gateway to show
        $activeGateway = $gatewaySlug && isset($gateways[$gatewaySlug])
            ? $gateways[$gatewaySlug]
            : array_values($gateways)[0];

        // Redirect-based gateways go straight to external URL
        if ($activeGateway->supportsRedirect()) {
            return redirect()->away($activeGateway->redirectUrl($invoice));
        }

        $invoice->loadMissing('client');
        $gatewayData = $activeGateway->prepareData($invoice);

        return view('client.invoices.pay', compact('invoice', 'gateways', 'activeGateway', 'gatewayData'));
    }

    public function processPayment(Request $request, Invoice $invoice, string $gatewaySlug)
    {
        abort_if($invoice->client_id !== auth('client')->id(), 403);

        $gateway = $this->gateways->get($gatewaySlug);
        $invoice->loadMissing('client');

        $result = $gateway->charge($invoice, $request);

        if ($result->isRedirect()) {
            return redirect()->away($result->redirectUrl);
        }

        if (! $result->success) {
            return back()->with('error', $result->error ?? __('invoices.payment_failed'));
        }

        // For gateways with automatic confirmation, record payment and mark paid
        if ($gateway->slug() !== 'bank_transfer') {
            $this->invoiceService->recordGatewayPayment($invoice, $gateway->slug(), $result);
        }

        return redirect()->route('client.invoices.show', $invoice)
            ->with('success', $gateway->slug() === 'bank_transfer'
                ? __('invoices.bank_transfer_received')
                : __('invoices.payment_success'));
    }
}
