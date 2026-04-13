<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(string $category = 'company')
    {
        $settings = Setting::getGroup($category);
        $currencies = Currency::orderBy('is_default', 'desc')->orderBy('name')->get();

        return view('admin.settings.index', compact('category', 'settings', 'currencies'));
    }

    public function update(Request $request, string $category)
    {
        match ($category) {
            'company' => $this->updateCompany($request),
            'branding' => $this->updateBranding($request),
            'currencies' => $this->updateCurrency($request),
            default => abort(404),
        };

        ActivityLogger::log('settings.updated', 'settings', null, $category);

        return back()->with('success', __('settings.saved'));
    }

    private function updateCompany(Request $request): void
    {
        $fields = ['company_name', 'company_address', 'company_city', 'company_state',
                    'company_postcode', 'company_country', 'company_phone', 'company_email',
                    'company_tax_id', 'company_website'];

        foreach ($fields as $field) {
            Setting::set($field, $request->input($field, ''), 'company');
        }
    }

    private function updateBranding(Request $request): void
    {
        $fields = ['brand_name', 'brand_primary_color'];

        foreach ($fields as $field) {
            Setting::set($field, $request->input($field, ''), 'branding');
        }

        if ($request->hasFile('brand_logo')) {
            $path = $request->file('brand_logo')->store('branding', 'public');
            Setting::set('brand_logo', $path, 'branding');
        }

        if ($request->hasFile('brand_favicon')) {
            $path = $request->file('brand_favicon')->store('branding', 'public');
            Setting::set('brand_favicon', $path, 'branding');
        }
    }

    private function updateCurrency(Request $request): void
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:3'],
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:10'],
            'prefix' => ['nullable', 'string', 'max:10'],
            'suffix' => ['nullable', 'string', 'max:10'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:4'],
            'exchange_rate' => ['required', 'numeric', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'currency_id' => ['nullable', 'exists:currencies,id'],
        ]);

        if ($request->filled('currency_id')) {
            $currency = Currency::findOrFail($request->input('currency_id'));
            $currency->update($validated);
        } else {
            $currency = Currency::create($validated);
        }

        if ($request->boolean('is_default')) {
            Currency::where('id', '!=', $currency->id)->update(['is_default' => false]);
            $currency->update(['is_default' => true]);
        }
    }

    public function deleteCurrency(Currency $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', __('settings.cannot_delete_default'));
        }

        $currency->delete();
        return back()->with('success', __('settings.currency_deleted'));
    }
}
