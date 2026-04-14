<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ProductGroup;

class PortalController extends Controller
{
    public function home()
    {
        $currency = Currency::getDefault();

        $groups = ProductGroup::with([
            'products' => fn($q) => $q->where('status', 'active')->orderBy('sort_order'),
            'products.pricing',
        ])
        ->whereHas('products', fn($q) => $q->where('status', 'active'))
        ->orderBy('sort_order')
        ->get();

        return view('portal.home', compact('groups', 'currency'));
    }
}
