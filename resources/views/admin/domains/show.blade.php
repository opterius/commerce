<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ $domain->domain_name }}</h2>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('admin.domains.sync', $domain) }}">
                    @csrf
                    <button type="submit" class="btn-secondary text-sm">{{ __('domains.sync') }}</button>
                </form>
                <a href="{{ route('admin.domains.index') }}" class="btn-secondary text-sm">← {{ __('common.back') }}</a>
            </div>
        </div>
    </x-slot>

    <x-flash-messages />

    @if (session('epp_code'))
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <p class="text-sm font-medium text-amber-800 mb-1">{{ __('domains.epp_code') }}</p>
            <code class="text-lg font-mono font-bold text-amber-900 select-all">{{ session('epp_code') }}</code>
            <p class="text-xs text-amber-600 mt-1">{{ __('domains.epp_one_time') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Info card --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('domains.domain_info') }}</h3>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500">{{ __('domains.domain_name') }}</dt><dd class="font-medium text-gray-900">{{ $domain->domain_name }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('common.status') }}</dt><dd><x-badge :color="$domain->statusBadgeColor()" :label="$domain->status" /></dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.registrar') }}</dt><dd class="font-medium text-gray-900">{{ $domain->registrar_module }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.registrar_order_id') }}</dt><dd class="font-mono text-gray-700">{{ $domain->registrar_order_id ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.registration_date') }}</dt><dd class="text-gray-700">{{ $domain->registration_date?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.expiry_date') }}</dt><dd class="text-gray-700">{{ $domain->expiry_date?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.next_due_date') }}</dt><dd class="text-gray-700">{{ $domain->next_due_date?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.billing_cycle') }}</dt><dd class="text-gray-700">{{ $domain->billing_cycle }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.auto_renew') }}</dt><dd class="text-gray-700">{{ $domain->auto_renew ? 'Yes' : 'No' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.whois_privacy') }}</dt><dd class="text-gray-700">{{ $domain->whois_privacy ? 'Enabled' : 'Disabled' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.lock') }}</dt><dd class="text-gray-700">{{ $domain->is_locked ? 'Locked' : 'Unlocked' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('common.client') }}</dt><dd><a href="{{ route('admin.clients.show', $domain->client_id) }}" class="text-indigo-600 hover:underline">{{ $domain->client?->company_name ?: $domain->client?->first_name . ' ' . $domain->client?->last_name }}</a></dd></div>
                </dl>
            </div>

            {{-- Nameservers --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('domains.nameservers') }}</h3>
                <form method="POST" action="{{ route('admin.domains.nameservers', $domain) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    @foreach (['ns1','ns2','ns3','ns4'] as $ns)
                        <div>
                            <label class="form-label">{{ strtoupper($ns) }}</label>
                            <input type="text" name="{{ $ns }}" value="{{ old($ns, $domain->{$ns}) }}" class="form-input" placeholder="ns1.example.com">
                        </div>
                    @endforeach
                    <button type="submit" class="btn-primary">{{ __('domains.update_nameservers') }}</button>
                </form>
            </div>

            {{-- Registrant contact --}}
            @if ($registrant = $domain->contacts->firstWhere('type', 'registrant'))
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('domains.registrant_contact') }}</h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div><dt class="text-gray-500">{{ __('common.name') }}</dt><dd class="text-gray-900">{{ $registrant->fullName() }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('common.email') }}</dt><dd class="text-gray-900">{{ $registrant->email }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('common.phone') }}</dt><dd class="text-gray-900">{{ $registrant->phone }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('common.company') }}</dt><dd class="text-gray-900">{{ $registrant->company ?? '—' }}</dd></div>
                        <div class="col-span-2"><dt class="text-gray-500">{{ __('common.address') }}</dt><dd class="text-gray-900">{{ $registrant->address_1 }}, {{ $registrant->city }}, {{ $registrant->postcode }}, {{ $registrant->country_code }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('domains.registrar_contact_id') }}</dt><dd class="font-mono text-gray-700">{{ $registrant->registrar_contact_id ?? '—' }}</dd></div>
                    </dl>
                </div>
            @endif

            {{-- Notes --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('common.notes') }}</h3>
                <form method="POST" action="{{ route('admin.domains.notes', $domain) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <textarea name="notes" rows="4" class="form-input">{{ old('notes', $domain->notes) }}</textarea>
                    <button type="submit" class="btn-primary">{{ __('common.save') }}</button>
                </form>
            </div>

        </div>

        {{-- Right sidebar --}}
        <div class="space-y-4">

            {{-- WHOIS Privacy --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('domains.whois_privacy') }}</h4>
                <p class="text-xs text-gray-500 mb-3">{{ $domain->whois_privacy ? 'Currently enabled.' : 'Currently disabled.' }}</p>
                <form method="POST" action="{{ route('admin.domains.privacy', $domain) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="{{ $domain->whois_privacy ? 'btn-secondary' : 'btn-primary' }} w-full text-sm">
                        {{ $domain->whois_privacy ? __('domains.disable_privacy') : __('domains.enable_privacy') }}
                    </button>
                </form>
            </div>

            {{-- Transfer Lock --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('domains.transfer_lock') }}</h4>
                <p class="text-xs text-gray-500 mb-3">{{ $domain->is_locked ? 'Domain is locked.' : 'Domain is unlocked.' }}</p>
                <form method="POST" action="{{ route('admin.domains.lock', $domain) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="{{ $domain->is_locked ? 'btn-secondary' : 'btn-primary' }} w-full text-sm">
                        {{ $domain->is_locked ? __('domains.unlock_domain') : __('domains.lock_domain') }}
                    </button>
                </form>
            </div>

            {{-- EPP Code --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('domains.epp_code') }}</h4>
                <p class="text-xs text-gray-500 mb-3">{{ __('domains.epp_description') }}</p>
                <form method="POST" action="{{ route('admin.domains.epp', $domain) }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full text-sm">{{ __('domains.reveal_epp') }}</button>
                </form>
            </div>

        </div>

    </div>
</x-admin-layout>
