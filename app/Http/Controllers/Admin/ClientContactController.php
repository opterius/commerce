<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContact;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientContactController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:client_contacts'],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'in:billing,technical,admin'],
        ]);

        if (empty($validated['password'])) {
            $validated['password'] = Str::random(12);
        }

        $client->contacts()->create($validated);

        ActivityLogger::log(
            'client.contact_created',
            'client',
            $client->id,
            $client->full_name,
        );

        return back()->with('success', __('clients.contact_created'));
    }

    public function update(Request $request, Client $client, ClientContact $contact)
    {
        if ($contact->client_id !== $client->id) {
            abort(404);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('client_contacts')->ignore($contact->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'in:billing,technical,admin'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $contact->update($validated);

        return back()->with('success', __('clients.contact_updated'));
    }

    public function destroy(Request $request, Client $client, ClientContact $contact)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        if ($contact->client_id !== $client->id) {
            abort(404);
        }

        $contact->delete();

        return back()->with('success', __('clients.contact_deleted'));
    }
}
