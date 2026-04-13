<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.promo-codes.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('products.edit_promo') }}</h2>
        </div>
    </x-slot>

    @php
        $selectedProducts = old('products', $promo->products->pluck('id')->toArray());
    @endphp

    <form method="POST" action="{{ route('admin.promo-codes.update', $promo) }}" x-data="{ appliesTo: '{{ old('applies_to', $promo->applies_to) }}' }">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            {{-- Promo details --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.details') }}</h3>
                <div class="space-y-4">
                    {{-- Code --}}
                    <div>
                        <x-label for="code" :value="__('products.promo_code_field')" />
                        <input
                            type="text"
                            name="code"
                            id="code"
                            value="{{ old('code', $promo->code) }}"
                            required
                            placeholder="{{ __('products.promo_code_field') }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm uppercase"
                        />
                        <x-input-error :messages="$errors->get('code')" />
                    </div>

                    {{-- Type --}}
                    <x-select name="type" :label="__('products.promo_type')" :options="[
                        'percent' => __('products.promo_type_percent'),
                        'fixed'   => __('products.promo_type_fixed'),
                    ]" :selected="old('type', $promo->type)" required />

                    {{-- Value --}}
                    <div>
                        <x-label for="value" :value="__('products.promo_value')" />
                        <input
                            type="number"
                            name="value"
                            id="value"
                            value="{{ old('value', $displayValue) }}"
                            step="0.01"
                            min="0"
                            required
                            placeholder="0.00"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        />
                        <x-input-error :messages="$errors->get('value')" />
                    </div>

                    {{-- Recurring --}}
                    <div>
                        <x-checkbox name="recurring" value="1" :label="__('products.promo_recurring')" :checked="old('recurring', $promo->recurring)" />
                        <p class="mt-1 text-xs text-gray-500 ml-6">{{ __('products.promo_recurring_help') }}</p>
                    </div>
                </div>
            </div>

            {{-- Product scope --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('products.promo_applies_to') }}</h3>
                <div class="space-y-4">
                    <div>
                        <x-label for="applies_to" :value="__('products.promo_applies_to')" />
                        <select
                            name="applies_to"
                            id="applies_to"
                            x-model="appliesTo"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                            <option value="all">{{ __('products.promo_applies_all') }}</option>
                            <option value="specific">{{ __('products.promo_applies_specific') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('applies_to')" />
                    </div>

                    {{-- Product checkboxes --}}
                    <div x-show="appliesTo === 'specific'" x-cloak>
                        @if (!empty($products) && count($products))
                            <div class="flex flex-wrap gap-4 mt-2">
                                @foreach ($products as $product)
                                    <label class="inline-flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            name="products[]"
                                            value="{{ $product->id }}"
                                            {{ in_array($product->id, $selectedProducts) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        />
                                        <span class="text-sm text-gray-700">{{ $product->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400 mt-2">{{ __('products.no_products') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Limits and dates --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.general') }}</h3>
                <div class="space-y-4">
                    {{-- Max uses --}}
                    <div>
                        <x-label for="max_uses" :value="__('products.promo_max_uses')" />
                        <input
                            type="number"
                            name="max_uses"
                            id="max_uses"
                            value="{{ old('max_uses', $promo->max_uses) }}"
                            min="0"
                            placeholder="{{ __('common.optional') }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        />
                        <p class="mt-1 text-xs text-gray-500">{{ __('products.promo_max_uses_help') }}</p>
                        <x-input-error :messages="$errors->get('max_uses')" />
                    </div>

                    {{-- Start date --}}
                    <div>
                        <x-label for="start_date" :value="__('products.promo_start_date')" />
                        <input
                            type="date"
                            name="start_date"
                            id="start_date"
                            value="{{ old('start_date', $promo->start_date?->format('Y-m-d')) }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        />
                        <x-input-error :messages="$errors->get('start_date')" />
                    </div>

                    {{-- End date --}}
                    <div>
                        <x-label for="end_date" :value="__('products.promo_end_date')" />
                        <input
                            type="date"
                            name="end_date"
                            id="end_date"
                            value="{{ old('end_date', $promo->end_date?->format('Y-m-d')) }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        />
                        <x-input-error :messages="$errors->get('end_date')" />
                    </div>

                    {{-- Active --}}
                    <x-checkbox name="is_active" value="1" :label="__('common.active')" :checked="old('is_active', $promo->is_active)" />

                    {{-- Notes --}}
                    <x-textarea name="notes" :label="__('products.promo_notes')" :value="old('notes', $promo->notes)" rows="3" />
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('admin.promo-codes.index') }}">
                <x-secondary-button type="button">{{ __('common.cancel') }}</x-secondary-button>
            </a>
            <x-button>{{ __('common.save_changes') }}</x-button>
        </div>
    </form>
</x-admin-layout>
