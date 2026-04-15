<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCancellationRequest;
use Illuminate\Http\Request;

class ServiceCancellationController extends Controller
{
    private function client()
    {
        return auth('client')->user();
    }

    public function create(Service $service)
    {
        $this->authorizeClient($service);

        if (in_array($service->status, ['cancelled', 'terminated'])) {
            return redirect()->route('client.services.show', $service)
                ->with('error', __('cancellations.already_cancelled'));
        }

        $pending = ServiceCancellationRequest::where('service_id', $service->id)
            ->where('status', 'pending')
            ->exists();

        if ($pending) {
            return redirect()->route('client.services.show', $service)
                ->with('error', __('cancellations.already_requested'));
        }

        return view('client.services.cancel', compact('service'));
    }

    public function store(Request $request, Service $service)
    {
        $this->authorizeClient($service);

        if (in_array($service->status, ['cancelled', 'terminated'])) {
            return redirect()->route('client.services.show', $service)
                ->with('error', __('cancellations.already_cancelled'));
        }

        $pending = ServiceCancellationRequest::where('service_id', $service->id)
            ->where('status', 'pending')
            ->exists();

        if ($pending) {
            return redirect()->route('client.services.show', $service)
                ->with('error', __('cancellations.already_requested'));
        }

        $data = $request->validate([
            'reason'      => 'required|string|max:2000',
            'cancel_type' => 'required|in:immediate,end_of_period',
        ]);

        ServiceCancellationRequest::create([
            'service_id'  => $service->id,
            'client_id'   => $this->client()->id,
            'reason'      => $data['reason'],
            'cancel_type' => $data['cancel_type'],
            'status'      => 'pending',
        ]);

        return redirect()->route('client.services.show', $service)
            ->with('success', __('cancellations.request_submitted'));
    }

    private function authorizeClient(Service $service): void
    {
        if ($service->client_id !== $this->client()->id) {
            abort(403);
        }
    }
}
