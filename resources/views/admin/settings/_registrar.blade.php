@php
    $currentModule = $settings['registrar_module'] ?? '';

    $registrars = [
        'resellerclub' => [
            'label'       => 'ResellerClub',
            'sublabel'    => 'LogicBoxes',
            'description' => 'Connect via the LogicBoxes HTTP API. Used by ResellerClub, BigRock, NetEarthOne, and other LogicBoxes-based resellers.',
        ],
        'enom' => [
            'label'       => 'Enom',
            'sublabel'    => 'Tucows',
            'description' => 'Connect via the Enom XML API. Enom is a Tucows company offering wholesale domain registration with a broad TLD portfolio.',
        ],
        'opensrs' => [
            'label'       => 'OpenSRS',
            'sublabel'    => 'Tucows',
            'description' => 'Connect via the OpenSRS XML API. One of the largest wholesale domain registrars, operated by Tucows, with extensive reseller tools.',
        ],
        'namecheap' => [
            'label'       => 'Namecheap',
            'sublabel'    => 'API v1',
            'description' => 'Connect via the Namecheap XML API. Requires your server IP to be whitelisted in your Namecheap reseller account.',
        ],
        'centralnic' => [
            'label'       => 'CentralNic Reseller',
            'sublabel'    => 'Hexonet',
            'description' => 'Connect via the CentralNic Reseller (formerly Hexonet) HTTP API. Supports a wide range of new gTLDs and ccTLDs.',
        ],
    ];
@endphp

<div class="space-y-6" x-data="{ module: '{{ $currentModule }}' }">

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-5">{{ __('domains.registrar_settings') }}</h3>

        {{-- Registrar cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
            @foreach ($registrars as $id => $reg)
                <button
                    type="button"
                    x-on:click="module = module === '{{ $id }}' ? '' : '{{ $id }}'"
                    :class="module === '{{ $id }}'
                        ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500'
                        : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'"
                    class="relative flex items-center justify-between gap-3 rounded-xl border-2 px-4 py-3 text-left transition-all cursor-pointer"
                >
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $reg['label'] }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $reg['sublabel'] }}</p>
                    </div>
                    <span
                        x-show="module === '{{ $id }}'"
                        class="flex-shrink-0 flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600"
                    >
                        <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                </button>
            @endforeach
        </div>

        {{-- No module selected placeholder --}}
        <template x-if="module === ''">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-400">
                Select a registrar above to configure your domain API credentials.
            </div>
        </template>

        {{-- ResellerClub --}}
        <template x-if="module === 'resellerclub'">
            <form method="POST" action="{{ route('admin.settings.update', 'registrar') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="registrar_module" value="resellerclub">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 space-y-5">
                    <div class="pb-2 border-b border-gray-200">
                        <p class="text-sm font-semibold text-gray-800">{{ $registrars['resellerclub']['label'] }}</p>
                        <p class="mt-1 text-sm text-gray-500 leading-relaxed">{{ $registrars['resellerclub']['description'] }}</p>
                    </div>
                    <div>
                        <x-input name="resellerclub_auth_userid" :label="__('domains.auth_userid')"
                            :value="$settings['resellerclub_auth_userid'] ?? ''" placeholder="123456" />
                        <p class="mt-1 text-xs text-gray-400">Found in your ResellerClub reseller control panel.</p>
                    </div>
                    <div>
                        <x-input name="resellerclub_api_key" :label="__('domains.api_key')"
                            :value="$settings['resellerclub_api_key'] ?? ''" placeholder="your-api-key" />
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="resellerclub_sandbox" value="1"
                                {{ ($settings['resellerclub_sandbox'] ?? '1') ? 'checked' : '' }} class="rounded">
                            {{ __('domains.sandbox_mode') }}
                        </label>
                        <p class="mt-1 text-xs text-gray-400">Routes API calls to test.httpapi.com. Disable in production.</p>
                    </div>
                </div>
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
            </form>
        </template>

        {{-- Enom --}}
        <template x-if="module === 'enom'">
            <form method="POST" action="{{ route('admin.settings.update', 'registrar') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="registrar_module" value="enom">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 space-y-5">
                    <div class="pb-2 border-b border-gray-200">
                        <p class="text-sm font-semibold text-gray-800">{{ $registrars['enom']['label'] }}</p>
                        <p class="mt-1 text-sm text-gray-500 leading-relaxed">{{ $registrars['enom']['description'] }}</p>
                    </div>
                    <div>
                        <x-input name="enom_uid" label="Enom Username (UID)"
                            :value="$settings['enom_uid'] ?? ''" placeholder="your-enom-username" />
                        <p class="mt-1 text-xs text-gray-400">Your Enom reseller account username.</p>
                    </div>
                    <div>
                        <x-input name="enom_pw" label="Enom Password" type="password"
                            :value="$settings['enom_pw'] ?? ''" placeholder="your-enom-password" />
                        <p class="mt-1 text-xs text-gray-400">Your Enom reseller account password.</p>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="enom_sandbox" value="1"
                                {{ ($settings['enom_sandbox'] ?? '1') ? 'checked' : '' }} class="rounded">
                            Sandbox Mode (resellertest.enom.com)
                        </label>
                        <p class="mt-1 text-xs text-gray-400">Routes API calls to the Enom test environment. Disable in production.</p>
                    </div>
                </div>
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
            </form>
        </template>

        {{-- OpenSRS --}}
        <template x-if="module === 'opensrs'">
            <form method="POST" action="{{ route('admin.settings.update', 'registrar') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="registrar_module" value="opensrs">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 space-y-5">
                    <div class="pb-2 border-b border-gray-200">
                        <p class="text-sm font-semibold text-gray-800">{{ $registrars['opensrs']['label'] }}</p>
                        <p class="mt-1 text-sm text-gray-500 leading-relaxed">{{ $registrars['opensrs']['description'] }}</p>
                    </div>
                    <div>
                        <x-input name="opensrs_username" label="OpenSRS Username"
                            :value="$settings['opensrs_username'] ?? ''" placeholder="your-opensrs-username" />
                        <p class="mt-1 text-xs text-gray-400">Your OpenSRS reseller account username.</p>
                    </div>
                    <div>
                        <x-input name="opensrs_private_key" label="OpenSRS Private Key" type="password"
                            :value="$settings['opensrs_private_key'] ?? ''" placeholder="your-private-key" />
                        <p class="mt-1 text-xs text-gray-400">Found in your OpenSRS reseller control panel under Profile → API Access.</p>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="opensrs_sandbox" value="1"
                                {{ ($settings['opensrs_sandbox'] ?? '1') ? 'checked' : '' }} class="rounded">
                            Sandbox Mode (horizon.opensrs.net)
                        </label>
                        <p class="mt-1 text-xs text-gray-400">Routes API calls to the OpenSRS test environment. Disable in production.</p>
                    </div>
                </div>
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
            </form>
        </template>

        {{-- Namecheap --}}
        <template x-if="module === 'namecheap'">
            <form method="POST" action="{{ route('admin.settings.update', 'registrar') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="registrar_module" value="namecheap">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 space-y-5">
                    <div class="pb-2 border-b border-gray-200">
                        <p class="text-sm font-semibold text-gray-800">{{ $registrars['namecheap']['label'] }}</p>
                        <p class="mt-1 text-sm text-gray-500 leading-relaxed">{{ $registrars['namecheap']['description'] }}</p>
                    </div>
                    <div>
                        <x-input name="namecheap_api_user" label="API Username"
                            :value="$settings['namecheap_api_user'] ?? ''" placeholder="your-namecheap-username" />
                        <p class="mt-1 text-xs text-gray-400">Your Namecheap account username (same as login).</p>
                    </div>
                    <div>
                        <x-input name="namecheap_api_key" label="API Key" type="password"
                            :value="$settings['namecheap_api_key'] ?? ''" placeholder="your-api-key" />
                        <p class="mt-1 text-xs text-gray-400">Found in Profile → Tools → API Access. Must be enabled and your server IP whitelisted.</p>
                    </div>
                    <div>
                        <x-input name="namecheap_client_ip" label="Server IP Address"
                            :value="$settings['namecheap_client_ip'] ?? ''" placeholder="1.2.3.4" />
                        <p class="mt-1 text-xs text-gray-400">Your server's public IP. Must be whitelisted in your Namecheap account under API Access.</p>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="namecheap_sandbox" value="1"
                                {{ ($settings['namecheap_sandbox'] ?? '1') ? 'checked' : '' }} class="rounded">
                            Sandbox Mode (api.sandbox.namecheap.com)
                        </label>
                        <p class="mt-1 text-xs text-gray-400">Routes API calls to the Namecheap test environment. Disable in production.</p>
                    </div>
                </div>
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
            </form>
        </template>

        {{-- CentralNic --}}
        <template x-if="module === 'centralnic'">
            <form method="POST" action="{{ route('admin.settings.update', 'registrar') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="registrar_module" value="centralnic">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 space-y-5">
                    <div class="pb-2 border-b border-gray-200">
                        <p class="text-sm font-semibold text-gray-800">{{ $registrars['centralnic']['label'] }}</p>
                        <p class="mt-1 text-sm text-gray-500 leading-relaxed">{{ $registrars['centralnic']['description'] }}</p>
                    </div>
                    <div>
                        <x-input name="centralnic_login" label="Reseller Login"
                            :value="$settings['centralnic_login'] ?? ''" placeholder="your-reseller-id" />
                        <p class="mt-1 text-xs text-gray-400">Your CentralNic Reseller account username (formerly Hexonet login).</p>
                    </div>
                    <div>
                        <x-input name="centralnic_password" label="Password" type="password"
                            :value="$settings['centralnic_password'] ?? ''" placeholder="your-password" />
                        <p class="mt-1 text-xs text-gray-400">Your CentralNic Reseller account password.</p>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="centralnic_sandbox" value="1"
                                {{ ($settings['centralnic_sandbox'] ?? '1') ? 'checked' : '' }} class="rounded">
                            OTE Mode (api-ote.rrpproxy.net)
                        </label>
                        <p class="mt-1 text-xs text-gray-400">Routes API calls to the CentralNic OTE test environment. Disable in production.</p>
                    </div>
                </div>
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
            </form>
        </template>
    </div>

    {{-- Test Connection — only when a module is active --}}
    <template x-if="module !== ''">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-1">{{ __('domains.test_connection') }}</h3>
            <p class="text-sm text-gray-500 mb-4">{{ __('domains.test_connection_help') }}</p>
            <form method="POST" action="{{ route('admin.domains.test-connection') }}">
                @csrf
                <button type="submit" class="btn-secondary">{{ __('domains.run_test') }}</button>
            </form>
        </div>
    </template>

</div>
