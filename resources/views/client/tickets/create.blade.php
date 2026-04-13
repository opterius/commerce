<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('client.tickets.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('tickets.open_ticket') }}</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('client.tickets.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            @csrf

            <div>
                <label class="form-label">{{ __('tickets.department') }} <span class="text-red-500">*</span></label>
                <select name="department_id" class="form-input" required>
                    <option value="">{{ __('common.select') }}…</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                            @if ($dept->description)
                                — {{ $dept->description }}
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <x-input name="subject" :label="__('tickets.subject')" :value="old('subject')" required />

            <div>
                <label class="form-label">{{ __('tickets.message') }} <span class="text-red-500">*</span></label>
                <textarea name="body" rows="8" class="form-input" required placeholder="{{ __('tickets.message_placeholder') }}">{{ old('body') }}</textarea>
                @error('body')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label">{{ __('tickets.attachments') }} <span class="text-gray-400 text-xs font-normal">({{ __('common.optional') }})</span></label>
                <input type="file" name="attachments[]" multiple
                       class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('tickets.submit_ticket') }}</button>
                <a href="{{ route('client.tickets.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </div>
</x-client-layout>
