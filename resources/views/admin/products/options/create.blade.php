<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.configurable-options.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('products.create_option_group') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.configurable-options.store') }}">
        @csrf

        <div class="space-y-6">
            {{-- Group details --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.details') }}</h3>
                <div class="space-y-4">
                    <x-input name="name" :label="__('products.option_group_name')" :value="old('name')" required />
                    <x-textarea name="description" :label="__('common.description')" :value="old('description')" rows="3" />
                </div>
            </div>

            {{-- Linked products --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('products.linked_products') }}</h3>
                @if (!empty($products) && count($products))
                    <div class="flex flex-wrap gap-4">
                        @foreach ($products as $product)
                            <label class="inline-flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    name="products[]"
                                    value="{{ $product->id }}"
                                    @checked(in_array($product->id, old('products', [])))
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <span class="text-sm text-gray-700">{{ $product->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400">{{ __('products.no_products') }}</p>
                @endif
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('admin.configurable-options.index') }}">
                <x-secondary-button type="button">{{ __('common.cancel') }}</x-secondary-button>
            </a>
            <x-button>{{ __('products.create_option_group') }}</x-button>
        </div>
    </form>
</x-admin-layout>
