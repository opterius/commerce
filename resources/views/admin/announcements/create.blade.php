<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.announcements.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('announcements.create') }}</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            @csrf

            <x-input name="title" :label="__('announcements.field_title')" :value="old('title')" required />
            <div>
                <x-input name="slug" :label="__('common.slug')" :value="old('slug')" placeholder="auto-generated-from-title" />
                <p class="mt-1 text-xs text-gray-400">{{ __('kb.slug_help') }}</p>
            </div>

            <div>
                <label class="form-label">{{ __('announcements.content') }} <span class="text-red-500">*</span></label>
                <textarea name="content" rows="8" class="form-input" required>{{ old('content') }}</textarea>
                <p class="mt-1 text-xs text-gray-400">{{ __('announcements.content_help') }}</p>
            </div>

            <div>
                <label class="form-label">{{ __('announcements.priority') }}</label>
                <select name="priority" class="form-input">
                    @foreach (\App\Models\Announcement::PRIORITIES as $p)
                        <option value="{{ $p }}" {{ old('priority', 'info') === $p ? 'selected' : '' }}>{{ __('announcements.priority_' . $p) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="form-label">{{ __('announcements.publish_at') }}</label>
                    <input type="datetime-local" name="published_at" class="form-input" value="{{ old('published_at') }}">
                    <p class="mt-1 text-xs text-gray-400">{{ __('announcements.publish_at_help') }}</p>
                </div>
                <div>
                    <label class="form-label">{{ __('announcements.expires_at') }}</label>
                    <input type="datetime-local" name="expires_at" class="form-input" value="{{ old('expires_at') }}">
                    <p class="mt-1 text-xs text-gray-400">{{ __('announcements.expires_at_help') }}</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 space-y-2">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded border-gray-300">
                    <span>{{ __('announcements.is_featured') }}</span>
                    <span class="text-xs text-gray-400">— {{ __('announcements.is_featured_help') }}</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="show_public" value="0">
                    <input type="checkbox" name="show_public" value="1" {{ old('show_public', '1') ? 'checked' : '' }} class="rounded border-gray-300">
                    <span>{{ __('announcements.show_public') }}</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="show_client" value="0">
                    <input type="checkbox" name="show_client" value="1" {{ old('show_client', '1') ? 'checked' : '' }} class="rounded border-gray-300">
                    <span>{{ __('announcements.show_client') }}</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('common.create') }}</button>
                <a href="{{ route('admin.announcements.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>
