<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.kb-articles.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('kb.edit_article') }}</h2>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.kb-articles.update', $article) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            @csrf
            @method('PUT')

            <x-input name="title" :label="__('kb.title')" :value="old('title', $article->title)" required />
            <x-input name="slug" :label="__('common.slug')" :value="old('slug', $article->slug)" />

            <div>
                <label class="form-label">{{ __('kb.category') }}</label>
                <select name="category_id" class="form-input">
                    <option value="">— {{ __('common.none') }} —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $article->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">{{ __('kb.excerpt') }}</label>
                <textarea name="excerpt" rows="2" class="form-input" maxlength="500">{{ old('excerpt', $article->excerpt) }}</textarea>
            </div>

            <div>
                <label class="form-label">{{ __('kb.content') }} <span class="text-red-500">*</span></label>
                <textarea name="content" rows="15" class="form-input font-mono text-sm" required>{{ old('content', $article->content) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">{{ __('kb.content_help') }}</p>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <x-input name="sort_order" type="number" :label="__('common.sort_order')" :value="old('sort_order', $article->sort_order)" min="0" />
                <div class="flex items-end pb-2">
                    <div class="text-sm text-gray-500">
                        <span class="text-gray-400">{{ __('kb.views') }}:</span>
                        <span class="font-semibold text-gray-700">{{ number_format($article->views) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $article->is_published) ? 'checked' : '' }} class="rounded border-gray-300">
                <label for="is_published" class="text-sm text-gray-700">{{ __('kb.published') }}</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
                <a href="{{ route('admin.kb-articles.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>
