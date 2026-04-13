<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.email-templates.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('email_templates.add_translation') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.email-templates.store') }}">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">{{ __('email_templates.event') }}</label>
                            <select name="mailable" class="form-input" id="mailable_select" required>
                                <option value="">— {{ __('common.select') }} —</option>
                                @foreach ($mailables as $key => $vars)
                                    <option value="{{ $key }}" {{ old('mailable') === $key ? 'selected' : '' }}
                                            data-vars="{{ implode(', ', $vars) }}">
                                        {{ __('email_templates.events.' . str_replace('.', '_', $key)) }} ({{ $key }})
                                    </option>
                                @endforeach
                            </select>
                            @error('mailable') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('email_templates.locale') }}</label>
                            <select name="locale" class="form-input" required>
                                @foreach ($locales as $code => $label)
                                    <option value="{{ $code }}" {{ old('locale', 'en') === $code ? 'selected' : '' }}>
                                        {{ $label }} ({{ $code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('locale') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <x-input name="subject" :label="__('email_templates.subject')"
                              :value="old('subject')" required />

                    <div>
                        <label class="form-label">{{ __('email_templates.body') }}</label>
                        <textarea name="body" rows="16" class="form-input font-mono text-sm" required>{{ old('body') }}</textarea>
                        @error('body') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('email_templates.status') }}</h3>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" class="rounded text-indigo-600"
                               {{ old('is_active', '1') ? 'checked' : '' }}>
                        {{ __('email_templates.active') }}
                    </label>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6" id="variables_box">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('email_templates.available_variables') }}</h3>
                    <p class="text-xs text-gray-400 italic" id="vars_hint">{{ __('email_templates.select_event_hint') }}</p>
                    <div id="vars_list" class="space-y-1 hidden"></div>
                </div>

                <x-button class="w-full justify-center">{{ __('common.save_changes') }}</x-button>
            </div>
        </div>
    </form>
</x-admin-layout>

@push('scripts')
<script>
document.getElementById('mailable_select').addEventListener('change', function () {
    const vars = this.options[this.selectedIndex]?.dataset?.vars || '';
    const list = document.getElementById('vars_list');
    const hint = document.getElementById('vars_hint');

    list.innerHTML = '';
    if (vars) {
        vars.split(', ').forEach(v => {
            const el = document.createElement('div');
            el.className = 'text-xs font-mono text-indigo-700 bg-indigo-50 px-2 py-1 rounded cursor-pointer hover:bg-indigo-100';
            el.textContent = v;
            el.title = 'Click to copy';
            el.addEventListener('click', () => navigator.clipboard?.writeText(v));
            list.appendChild(el);
        });
        list.classList.remove('hidden');
        hint.classList.add('hidden');
    } else {
        list.classList.add('hidden');
        hint.classList.remove('hidden');
    }
});
</script>
@endpush
