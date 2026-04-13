<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth('client')->user()
            ->orders()
            ->with(['items.product'])
            ->orderByDesc('created_at')
            ->paginate(config('commerce.pagination', 25));

        return view('client.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_if($order->client_id !== auth('client')->id(), 403);

        $order->load(['items.product', 'promoCode', 'invoice']);

        return view('client.orders.show', compact('order'));
    }
}
