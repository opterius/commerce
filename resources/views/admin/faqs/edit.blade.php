<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.faqs.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('faq.edit') }}</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.faqs.update', $faq) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="form-label">{{ __('faq.question') }} <span class="text-red-500">*</span></label>
                <textarea name="question" rows="2" class="form-input" maxlength="500" required>{{ old('question', $faq->question) }}</textarea>
            </div>

            <div>
                <label class="form-label">{{ __('faq.answer') }} <span class="text-red-500">*</span></label>
                <textarea name="answer" rows="6" class="form-input" required>{{ old('answer', $faq->answer) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">{{ __('faq.answer_help') }}</p>
            </div>

            <x-input name="sort_order" type="number" :label="__('common.sort_order')" :value="old('sort_order', $faq->sort_order)" min="0" />

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $faq->is_published) ? 'checked' : '' }} class="rounded border-gray-300">
                <label for="is_published" class="text-sm text-gray-700">{{ __('kb.published') }}</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
                <a href="{{ route('admin.faqs.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>
