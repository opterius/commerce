<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServiceUpgradeRequest;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class ServiceUpgradeController extends AdminController
{
    public function index(Request $request)
    {
        $this->authorize('services.manage');

        $status = $request->get('status', 'pending');

        $upgradeRequests = ServiceUpgradeRequest::with([
            'client',
            'service',
            'fromProduct',
            'toProduct',
            'invoice',
            'processedBy',
        ])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(config('commerce.pagination', 25))
            ->withQueryString();

        return view('admin.service-upgrades.index', compact('upgradeRequests', 'status'));
    }

    public function approve(Request $request, ServiceUpgradeRequest $upgradeRequest)
    {
        $this->authorize('services.manage');

        if ($upgradeRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $upgradeRequest->processed_by = auth('staff')->id();

        app(InvoiceService::class)->applyUpgrade($upgradeRequest);

        return redirect()->route('admin.service-upgrades.index')
            ->with('success', 'Upgrade request approved and applied.');
    }

    public function reject(Request $request, ServiceUpgradeRequest $upgradeRequest)
    {
        $this->authorize('services.manage');

        if ($upgradeRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $upgradeRequest->update([
            'status'       => 'rejected',
            'notes'        => $request->input('notes'),
            'processed_by' => auth('staff')->id(),
            'processed_at' => now(),
        ]);

        return redirect()->route('admin.service-upgrades.index')
            ->with('success', 'Upgrade request rejected.');
    }
}
