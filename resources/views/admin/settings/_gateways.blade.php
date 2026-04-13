<div class="space-y-6">
    @foreach ($allGateways as $slug => $gateway)
        @php
            $isEnabled = (bool) \App\Models\Setting::get("gateway_{$slug}_enabled", false);
        @endphp
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <span class="text-sm font-semibold text-gray-800">{{ $gateway->name() }}</span>
                    @if ($gateway->isConfigured() && $isEnabled)
                        <x-badge color="green" class="ml-2">{{ __('common.active') }}</x-badge>
                    @elseif ($isEnabled && !$gateway->isConfigured())
                        <x-badge color="amber" class="ml-2">{{ __('settings.not_configured') }}</x-badge>
                    @else
                        <x-badge color="gray" class="ml-2">{{ __('common.inactive') }}</x-badge>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.update', 'gateways') }}">
                @csrf
                <input type="hidden" name="gateway_slug" value="{{ $slug }}">

                <div class="p-6 space-y-4">
                    {{-- Enable toggle --}}
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="gateway_{{ $slug }}_enabled" value="1"
                               class="rounded text-indigo-600"
                               {{ $isEnabled ? 'checked' : '' }}>
                        {{ __('settings.enable_gateway', ['name' => $gateway->name()]) }}
                    </label>

                    {{-- Dynamic settings fields --}}
                    @foreach ($gateway->settingsFields() as $field)
                        @php $value = \App\Models\Setting::get("gateway_{$slug}_{$field['key']}", ''); @endphp
                        <div>
                            <label class="form-label">
                                {{ $field['label'] }}
                                @if (!empty($field['required'])) <span class="text-red-500">*</span> @endif
                            </label>

                            @if ($field['type'] === 'password')
                                <input type="password" name="gateway_{{ $slug }}_{{ $field['key'] }}"
                                       value="{{ $value }}"
                                       class="form-input"
                                       autocomplete="new-password">
                            @elseif ($field['type'] === 'textarea')
                                <textarea name="gateway_{{ $slug }}_{{ $field['key'] }}"
                                          rows="3" class="form-input">{{ $value }}</textarea>
                            @elseif ($field['type'] === 'toggle')
                                <label class="flex items-center gap-2 text-sm text-gray-700 mt-1">
                                    <input type="checkbox" name="gateway_{{ $slug }}_{{ $field['key'] }}" value="1"
                                           class="rounded text-indigo-600"
                                           {{ $value ? 'checked' : '' }}>
                                    {{ __('common.enabled') }}
                                </label>
                            @elseif ($field['type'] === 'select')
                                <select name="gateway_{{ $slug }}_{{ $field['key'] }}" class="form-input">
                                    @foreach ($field['options'] ?? [] as $optVal => $optLabel)
                                        <option value="{{ $optVal }}" {{ $value === (string)$optVal ? 'selected' : '' }}>
                                            {{ $optLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" name="gateway_{{ $slug }}_{{ $field['key'] }}"
                                       value="{{ $value }}" class="form-input">
                            @endif

                            @if (!empty($field['help']))
                                <p class="text-xs text-gray-400 mt-1">{{ $field['help'] }}</p>
                            @endif
                        </div>
                    @endforeach

                    @if ($slug === 'stripe')
                        <p class="text-xs text-gray-400">
                            {{ __('settings.stripe_webhook_url') }}:
                            <span class="font-mono select-all">{{ url('/webhooks/stripe') }}</span>
                        </p>
                    @endif
                </div>

                <div class="px-6 pb-5">
                    <x-button type="submit">{{ __('common.save_changes') }}</x-button>
                </div>
            </form>
        </div>
    @endforeach
</div>
