<div class="space-y-6">
    {{-- Existing currencies table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800">{{ __('settings.currencies') }}</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('settings.currency_code') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('settings.currency_name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('settings.currency_symbol') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('settings.currency_exchange_rate') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('settings.currency_default') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('settings.currency_active') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($currencies as $currency)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-medium text-gray-900">{{ $currency->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $currency->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $currency->symbol }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $currency->exchange_rate }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge :color="$currency->is_default ? 'indigo' : 'gray'">
                                    {{ $currency->is_default ? __('common.yes') : __('common.no') }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge :color="$currency->is_active ? 'green' : 'gray'">
                                    {{ $currency->is_active ? __('common.active') : __('common.inactive') }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                @unless ($currency->is_default)
                                    <form method="POST" action="{{ route('admin.settings.currencies.destroy', $currency) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">{{ __('common.delete') }}</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add currency form --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('settings.add_currency') }}</h3>

        <form method="POST" action="{{ route('admin.settings.update', 'currencies') }}">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <x-input name="code" :label="__('settings.currency_code')" :value="old('code')" placeholder="GBP" required class="uppercase" />
                <x-input name="name" :label="__('settings.currency_name')" :value="old('name')" placeholder="British Pound" required />
                <x-input name="symbol" :label="__('settings.currency_symbol')" :value="old('symbol')" required />
                <x-input name="prefix" :label="__('settings.currency_prefix')" :value="old('prefix')" />
                <x-input name="suffix" :label="__('settings.currency_suffix')" :value="old('suffix')" />
                <x-input name="decimal_places" type="number" :label="__('settings.currency_decimals')" :value="old('decimal_places', '2')" />
                <x-input name="exchange_rate" type="number" :label="__('settings.currency_exchange_rate')" :value="old('exchange_rate', '1.000000')" step="0.000001" min="0" />
            </div>

            <div class="mt-4 flex items-center gap-6">
                <x-checkbox name="is_default" value="1" :label="__('settings.currency_default')" :checked="old('is_default')" />
                <x-checkbox name="is_active" value="1" :label="__('settings.currency_active')" :checked="old('is_active', true)" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-button>{{ __('settings.add_currency') }}</x-button>
            </div>
        </form>
    </div>
</div>
