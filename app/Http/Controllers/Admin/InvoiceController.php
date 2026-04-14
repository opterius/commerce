<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\ActivityLogger;
use App\Services\InvoicePdfService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['client'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('client', fn ($q) =>
                $q->where('email', 'like', '%' . $request->search . '%')
                  ->orWhere('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('company_name', 'like', '%' . $request->search . '%')
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->paginate(config('commerce.pagination', 25))->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    public function downloadPdf(Invoice $invoice, InvoicePdfService $pdf)
    {
        return $pdf->download($invoice);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items.service', 'payments', 'creditNotes.createdBy']);

        $creditBalance = $invoice->client->credits()->sum('amount');

        return view('admin.invoices.show', compact('invoice', 'creditBalance'));
    }

    public function void(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'Paid invoices cannot be voided.');
        }

        $invoice->update(['status' => 'cancelled']);

        ActivityLogger::log('invoice.voided', 'invoice', $invoice->id, $invoice->invoice_number);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice voided.');
    }

    public function send(Invoice $invoice)
    {
        $invoice->update(['sent_at' => now()]);

        ActivityLogger::log('invoice.sent', 'invoice', $invoice->id, $invoice->invoice_number);

        return redirect()->back()->with('success', 'Invoice marked as sent.');
    }

    public function manualPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'method'         => ['required', 'in:bank_transfer,cash,check,other'],
            'notes'          => ['nullable', 'string'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
        ]);

        $cents = (int) round($request->amount * 100);

        app(InvoiceService::class)->recordManualPayment($invoice, [
            'amount'         => $cents,
            'method'         => $request->method,
            'notes'          => $request->notes,
            'transaction_id' => $request->transaction_id,
        ], auth()->user());

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Payment recorded.');
    }

    public function applyCredit(Request $request, Invoice $invoice)
    {
        $request->validate(['amount' => ['required', 'numeric', 'min:0.01']]);

        $cents = (int) round($request->amount * 100);

        app(InvoiceService::class)->applyCredit($invoice, $cents);

        return redirect()->back()->with('success', 'Credit applied.');
    }
}
