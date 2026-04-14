<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceStatus;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ServiceStatusController extends Controller
{
    public function index()
    {
        $components = ServiceStatus::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.service-statuses.index', compact('components'));
    }

    public function create()
    {
        return view('admin.service-statuses.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $s = ServiceStatus::create($data);
        ActivityLogger::log('service_status.created', 'service_status', $s->id, $s->name, null);

        return redirect()->route('admin.service-statuses.index')
            ->with('success', __('announcements.status_created'));
    }

    public function edit(ServiceStatus $serviceStatus)
    {
        return view('admin.service-statuses.edit', ['component' => $serviceStatus]);
    }

    public function update(Request $request, ServiceStatus $serviceStatus)
    {
        $data = $this->validateData($request);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $serviceStatus->update($data);
        ActivityLogger::log('service_status.updated', 'service_status', $serviceStatus->id, $serviceStatus->name, null);

        return redirect()->route('admin.service-statuses.index')
            ->with('success', __('announcements.status_updated'));
    }

    public function destroy(Request $request, ServiceStatus $serviceStatus)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $name = $serviceStatus->name;
        $serviceStatus->delete();
        ActivityLogger::log('service_status.deleted', 'service_status', $serviceStatus->id, $name, null);

        return redirect()->route('admin.service-statuses.index')
            ->with('success', __('announcements.status_deleted'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:' . implode(',', ServiceStatus::STATUSES),
            'sort_order'  => 'nullable|integer|min:0',
        ]);
    }
}
