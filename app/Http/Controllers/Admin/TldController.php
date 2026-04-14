<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainTld;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class TldController extends Controller
{
    public function index()
    {
        $tlds = DomainTld::orderBy('sort_order')->orderBy('tld')->get();

        return view('admin.tlds.index', compact('tlds'));
    }

    public function create()
    {
        return view('admin.tlds.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tld'                     => 'required|string|max:30|unique:domain_tlds,tld',
            'is_active'               => 'boolean',
            'sort_order'              => 'nullable|integer|min:0',
            'register_price'          => 'required|integer|min:0',
            'renew_price'             => 'required|integer|min:0',
            'transfer_price'          => 'required|integer|min:0',
            'min_years'               => 'required|integer|min:1|max:10',
            'max_years'               => 'required|integer|min:1|max:10',
            'epp_required'            => 'boolean',
            'whois_privacy_available' => 'boolean',
            'grace_period_days'       => 'nullable|integer|min:0',
            'redemption_period_days'  => 'nullable|integer|min:0',
            'currency_code'           => 'required|string|size:3',
        ]);

        $data['tld']                     = ltrim(strtolower($data['tld']), '.');
        $data['is_active']               = $request->boolean('is_active', true);
        $data['epp_required']            = $request->boolean('epp_required');
        $data['whois_privacy_available'] = $request->boolean('whois_privacy_available');
        $data['sort_order']              = $data['sort_order'] ?? 0;
        $data['grace_period_days']       = $data['grace_period_days'] ?? 0;
        $data['redemption_period_days']  = $data['redemption_period_days'] ?? 0;

        $tld = DomainTld::create($data);

        ActivityLogger::log('tld.created', 'tld', $tld->id, $tld->tld, null);

        return redirect()->route('admin.tlds.index')
            ->with('success', ".{$tld->tld} TLD created.");
    }

    public function edit(DomainTld $tld)
    {
        return view('admin.tlds.edit', compact('tld'));
    }

    public function update(Request $request, DomainTld $tld)
    {
        $data = $request->validate([
            'is_active'               => 'boolean',
            'sort_order'              => 'nullable|integer|min:0',
            'register_price'          => 'required|integer|min:0',
            'renew_price'             => 'required|integer|min:0',
            'transfer_price'          => 'required|integer|min:0',
            'min_years'               => 'required|integer|min:1|max:10',
            'max_years'               => 'required|integer|min:1|max:10',
            'epp_required'            => 'boolean',
            'whois_privacy_available' => 'boolean',
            'grace_period_days'       => 'nullable|integer|min:0',
            'redemption_period_days'  => 'nullable|integer|min:0',
            'currency_code'           => 'required|string|size:3',
        ]);

        $data['is_active']               = $request->boolean('is_active');
        $data['epp_required']            = $request->boolean('epp_required');
        $data['whois_privacy_available'] = $request->boolean('whois_privacy_available');
        $data['sort_order']              = $data['sort_order'] ?? 0;
        $data['grace_period_days']       = $data['grace_period_days'] ?? 0;
        $data['redemption_period_days']  = $data['redemption_period_days'] ?? 0;

        $tld->update($data);

        ActivityLogger::log('tld.updated', 'tld', $tld->id, $tld->tld, null);

        return redirect()->route('admin.tlds.index')
            ->with('success', ".{$tld->tld} TLD updated.");
    }

    public function destroy(Request $request, DomainTld $tld)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $name = $tld->tld;
        $tld->delete();

        ActivityLogger::log('tld.deleted', 'tld', $tld->id, $name, null);

        return redirect()->route('admin.tlds.index')
            ->with('success', ".{$name} TLD deleted.");
    }
}
