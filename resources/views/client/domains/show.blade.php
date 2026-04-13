<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ $domain->domain_name }}</h2>
            <a href="{{ route('client.domains.index') }}" class="btn-secondary">← {{ __('domains.my_domains') }}</a>
        </div>
    </x-slot>

    <x-flash-messages />

    @if (session('epp_code'))
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-5">
            <p class="text-sm font-medium text-amber-800 mb-2">{{ __('domains.your_epp_code') }}</p>
            <code class="text-xl font-mono font-bold text-amber-900 select-all block">{{ session('epp_code') }}</code>
            <p class="text-xs text-amber-600 mt-2">{{ __('domains.epp_one_time') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Info --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('domains.domain_info') }}</h3>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500">{{ __('common.status') }}</dt><dd><x-badge :color="$domain->statusBadgeColor()" :label="$domain->status" /></dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.registration_date') }}</dt><dd>{{ $domain->registration_date?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.expiry_date') }}</dt><dd>{{ $domain->expiry_date?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.auto_renew') }}</dt><dd>{{ $domain->auto_renew ? __('common.on') : __('common.off') }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.whois_privacy') }}</dt><dd>{{ $domain->whois_privacy ? __('common.enabled') : __('common.disabled') }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('domains.transfer_lock') }}</dt><dd>{{ $domain->is_locked ? __('domains.locked') : __('domains.unlocked') }}</dd></div>
                </dl>
            </div>

            {{-- Nameservers --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('domains.nameservers') }}</h3>
                <form method="POST" action="{{ route('client.domains.nameservers', $domain) }}" class="space-y-3">
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

            {{-- Registrant --}}
            @if ($registrant = $domain->contacts->firstWhere('type', 'registrant'))
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('domains.registrant_contact') }}</h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm text-gray-700">
                        <div><dt class="text-gray-500">{{ __('common.name') }}</dt><dd>{{ $registrant->fullName() }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('common.email') }}</dt><dd>{{ $registrant->email }}</dd></div>
                        <div><dt class="text-gray-500">{{ __('common.phone') }}</dt><dd>{{ $registrant->phone }}</dd></div>
                        <div class="col-span-2"><dt class="text-gray-500">{{ __('common.address') }}</dt><dd>{{ $registrant->address_1 }}, {{ $registrant->city }}, {{ $registrant->postcode }}, {{ $registrant->country_code }}</dd></div>
                    </dl>
                </div>
            @endif

        </div>

        {{-- Right sidebar --}}
        <div class="space-y-4">

            {{-- Auto Renew --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ __('domains.auto_renew') }}</h4>
                <p class="text-xs text-gray-500 mb-3">
                    {{ $domain->auto_renew ? __('domains.auto_renew_on_desc') : __('domains.auto_renew_off_desc') }}
                </p>
                <form method="POST" action="{{ route('client.domains.auto-renew', $domain) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="{{ $domain->auto_renew ? 'btn-secondary' : 'btn-primary' }} w-full text-sm">
                        {{ $domain->auto_renew ? __('domains.disable_auto_renew') : __('domains.enable_auto_renew') }}
                    </button>
                </form>
            </div>

            {{-- WHOIS Privacy --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ __('domains.whois_privacy') }}</h4>
                <p class="text-xs text-gray-500 mb-3">
                    {{ $domain->whois_privacy ? __('domains.privacy_on_desc') : __('domains.privacy_off_desc') }}
                </p>
                <form method="POST" action="{{ route('client.domains.privacy', $domain) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="{{ $domain->whois_privacy ? 'btn-secondary' : 'btn-primary' }} w-full text-sm">
                        {{ $domain->whois_privacy ? __('domains.disable_privacy') : __('domains.enable_privacy') }}
                    </button>
                </form>
            </div>

            {{-- EPP Code --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ __('domains.epp_code') }}</h4>
                <p class="text-xs text-gray-500 mb-3">{{ __('domains.epp_description') }}</p>
                <form method="POST" action="{{ route('client.domains.epp', $domain) }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full text-sm">{{ __('domains.reveal_epp') }}</button>
                </form>
            </div>

        </div>

    </div>
</x-client-layout>
