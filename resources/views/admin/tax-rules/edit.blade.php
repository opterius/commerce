<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.tax-rules.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('tax_rules.edit') }}: {{ $taxRule->name }}</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.tax-rules.update', $taxRule) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-input name="name" :label="__('common.name')"
                    :value="old('name', $taxRule->name)" required maxlength="100" />

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="country_code" :value="__('tax_rules.country_code')" />
                        <input type="text" id="country_code" name="country_code"
                            value="{{ old('country_code', $taxRule->country_code) }}"
                            maxlength="2" placeholder="US"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm uppercase"
                            required />
                        <x-input-error :messages="$errors->get('country_code')" class="mt-1" />
                    </div>
                    <div>
                        <x-label for="state_code" :value="__('tax_rules.state_code') . ' (' . __('common.optional') . ')'" />
                        <input type="text" id="state_code" name="state_code"
                            value="{{ old('state_code', $taxRule->state_code) }}"
                            maxlength="10" placeholder="CA"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        <x-input-error :messages="$errors->get('state_code')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-label for="rate" :value="__('tax_rules.rate')" />
                    <div class="mt-1 flex rounded-lg shadow-sm">
                        <input type="number" id="rate" name="rate"
                            value="{{ old('rate', $taxRule->rate) }}"
                            step="0.01" min="0" max="100"
                            class="block w-full rounded-l-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required />
                        <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">%</span>
                    </div>
                    <x-input-error :messages="$errors->get('rate')" class="mt-1" />
                </div>

                <div>
                    <x-label for="applies_to" :value="__('tax_rules.applies_to')" />
                    <select id="applies_to" name="applies_to"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @foreach (['all', 'hosting', 'one_time'] as $opt)
                            <option value="{{ $opt }}" @selected(old('applies_to', $taxRule->applies_to) === $opt)>
                                {{ __('tax_rules.applies_' . $opt) }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('applies_to')" class="mt-1" />
                </div>

                <x-input name="sort_order" type="number" :label="__('common.sort_order')"
                    :value="old('sort_order', $taxRule->sort_order)" min="0" />

                <div class="space-y-3 pt-2">
                    <x-checkbox name="is_eu_tax" value="1"
                        :checked="old('is_eu_tax', $taxRule->is_eu_tax)"
                        :label="__('tax_rules.is_eu_tax')" />
                    <x-checkbox name="is_active" value="1"
                        :checked="old('is_active', $taxRule->is_active)"
                        :label="__('common.active')" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.tax-rules.index') }}">
                        <x-secondary-button type="button">{{ __('common.cancel') }}</x-secondary-button>
                    </a>
                    <x-button type="submit">{{ __('common.save_changes') }}</x-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
