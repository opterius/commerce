<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.products.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('products.create_product') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.products.store') }}">
        @csrf

        {{-- Tab navigation --}}
        <div x-data="{ tab: 'details' }">
            <div class="bg-white rounded-xl shadow-sm mb-6">
                <nav class="flex border-b border-gray-200 px-6" aria-label="Tabs">
                    <button
                        type="button"
                        @click="tab = 'details'"
                        :class="tab === 'details' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm transition-colors"
                    >
                        {{ __('products.tab_details') }}
                    </button>
                    <button
                        type="button"
                        @click="tab = 'pricing'"
                        :class="tab === 'pricing' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm transition-colors"
                    >
                        {{ __('products.tab_pricing') }}
                    </button>
                </nav>
            </div>

            {{-- ========== Details tab ========== --}}
            <div x-show="tab === 'details'" x-cloak>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Left column --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.details') }}</h3>
                        <div class="space-y-4">
                            <x-input name="name" :label="__('products.product_name')" :value="old('name')" required />

                            <div>
                                <x-input name="slug" :label="__('products.product_slug')" :value="old('slug')" />
                                <p class="mt-1 text-xs text-gray-500">{{ __('products.slug_help') }}</p>
                            </div>

                            <x-textarea name="description" :label="__('products.product_description')" :value="old('description')" rows="4" />

                            <x-select name="type" :label="__('products.product_type')" :options="[
                                'hosting' => __('products.type_hosting'),
                                'other'   => __('products.type_other'),
                            ]" :selected="old('type', 'hosting')" />

                            <x-select name="status" :label="__('products.product_status')" :options="[
                                'active'  => __('products.status_active'),
                                'hidden'  => __('products.status_hidden'),
                                'retired' => __('products.status_retired'),
                            ]" :selected="old('status', 'active')" />
                        </div>
                    </div>

                    {{-- Right column --}}
                    <div class="bg-white rounded-xl shadow-sm p-6" x-data="{ stockControl: {{ old('stock_control') ? 'true' : 'false' }} }">
                        <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.general') }}</h3>
                        <div class="space-y-4">
                            <x-select name="product_group_id" :label="__('products.product_group')" :options="$groupOptions" :selected="old('product_group_id')" />

                            <x-select name="provisioning_module" :label="__('products.provisioning_module')" :options="[
                                ''               => __('products.module_none'),
                                'opterius_panel'  => __('products.module_opterius_panel'),
                            ]" :selected="old('provisioning_module')" />

                            <div>
                                <label class="form-label">{{ __('provisioning.server_group') }}</label>
                                <select name="server_group_id" class="form-input">
                                    <option value="">— {{ __('common.none') }} —</option>
                                    @foreach ($serverGroups as $sg)
                                        <option value="{{ $sg->id }}" {{ old('server_group_id') == $sg->id ? 'selected' : '' }}>
                                            {{ $sg->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <x-input name="provisioning_package" :label="__('provisioning.provisioning_package')" :value="old('provisioning_package')" />

                            <div>
                                <label class="inline-flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="stock_control"
                                        value="1"
                                        x-model="stockControl"
                                        {{ old('stock_control') ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <span class="text-sm text-gray-700">{{ __('products.stock_control') }}</span>
                                </label>
                            </div>

                            <div x-show="stockControl" x-cloak>
                                <x-input name="qty_in_stock" type="number" :label="__('products.qty_in_stock')" :value="old('qty_in_stock', 0)" />
                            </div>

                            <x-checkbox name="require_domain" value="1" :label="__('products.require_domain')" :checked="old('require_domain')" />

                            <x-input name="sort_order" type="number" :label="__('common.sort_order')" :value="old('sort_order', 0)" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========== Pricing tab ========== --}}
            <div x-show="tab === 'pricing'" x-cloak>
                <p class="text-sm text-gray-500 mb-4">{{ __('products.pricing_help') }}</p>

                @php
                    $cycles = ['monthly', 'quarterly', 'semi_annual', 'annual', 'biennial', 'one_time'];
                @endphp

                @foreach ($currencies as $currency)
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="text-base font-semibold text-gray-800">{{ $currency->code }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.billing_cycle') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.price') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.setup_fee') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($cycles as $cycle)
                                        <tr>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-700">
                                                {{ __('products.cycle_' . $cycle) }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap">
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    name="pricing[{{ $currency->code }}][{{ $cycle }}][price]"
                                                    value="{{ old('pricing.' . $currency->code . '.' . $cycle . '.price') }}"
                                                    placeholder="0.00"
                                                    class="block w-32 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                />
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap">
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    name="pricing[{{ $currency->code }}][{{ $cycle }}][setup_fee]"
                                                    value="{{ old('pricing.' . $currency->code . '.' . $cycle . '.setup_fee') }}"
                                                    placeholder="0.00"
                                                    class="block w-32 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Configurable Options --}}
            @if (!empty($optionGroups) && count($optionGroups))
                <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('products.configurable_options') }}</h3>
                    <div class="flex flex-wrap gap-4">
                        @foreach ($optionGroups as $optionGroup)
                            <label class="inline-flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    name="option_groups[]"
                                    value="{{ $optionGroup->id }}"
                                    @checked(in_array($optionGroup->id, old('option_groups', [])))
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <span class="text-sm text-gray-700">{{ $optionGroup->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('admin.products.index') }}">
                    <x-secondary-button type="button">{{ __('common.cancel') }}</x-secondary-button>
                </a>
                <x-button>{{ __('products.create_product') }}</x-button>
            </div>
        </div>
    </form>
</x-admin-layout>
