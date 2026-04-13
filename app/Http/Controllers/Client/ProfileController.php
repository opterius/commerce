<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $client     = auth('client')->user();
        $currencies = Currency::where('is_active', true)->orderBy('code')->get();
        return view('client.profile', compact('client', 'currencies'));
    }

    public function update(Request $request)
    {
        $client = auth('client')->user();

        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('clients')->ignore($client->id)],
            'company_name'  => ['nullable', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'address_1'     => ['nullable', 'string', 'max:255'],
            'address_2'     => ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:100'],
            'state'         => ['nullable', 'string', 'max:100'],
            'postcode'      => ['nullable', 'string', 'max:20'],
            'country_code'  => ['nullable', 'string', 'size:2'],
            'currency_code' => ['nullable', 'string', 'size:3', 'exists:currencies,code'],
        ]);

        $client->update($validated);

        ActivityLogger::log(
            'client.profile_updated',
            'client',
            $client->id,
            $client->full_name,
        );

        return back()->with('success', __('clients.client_updated'));
    }
}
