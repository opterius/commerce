<?php

namespace App\Http\Controllers\Admin;

use App\Models\{Invoice, InvoiceItem, Service};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends AdminController
{
    public function index()
    {
        $this->authorize('reports.view');

        // ── 1. MRR (Monthly Recurring Revenue) ───────────────────────────────
        $cycleMultipliers = [
            'monthly'     => 1,
            'quarterly'   => 3,
            'semi_annual' => 6,
            'annual'      => 12,
            'biennial'    => 24,
            'triennial'   => 36,
        ];

        $activeServices = Service::where('status', 'active')->get();

        $mrr = [];
        foreach ($activeServices as $service) {
            $multiplier = $cycleMultipliers[$service->billing_cycle] ?? 1;
            $monthly    = (int) round($service->amount / $multiplier);
            $currency   = $service->currency_code;
            $mrr[$currency] = ($mrr[$currency] ?? 0) + $monthly;
        }

        // ── 2. Revenue last 12 months ─────────────────────────────────────────
        $revenueMonths = [];
        $start = Carbon::now()->startOfMonth()->subMonths(11);

        $rawRevenue = DB::table('payments')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->where('payments.status', 'completed')
            ->whereNotNull('invoices.paid_date')
            ->where('invoices.paid_date', '>=', $start)
            ->selectRaw('YEAR(invoices.paid_date) as yr, MONTH(invoices.paid_date) as mo, SUM(payments.amount) as total')
            ->groupByRaw('YEAR(invoices.paid_date), MONTH(invoices.paid_date)')
            ->get()
            ->keyBy(fn ($row) => $row->yr . '-' . str_pad($row->mo, 2, '0', STR_PAD_LEFT));

        $revenueData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date  = Carbon::now()->startOfMonth()->subMonths($i);
            $key   = $date->format('Y-m');
            $label = $date->format('M Y');
            $revenueData[] = [
                'label'  => $label,
                'amount' => isset($rawRevenue[$key]) ? (int) $rawRevenue[$key]->total : 0,
            ];
        }

        $maxRevenue = max(1, max(array_column($revenueData, 'amount')));

        // ── 3. Upcoming renewals (next 30 days) ───────────────────────────────
        $upcomingRenewals = Service::with(['client', 'product'])
            ->where('status', 'active')
            ->whereBetween('next_due_date', [
                Carbon::today(),
                Carbon::today()->addDays(30),
            ])
            ->orderBy('next_due_date')
            ->get();

        // ── 4. Overdue summary ────────────────────────────────────────────────
        $overdueInvoices = Invoice::where('status', 'overdue')
            ->selectRaw('COUNT(*) as cnt, SUM(total) as total_amount')
            ->first();

        $overdueCount  = (int) ($overdueInvoices->cnt ?? 0);
        $overdueAmount = (int) ($overdueInvoices->total_amount ?? 0);

        // ── 5. Revenue by product (top 10) ────────────────────────────────────
        $revenueByProduct = InvoiceItem::select('service_id', DB::raw('SUM(amount) as total'))
            ->whereHas('invoice', fn ($q) => $q->where('status', 'paid'))
            ->whereNotNull('service_id')
            ->groupBy('service_id')
            ->with('service.product')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'product_name' => $item->service?->product?->name ?? 'Unknown Product',
                'total'        => (int) $item->total,
            ]);

        // ── 6. Total revenue (all time) ───────────────────────────────────────
        $totalRevenue = (int) Invoice::where('status', 'paid')->sum('total');

        // ── 7. Active services count ──────────────────────────────────────────
        $activeServicesCount = Service::where('status', 'active')->count();

        return view('admin.reports.index', compact(
            'mrr',
            'revenueData',
            'maxRevenue',
            'upcomingRenewals',
            'overdueCount',
            'overdueAmount',
            'revenueByProduct',
            'totalRevenue',
            'activeServicesCount',
        ));
    }

    public function exportCsv()
    {
        $this->authorize('reports.view');

        $start = Carbon::now()->startOfMonth()->subMonths(11);

        $rawRevenue = DB::table('payments')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->where('payments.status', 'completed')
            ->whereNotNull('invoices.paid_date')
            ->where('invoices.paid_date', '>=', $start)
            ->selectRaw('YEAR(invoices.paid_date) as yr, MONTH(invoices.paid_date) as mo, SUM(payments.amount) as total')
            ->groupByRaw('YEAR(invoices.paid_date), MONTH(invoices.paid_date)')
            ->get()
            ->keyBy(fn ($row) => $row->yr . '-' . str_pad($row->mo, 2, '0', STR_PAD_LEFT));

        $rows = [];
        for ($i = 11; $i >= 0; $i--) {
            $date  = Carbon::now()->startOfMonth()->subMonths($i);
            $key   = $date->format('Y-m');
            $rows[] = [
                $date->format('M Y'),
                isset($rawRevenue[$key]) ? number_format((int) $rawRevenue[$key]->total / 100, 2, '.', '') : '0.00',
            ];
        }

        $filename = 'revenue-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Month', 'Revenue']);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
