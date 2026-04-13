<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = auth('client')->user()
            ->services()
            ->with('product')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $services = $query->paginate(config('commerce.pagination', 25))->withQueryString();

        return view('client.services.index', compact('services'));
    }

    public function show(Service $service)
    {
        abort_if($service->client_id !== auth('client')->id(), 403);

        $service->load(['product', 'order']);

        return view('client.services.show', compact('service'));
    }
}
