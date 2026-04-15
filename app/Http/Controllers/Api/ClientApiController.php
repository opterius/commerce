<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientApiController extends Controller
{
    private function client(Request $request)
    {
        return $request->get('_api_client');
    }

    // GET /api/v1/me
    public function me(Request $request): JsonResponse
    {
        $client = $this->client($request);
        return response()->json([
            'id'           => $client->id,
            'name'         => $client->full_name,
            'email'        => $client->email,
            'company'      => $client->company_name,
            'phone'        => $client->phone,
            'address'      => $client->address_1,
            'city'         => $client->city,
            'state'        => $client->state,
            'postcode'     => $client->postcode,
            'country_code' => $client->country_code,
            'currency'     => $client->currency_code,
            'status'       => $client->status,
        ]);
    }

    // GET /api/v1/services
    public function services(Request $request): JsonResponse
    {
        $client   = $this->client($request);
        $services = Service::where('client_id', $client->id)
            ->with('product:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'product'        => $s->product?->name,
                'domain'         => $s->domain,
                'status'         => $s->status,
                'billing_cycle'  => $s->billing_cycle,
                'amount'         => $s->amount / 100,
                'currency'       => $s->currency_code,
                'next_due_date'  => $s->next_due_date?->toDateString(),
                'created_at'     => $s->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $services]);
    }

    // GET /api/v1/invoices
    public function invoices(Request $request): JsonResponse
    {
        $client = $this->client($request);
        $query  = Invoice::where('client_id', $client->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderByDesc('created_at')
            ->get()
            ->map(fn($i) => [
                'id'             => $i->id,
                'invoice_number' => $i->invoice_number,
                'status'         => $i->status,
                'total'          => $i->total / 100,
                'amount_due'     => $i->amount_due / 100,
                'currency'       => $i->currency_code,
                'due_date'       => $i->due_date?->toDateString(),
                'paid_date'      => $i->paid_date?->toDateString(),
                'created_at'     => $i->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $invoices]);
    }

    // GET /api/v1/invoices/{id}
    public function invoice(Request $request, int $id): JsonResponse
    {
        $client  = $this->client($request);
        $invoice = Invoice::where('client_id', $client->id)
            ->with('items')
            ->findOrFail($id);

        return response()->json([
            'id'             => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status'         => $invoice->status,
            'currency'       => $invoice->currency_code,
            'subtotal'       => $invoice->subtotal / 100,
            'tax'            => $invoice->tax / 100,
            'credit_applied' => $invoice->credit_applied / 100,
            'total'          => $invoice->total / 100,
            'amount_due'     => $invoice->amount_due / 100,
            'due_date'       => $invoice->due_date?->toDateString(),
            'paid_date'      => $invoice->paid_date?->toDateString(),
            'created_at'     => $invoice->created_at->toIso8601String(),
            'items'          => $invoice->items->map(fn($item) => [
                'description' => $item->description,
                'quantity'    => $item->quantity,
                'amount'      => ($item->amount + $item->tax_amount) / 100,
            ]),
        ]);
    }

    // GET /api/v1/orders
    public function orders(Request $request): JsonResponse
    {
        $client = $this->client($request);
        $orders = Order::where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($o) => [
                'id'         => $o->id,
                'status'     => $o->status,
                'total'      => $o->total / 100,
                'currency'   => $o->currency_code,
                'created_at' => $o->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $orders]);
    }

    // GET /api/v1/domains
    public function domains(Request $request): JsonResponse
    {
        $client  = $this->client($request);
        $domains = Domain::where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($d) => [
                'id'             => $d->id,
                'domain_name'    => $d->domain_name,
                'status'         => $d->status,
                'billing_cycle'  => $d->billing_cycle,
                'amount'         => $d->amount / 100,
                'currency'       => $d->currency_code,
                'auto_renew'     => $d->auto_renew,
                'expires_at'     => $d->expires_at?->toDateString(),
                'created_at'     => $d->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $domains]);
    }
}
