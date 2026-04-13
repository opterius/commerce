<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.product-groups.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('products.create_group') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.product-groups.store') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.details') }}</h3>
            <div class="space-y-4">
                <x-input name="name" :label="__('products.group_name')" :value="old('name')" required />

                <div>
                    <x-input name="slug" :label="__('products.group_slug')" :value="old('slug')" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('products.slug_help') }}</p>
                </div>

                <x-textarea name="description" :label="__('products.group_description')" :value="old('description')" rows="3" />

                <x-input name="sort_order" type="number" :label="__('products.group_sort_order')" :value="old('sort_order', 0)" />

                <x-checkbox name="is_visible" value="1" :label="__('products.group_visible')" :checked="old('is_visible', true)" />
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('admin.product-groups.index') }}">
                <x-secondary-button type="button">{{ __('common.cancel') }}</x-secondary-button>
            </a>
            <x-button>{{ __('products.create_group') }}</x-button>
        </div>
    </form>
</x-admin-layout>
