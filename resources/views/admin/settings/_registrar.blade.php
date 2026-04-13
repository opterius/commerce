<div class="space-y-6" x-data="{ module: '{{ $settings['registrar_module'] ?? 'resellerclub' }}' }">

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-6">{{ __('domains.registrar_settings') }}</h3>

        <form method="POST" action="{{ route('admin.settings.update', 'registrar') }}">
            @csrf

            <div class="space-y-6">

                {{-- Module selector --}}
                <div>
                    <label class="form-label">{{ __('domains.registrar_module') }}</label>
                    <select name="registrar_module" x-model="module" class="form-input w-64">
                        <option value="resellerclub">ResellerClub (LogicBoxes)</option>
                        <option value="enom">Enom (Tucows)</option>
                        <option value="opensrs">OpenSRS (Tucows)</option>
                        <option value="namecheap">Namecheap</option>
                        <option value="centralnic">CentralNic Reseller (Hexonet)</option>
                    </select>
                </div>

                {{-- ResellerClub credentials --}}
                <div x-show="module === 'resellerclub'" x-cloak class="space-y-4 border border-gray-100 rounded-lg p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">ResellerClub API Credentials</p>

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

                {{-- Enom credentials --}}
                <div x-show="module === 'enom'" x-cloak class="space-y-4 border border-gray-100 rounded-lg p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Enom API Credentials</p>

                    <div>
                        <x-input name="enom_uid" label="Enom Username (UID)"
                            :value="$settings['enom_uid'] ?? ''" placeholder="your-enom-username" />
                        <p class="mt-1 text-xs text-gray-400">Your Enom reseller account username.</p>
                    </div>

                    <div>
                        <x-input name="enom_pw" label="Enom Password"
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

                {{-- OpenSRS credentials --}}
                <div x-show="module === 'opensrs'" x-cloak class="space-y-4 border border-gray-100 rounded-lg p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">OpenSRS API Credentials</p>

                    <div>
                        <x-input name="opensrs_username" label="OpenSRS Username"
                            :value="$settings['opensrs_username'] ?? ''" placeholder="your-opensrs-username" />
                        <p class="mt-1 text-xs text-gray-400">Your OpenSRS reseller account username.</p>
                    </div>

                    <div>
                        <x-input name="opensrs_private_key" label="OpenSRS Private Key"
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

                {{-- Namecheap credentials --}}
                <div x-show="module === 'namecheap'" x-cloak class="space-y-4 border border-gray-100 rounded-lg p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Namecheap API Credentials</p>

                    <div>
                        <x-input name="namecheap_api_user" label="API Username"
                            :value="$settings['namecheap_api_user'] ?? ''" placeholder="your-namecheap-username" />
                        <p class="mt-1 text-xs text-gray-400">Your Namecheap account username (same as login).</p>
                    </div>

                    <div>
                        <x-input name="namecheap_api_key" label="API Key"
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

                {{-- CentralNic Reseller credentials --}}
                <div x-show="module === 'centralnic'" x-cloak class="space-y-4 border border-gray-100 rounded-lg p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">CentralNic Reseller API Credentials</p>

                    <div>
                        <x-input name="centralnic_login" label="Reseller Login"
                            :value="$settings['centralnic_login'] ?? ''" placeholder="your-reseller-id" />
                        <p class="mt-1 text-xs text-gray-400">Your CentralNic Reseller account username (formerly Hexonet login).</p>
                    </div>

                    <div>
                        <x-input name="centralnic_password" label="Password"
                            :value="$settings['centralnic_password'] ?? ''" placeholder="your-password" type="password" />
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

            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
            </div>
        </form>
    </div>

    {{-- Test Connection --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-2">{{ __('domains.test_connection') }}</h3>
        <p class="text-sm text-gray-500 mb-4">{{ __('domains.test_connection_help') }}</p>
        <form method="POST" action="{{ route('admin.domains.test-connection') }}">
            @csrf
            <button type="submit" class="btn-secondary">{{ __('domains.run_test') }}</button>
        </form>
    </div>

</div>
