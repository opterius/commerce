<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, Product, Service};
use App\Services\ProvisioningService;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with(['client', 'product'])
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

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $services = $query->paginate(config('commerce.pagination', 25))->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('admin.services.index', compact('services', 'products'));
    }

    public function show(Service $service)
    {
        $service->load(['client', 'product', 'order', 'orderItem', 'server']);

        $invoices = Invoice::whereHas('items', fn ($q) =>
            $q->where('service_id', $service->id)
        )->latest()->get();

        $provisioningLogs = $service->provisioningLogs()
            ->with('staff')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return view('admin.services.show', compact('service', 'invoices', 'provisioningLogs'));
    }

    public function provisionAction(Request $request, Service $service)
    {
        $request->validate([
            'action' => 'required|in:create,suspend,unsuspend,terminate,info',
        ]);

        $action      = $request->action;
        $triggeredBy = auth('staff')->id();

        app(ProvisioningService::class)->dispatch($service, $action, $triggeredBy);

        ActivityLogger::log(
            "service.provision.{$action}",
            'service',
            $service->id,
            $service->domain ?? "service #{$service->id}",
            "Manual provisioning action: {$action}",
            ['triggered_by' => $triggeredBy]
        );

        return back()->with('success', ucfirst($action) . ' job queued.');
    }
}
