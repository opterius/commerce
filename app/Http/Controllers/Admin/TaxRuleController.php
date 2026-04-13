<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRule;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class TaxRuleController extends Controller
{
    public function index()
    {
        $taxRules = TaxRule::orderBy('sort_order')->orderBy('name')
            ->paginate(config('commerce.pagination', 25));

        return view('admin.tax-rules.index', compact('taxRules'));
    }

    public function create()
    {
        return view('admin.tax-rules.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'country_code' => ['required', 'string', 'size:2'],
            'state_code'   => ['nullable', 'string', 'max:10'],
            'rate'         => ['required', 'numeric', 'min:0', 'max:100'],
            'applies_to'   => ['required', 'in:all,hosting,one_time'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_eu_tax']  = $request->boolean('is_eu_tax');
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->input('sort_order', 0);

        $taxRule = TaxRule::create($validated);

        ActivityLogger::log('tax_rule.created', 'tax_rule', $taxRule->id, $taxRule->name);

        return redirect()->route('admin.tax-rules.index')
            ->with('success', 'Tax rule created.');
    }

    public function edit(TaxRule $taxRule)
    {
        return view('admin.tax-rules.edit', compact('taxRule'));
    }

    public function update(Request $request, TaxRule $taxRule)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'country_code' => ['required', 'string', 'size:2'],
            'state_code'   => ['nullable', 'string', 'max:10'],
            'rate'         => ['required', 'numeric', 'min:0', 'max:100'],
            'applies_to'   => ['required', 'in:all,hosting,one_time'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_eu_tax']  = $request->boolean('is_eu_tax');
        $validated['is_active']  = $request->boolean('is_active');
        $validated['sort_order'] = $request->input('sort_order', 0);

        $taxRule->update($validated);

        ActivityLogger::log('tax_rule.updated', 'tax_rule', $taxRule->id, $taxRule->name);

        return redirect()->route('admin.tax-rules.index')
            ->with('success', 'Tax rule updated.');
    }

    public function destroy(Request $request, TaxRule $taxRule)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        ActivityLogger::log('tax_rule.deleted', 'tax_rule', $taxRule->id, $taxRule->name);

        $taxRule->delete();

        return redirect()->route('admin.tax-rules.index')
            ->with('success', 'Tax rule deleted.');
    }
}
