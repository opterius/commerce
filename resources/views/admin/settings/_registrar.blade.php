<div class="space-y-6">

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-6">{{ __('domains.registrar_settings') }}</h3>

        <form method="POST" action="{{ route('admin.settings.update', 'registrar') }}">
            @csrf

            <div class="space-y-5">
                <div>
                    <label class="form-label">{{ __('domains.registrar_module') }}</label>
                    <select name="registrar_module" class="form-input w-56">
                        <option value="resellerclub" {{ ($settings['registrar_module'] ?? 'resellerclub') === 'resellerclub' ? 'selected' : '' }}>
                            ResellerClub (LogicBoxes)
                        </option>
                    </select>
                </div>

                <div>
                    <x-input name="resellerclub_auth_userid" :label="__('domains.auth_userid')"
                        :value="$settings['resellerclub_auth_userid'] ?? ''" placeholder="123456" />
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
                    <p class="mt-1 text-xs text-gray-500">{{ __('domains.sandbox_help') }}</p>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100 flex items-center gap-4">
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
