<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.products.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <h2 class="text-lg font-semibold text-gray-800">{{ $product->name }}</h2>
                @php
                    $statusColor = match($product->status) {
                        'active'  => 'green',
                        'hidden'  => 'amber',
                        'retired' => 'red',
                        default   => 'gray',
                    };
                @endphp
                <x-badge :color="$statusColor">{{ __('products.status_' . $product->status) }}</x-badge>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.products.edit', $product) }}">
                    <x-secondary-button type="button">{{ __('common.edit') }}</x-secondary-button>
                </a>
                <x-danger-button
                    type="button"
                    x-data=""
                    x-on:click="$dispatch('open-modal', 'delete-product')"
                >
                    {{ __('common.delete') }}
                </x-danger-button>
            </div>
        </div>
    </x-slot>

    {{-- Tabs --}}
    <div x-data="{ tab: '{{ request('tab', 'details') }}' }">
        {{-- Tab navigation --}}
        <div class="bg-white rounded-xl shadow-sm mb-6">
            <nav class="flex border-b border-gray-200 px-6" aria-label="Tabs">
                @foreach (['details', 'pricing', 'options'] as $t)
                    <button
                        type="button"
                        @click="tab = '{{ $t }}'"
                        :class="tab === '{{ $t }}' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm transition-colors"
                    >
                        {{ __('products.tab_' . $t) }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- ========== Details tab ========== --}}
        <div x-show="tab === 'details'" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Product info --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('products.product') }}</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('products.product_name') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $product->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('products.product_slug') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $product->slug }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('products.product_description') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 text-right max-w-xs">{{ $product->description ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('products.product_type') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                <x-badge color="blue">{{ __('products.type_' . $product->type) }}</x-badge>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('products.product_status') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                <x-badge :color="$statusColor">{{ __('products.status_' . $product->status) }}</x-badge>
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Settings --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.general') }}</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('products.product_group') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                @if ($product->productGroup)
                                    <x-badge color="indigo">{{ $product->productGroup->name }}</x-badge>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('products.provisioning_module') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $product->provisioning_module ? __('products.module_opterius_panel') : __('products.module_none') }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('products.stock_control') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                @if ($product->stock_control)
                                    {{ __('common.yes') }} ({{ $product->qty_in_stock }})
                                @else
                                    {{ __('common.no') }}
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('products.require_domain') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $product->require_domain ? __('common.yes') : __('common.no') }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('common.sort_order') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $product->sort_order }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('common.created_at') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $product->created_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- ========== Pricing tab ========== --}}
        <div x-show="tab === 'pricing'" x-cloak>
            @php
                $cycles = ['monthly', 'quarterly', 'semi_annual', 'annual', 'biennial', 'one_time'];
                $hasPricing = false;
            @endphp

            @foreach ($currencies as $currency)
                @php
                    $currencyPricing = $product->pricing->where('currency_code', $currency->code);
                    $activeCycles = $currencyPricing->filter(fn($p) => $p->price !== null);
                    if ($activeCycles->count()) $hasPricing = true;
                @endphp

                @if ($activeCycles->count())
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
                                    @foreach ($activeCycles as $pricing)
                                        <tr>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-700">
                                                {{ __('products.cycle_' . $pricing->billing_cycle) }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($pricing->price / 100, 2) }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">
                                                {{ $pricing->setup_fee ? number_format($pricing->setup_fee / 100, 2) : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach

            @if (!$hasPricing)
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <p class="text-sm text-gray-400">{{ __('products.no_pricing_set') }}</p>
                </div>
            @endif
        </div>

        {{-- ========== Options tab ========== --}}
        <div x-show="tab === 'options'" x-cloak>
            @if ($product->configurableOptionGroups && $product->configurableOptionGroups->count())
                <div class="space-y-6">
                    @foreach ($product->configurableOptionGroups as $optionGroup)
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-4">{{ $optionGroup->name }}</h3>

                            @if ($optionGroup->options && $optionGroup->options->count())
                                <div class="space-y-4">
                                    @foreach ($optionGroup->options as $option)
                                        <div class="border border-gray-100 rounded-lg p-4">
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="text-sm font-medium text-gray-900">{{ $option->name }}</span>
                                                <x-badge color="gray">{{ __('products.option_type_' . $option->input_type) }}</x-badge>
                                            </div>

                                            @if ($option->values && $option->values->count())
                                                <div class="ml-4 space-y-1">
                                                    @foreach ($option->values as $value)
                                                        <div class="flex items-center justify-between text-sm">
                                                            <span class="text-gray-600">{{ $value->label }}</span>
                                                            @if ($value->price)
                                                                <span class="text-gray-500">{{ number_format($value->price / 100, 2) }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <p class="text-sm text-gray-400">{{ __('products.no_linked_options') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Delete product modal --}}
    @push('modals')
        <x-delete-modal
            name="delete-product"
            :title="__('products.delete_product')"
            :message="__('products.delete_product_confirm')"
            :action="route('admin.products.destroy', $product)"
        />
    @endpush
</x-admin-layout>
