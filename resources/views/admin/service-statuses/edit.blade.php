<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.service-statuses.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('announcements.edit_component') }}</h2>
        </div>
    </x-slot>

    <div class="max-w-xl">
        <form method="POST" action="{{ route('admin.service-statuses.update', $component) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            @csrf
            @method('PUT')

            <x-input name="name" :label="__('common.name')" :value="old('name', $component->name)" required />

            <div>
                <label class="form-label">{{ __('common.description') }}</label>
                <textarea name="description" rows="3" class="form-input">{{ old('description', $component->description) }}</textarea>
            </div>

            <div>
                <label class="form-label">{{ __('announcements.status') }}</label>
                <select name="status" class="form-input">
                    @foreach (\App\Models\ServiceStatus::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status', $component->status) === $s ? 'selected' : '' }}>{{ __('announcements.status_' . $s) }}</option>
                    @endforeach
                </select>
            </div>

            <x-input name="sort_order" type="number" :label="__('common.sort_order')" :value="old('sort_order', $component->sort_order)" min="0" />

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
                <a href="{{ route('admin.service-statuses.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>
