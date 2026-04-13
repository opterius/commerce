<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProvisioningLog;
use App\Models\Service;
use Illuminate\Http\Request;

class ProvisioningLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ProvisioningLog::with(['service.client', 'staff'])
            ->orderByDesc('created_at');

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(config('commerce.pagination', 25))->withQueryString();

        return view('admin.provisioning-log.index', compact('logs'));
    }

    public function show(ProvisioningLog $provisioningLog)
    {
        $provisioningLog->load(['service.client', 'staff']);

        return view('admin.provisioning-log.show', compact('provisioningLog'));
    }
}
