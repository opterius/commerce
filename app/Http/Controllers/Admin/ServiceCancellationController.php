<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use App\Models\ServiceCancellationRequest;
use Illuminate\Http\Request;

class ServiceCancellationController extends AdminController
{
    public function index(Request $request)
    {
        $this->authorize('services.manage');

        $status = $request->get('status', 'pending');

        $requests = ServiceCancellationRequest::with(['service', 'client', 'processedBy'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(config('commerce.pagination', 25))
            ->withQueryString();

        return view('admin.service-cancellations.index', compact('requests', 'status'));
    }

    public function approve(Request $request, ServiceCancellationRequest $cancellationRequest)
    {
        $this->authorize('services.manage');

        if ($cancellationRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $service = $cancellationRequest->service;

        if ($cancellationRequest->cancel_type === 'immediate') {
            $service->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);
        } else {
            // End of billing period: mark for cancellation at next renewal
            // Service continues until next_due_date, then cancels
            $service->update(['status' => 'cancelled', 'cancelled_at' => $service->next_due_date ?? now()]);
        }

        $cancellationRequest->update([
            'status'       => 'approved',
            'admin_notes'  => $request->input('admin_notes'),
            'processed_by' => auth('staff')->id(),
            'processed_at' => now(),
        ]);

        return redirect()->route('admin.service-cancellations.index')
            ->with('success', 'Cancellation request approved. Service has been cancelled.');
    }

    public function reject(Request $request, ServiceCancellationRequest $cancellationRequest)
    {
        $this->authorize('services.manage');

        if ($cancellationRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $cancellationRequest->update([
            'status'       => 'rejected',
            'admin_notes'  => $request->input('admin_notes'),
            'processed_by' => auth('staff')->id(),
            'processed_at' => now(),
        ]);

        return redirect()->route('admin.service-cancellations.index')
            ->with('success', 'Cancellation request rejected.');
    }
}
