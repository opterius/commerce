<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PromoCode;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::latest()->paginate(config('commerce.pagination', 25));
        return view('admin.products.promos.index', compact('promoCodes'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('admin.products.promos.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:promo_codes'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'recurring' => ['nullable', 'boolean'],
            'applies_to' => ['required', 'in:all,specific'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'products' => ['nullable', 'array'],
            'products.*' => ['exists:products,id'],
        ]);

        // Convert human value to stored value (cents or basis points)
        if ($validated['type'] === 'percent') {
            $validated['value'] = (int) round($validated['value'] * 100); // 20% = 2000
        } else {
            $validated['value'] = (int) round($validated['value'] * 100); // $5.00 = 500
        }

        $validated['recurring'] = $request->boolean('recurring');
        $validated['is_active'] = $request->boolean('is_active', true);

        $promo = PromoCode::create($validated);

        if ($validated['applies_to'] === 'specific' && $request->has('products')) {
            $promo->products()->sync($request->input('products'));
        }

        ActivityLogger::log('promo_code.created', 'promo_code', $promo->id, $promo->code);

        return redirect()->route('admin.promo-codes.index')
            ->with('success', __('products.promo_created'));
    }

    public function edit(PromoCode $promoCode)
    {
        $promoCode->load('products');
        $products = Product::orderBy('name')->get();

        // Convert stored value back to human-readable for form
        $displayValue = $promoCode->value / 100;

        return view('admin.products.promos.edit', [
            'promo' => $promoCode,
            'products' => $products,
            'displayValue' => $displayValue,
        ]);
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:promo_codes,code,' . $promoCode->id],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'recurring' => ['nullable', 'boolean'],
            'applies_to' => ['required', 'in:all,specific'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'products' => ['nullable', 'array'],
            'products.*' => ['exists:products,id'],
        ]);

        $validated['value'] = (int) round($validated['value'] * 100);
        $validated['recurring'] = $request->boolean('recurring');
        $validated['is_active'] = $request->boolean('is_active', true);

        $promoCode->update($validated);

        if ($validated['applies_to'] === 'specific') {
            $promoCode->products()->sync($request->input('products', []));
        } else {
            $promoCode->products()->detach();
        }

        ActivityLogger::log('promo_code.updated', 'promo_code', $promoCode->id, $promoCode->code);

        return redirect()->route('admin.promo-codes.index')
            ->with('success', __('products.promo_updated'));
    }

    public function destroy(Request $request, PromoCode $promoCode)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        ActivityLogger::log('promo_code.deleted', 'promo_code', $promoCode->id, $promoCode->code);
        $promoCode->delete();

        return redirect()->route('admin.promo-codes.index')
            ->with('success', __('products.promo_deleted'));
    }
}
