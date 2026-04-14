<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.client-groups.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('clients.create_group') }}</h2>
        </div>
    </x-slot>

    <div class="max-w-xl">
        <form method="POST" action="{{ route('admin.client-groups.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            @csrf

            <x-input name="name" :label="__('common.name')" :value="old('name')" required />

            <div>
                <label class="form-label" for="color">{{ __('clients.group_color') }}</label>
                <div class="mt-1 flex items-center gap-3">
                    <input type="color" name="color" id="color" value="{{ old('color', '#6366f1') }}" class="h-10 w-16 rounded border-gray-300 cursor-pointer">
                    <span class="text-sm text-gray-500">{{ old('color', '#6366f1') }}</span>
                </div>
            </div>

            <div>
                <label class="form-label">{{ __('common.description') }}</label>
                <textarea name="description" rows="2" class="form-input">{{ old('description') }}</textarea>
            </div>

            <div>
                <x-input name="discount_percent" type="number" step="0.01" min="0" max="100"
                    :label="__('clients.discount_percent')" :value="old('discount_percent', 0)" />
                <p class="mt-1 text-xs text-gray-400">{{ __('clients.discount_percent_help') }}</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('common.create') }}</button>
                <a href="{{ route('admin.client-groups.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>
