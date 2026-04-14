<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.email-templates.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">
                {{ __('email_templates.events.' . str_replace('.', '_', $emailTemplate->mailable)) }}
                <span class="text-gray-400 font-normal text-base ml-1">/ {{ strtoupper($emailTemplate->locale) }}</span>
            </h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.email-templates.update', $emailTemplate) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                    <div>
                        <label class="form-label">{{ __('email_templates.locale') }}</label>
                        <select name="locale" class="form-input">
                            @foreach ($locales as $code => $label)
                                <option value="{{ $code }}"
                                    {{ old('locale', $emailTemplate->locale) === $code ? 'selected' : '' }}>
                                    {{ $label }} ({{ $code }})
                                </option>
                            @endforeach
                        </select>
                        @error('locale') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <x-input name="subject" :label="__('email_templates.subject')"
                              :value="old('subject', $emailTemplate->subject)" required />

                    <div>
                        <label class="form-label">{{ __('email_templates.body') }}</label>
                        <textarea name="body" rows="20" class="form-input font-mono text-sm" style="white-space: pre; overflow-x: auto;" required>{{ old('body', $emailTemplate->body) }}</textarea>
                        @error('body') <p class="form-error">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1">{{ __('email_templates.html_note') }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('email_templates.status') }}</h3>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" class="rounded text-indigo-600"
                               {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}>
                        {{ __('email_templates.active') }}
                    </label>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('email_templates.available_variables') }}</h3>
                    <div class="space-y-1">
                        @foreach ($variables as $var)
                            <div class="text-xs font-mono text-indigo-700 bg-indigo-50 px-2 py-1 rounded cursor-pointer hover:bg-indigo-100"
                                 title="Click to copy"
                                 onclick="navigator.clipboard?.writeText('{{ $var }}')">
                                {{ $var }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-button class="w-full justify-center">{{ __('common.save_changes') }}</x-button>
            </div>
        </div>
    </form>
</x-admin-layout>
