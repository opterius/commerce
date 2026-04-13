<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientGroup;
use App\Models\ClientTag;
use App\Models\Currency;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('group', 'tags');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($groupId = $request->input('group')) {
            $query->where('client_group_id', $groupId);
        }

        $clients = $query->latest()->paginate(config('commerce.pagination', 25));
        $groups = ClientGroup::orderBy('name')->get();

        return view('admin.clients.index', compact('clients', 'groups'));
    }

    public function create()
    {
        $groups = ClientGroup::orderBy('name')->get();
        $tags = ClientTag::orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->get();
        $customFields = CustomField::where('entity_type', 'client')
            ->orderBy('sort_order')->get();

        return view('admin.clients.create', compact(
            'groups', 'tags', 'currencies', 'customFields'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:clients'],
            'password' => ['nullable', 'string', 'min:8'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_1' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'language' => ['nullable', 'string', 'max:10'],
            'client_group_id' => ['nullable', 'exists:client_groups,id'],
            'status' => ['nullable', 'in:active,inactive,closed'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:client_tags,id'],
        ]);

        if (empty($validated['password'])) {
            $validated['password'] = Str::random(12);
        }

        $client = Client::create($validated);

        if ($request->filled('tags')) {
            $client->tags()->sync($request->input('tags'));
        }

        // Save custom field values
        $this->saveCustomFields($client, $request);

        ActivityLogger::log(
            'client.created',
            'client',
            $client->id,
            $client->full_name,
            __('clients.client_created'),
        );

        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('clients.client_created'));
    }

    public function show(Client $client)
    {
        $client->load('group', 'tags', 'contacts', 'notes.staff');
        $customFields = CustomField::where('entity_type', 'client')
            ->orderBy('sort_order')->get();
        $customFieldValues = CustomFieldValue::where('entity_type', 'client')
            ->where('entity_id', $client->id)
            ->pluck('value', 'custom_field_id');
        $recentActivity = ActivityLog::where('entity_type', 'client')
            ->where('entity_id', $client->id)
            ->latest('created_at')
            ->take(20)
            ->get();

        return view('admin.clients.show', compact(
            'client', 'customFields', 'customFieldValues', 'recentActivity'
        ));
    }

    public function edit(Client $client)
    {
        $groups = ClientGroup::orderBy('name')->get();
        $tags = ClientTag::orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->get();
        $customFields = CustomField::where('entity_type', 'client')
            ->orderBy('sort_order')->get();
        $customFieldValues = CustomFieldValue::where('entity_type', 'client')
            ->where('entity_id', $client->id)
            ->pluck('value', 'custom_field_id');

        return view('admin.clients.edit', compact(
            'client', 'groups', 'tags', 'currencies',
            'customFields', 'customFieldValues'
        ));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('clients')->ignore($client->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_1' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'language' => ['nullable', 'string', 'max:10'],
            'client_group_id' => ['nullable', 'exists:client_groups,id'],
            'status' => ['nullable', 'in:active,inactive,closed'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:client_tags,id'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $client->update($validated);
        $client->tags()->sync($request->input('tags', []));
        $this->saveCustomFields($client, $request);

        ActivityLogger::log(
            'client.updated',
            'client',
            $client->id,
            $client->full_name,
        );

        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('clients.client_updated'));
    }

    public function destroy(Request $request, Client $client)
    {
        $request->validate([
            'password' => ['required', 'current_password:staff'],
        ]);

        ActivityLogger::log(
            'client.deleted',
            'client',
            $client->id,
            $client->full_name,
        );

        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', __('clients.client_deleted'));
    }

    private function saveCustomFields(Client $client, Request $request): void
    {
        $customFields = CustomField::where('entity_type', 'client')->get();

        foreach ($customFields as $field) {
            $value = $request->input("custom_field.{$field->id}");

            CustomFieldValue::updateOrCreate(
                [
                    'custom_field_id' => $field->id,
                    'entity_type' => 'client',
                    'entity_id' => $client->id,
                ],
                ['value' => $value]
            );
        }
    }
}
