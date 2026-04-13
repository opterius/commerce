<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.canned-responses.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('tickets.edit_canned_response') }}</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.canned-responses.update', $cannedResponse) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            @csrf
            @method('PUT')

            <x-input name="title" :label="__('tickets.response_title')" :value="old('title', $cannedResponse->title)" required />

            <div>
                <label class="form-label">{{ __('tickets.department') }}</label>
                <select name="department_id" class="form-input">
                    <option value="">{{ __('tickets.all_departments') }}</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $cannedResponse->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">{{ __('tickets.response_body') }}</label>
                <textarea name="body" rows="10" class="form-input font-mono text-sm" required>{{ old('body', $cannedResponse->body) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">{{ __('tickets.canned_variables_hint') }}: <code>{client_name}</code>, <code>{client_email}</code>, <code>{ticket_id}</code>, <code>{department}</code></p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
                <a href="{{ route('admin.canned-responses.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>
