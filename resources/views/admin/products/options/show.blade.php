<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.configurable-options.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <h2 class="text-lg font-semibold text-gray-800">{{ $group->name }}</h2>
            </div>
            <x-danger-button
                type="button"
                x-data=""
                x-on:click="$dispatch('open-modal', 'delete-group')"
            >
                {{ __('common.delete') }}
            </x-danger-button>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Card 1: Group details (inline edit) --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.details') }}</h3>

            <form method="POST" action="{{ route('admin.configurable-options.update', $group) }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <x-input name="name" :label="__('products.option_group_name')" :value="old('name', $group->name)" required />
                    <x-textarea name="description" :label="__('common.description')" :value="old('description', $group->description)" rows="3" />

                    {{-- Linked products --}}
                    <div>
                        <x-label value="{{ __('products.linked_products') }}" />
                        @if (!empty($products) && count($products))
                            @php
                                $selectedProducts = old('products', $group->products->pluck('id')->toArray());
                            @endphp
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

                <div class="flex items-center justify-end mt-6">
                    <x-button>{{ __('common.save_changes') }}</x-button>
                </div>
            </form>
        </div>

        {{-- Card 2: Options list --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-6">{{ __('products.options') }}</h3>

            @if ($group->options && $group->options->count())
                <div class="space-y-6">
                    @foreach ($group->options as $option)
                        <div class="border border-gray-200 rounded-lg p-4">
                            {{-- Option header --}}
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-gray-900">{{ $option->name }}</span>
                                    @php
                                        $typeColor = match($option->option_type) {
                                            'dropdown' => 'blue',
                                            'radio'    => 'indigo',
                                            'checkbox' => 'green',
                                            'quantity' => 'amber',
                                            default    => 'gray',
                                        };
                                    @endphp
                                    <x-badge :color="$typeColor">{{ __('products.option_type_' . $option->option_type) }}</x-badge>
                                </div>
                                <form method="POST" action="{{ route('admin.configurable-options.options.destroy', [$group, $option]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-gray-400 hover:text-red-600 transition-colors"
                                        title="{{ __('common.delete') }}"
                                    >
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            {{-- Option values --}}
                            @if ($option->values && $option->values->count())
                                <div class="mb-4">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">{{ __('products.option_values') }}</p>
                                    <div class="space-y-2">
                                        @foreach ($option->values as $value)
                                            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                                                <span class="text-sm text-gray-700">{{ $value->label }}</span>
                                                <form method="POST" action="{{ route('admin.configurable-options.values.destroy', [$option, $value]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="text-gray-400 hover:text-red-600 transition-colors"
                                                        title="{{ __('common.delete') }}"
                                                    >
                                                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Add value form --}}
                            <form method="POST" action="{{ route('admin.configurable-options.values.store', $option) }}" class="flex items-end gap-3">
                                @csrf
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('products.value_label') }}</label>
                                    <input
                                        type="text"
                                        name="label"
                                        required
                                        placeholder="{{ __('products.value_label') }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    />
                                </div>
                                <x-button class="!py-2">{{ __('products.add_value') }}</x-button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 mb-6">{{ __('common.no_results') }}</p>
            @endif

            {{-- Add option form --}}
            <div class="mt-6 border-t border-gray-200 pt-6">
                <h4 class="text-sm font-semibold text-gray-800 mb-4">{{ __('products.add_option') }}</h4>

                <form method="POST" action="{{ route('admin.configurable-options.options.store', $group) }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('products.option_name') }}</label>
                            <input
                                type="text"
                                name="name"
                                required
                                placeholder="{{ __('products.option_name') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('products.option_type') }}</label>
                            <select
                                name="option_type"
                                required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            >
                                <option value="dropdown">{{ __('products.option_type_dropdown') }}</option>
                                <option value="radio">{{ __('products.option_type_radio') }}</option>
                                <option value="checkbox">{{ __('products.option_type_checkbox') }}</option>
                                <option value="quantity">{{ __('products.option_type_quantity') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('products.group_sort_order') }}</label>
                            <input
                                type="number"
                                name="sort_order"
                                value="0"
                                min="0"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            />
                        </div>
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <x-button>{{ __('products.add_option') }}</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete group modal --}}
    @push('modals')
        <x-delete-modal
            name="delete-group"
            :title="__('common.are_you_sure')"
            :message="__('common.this_action_cannot_be_undone')"
            :action="route('admin.configurable-options.destroy', $group)"
        />
    @endpush
</x-admin-layout>
