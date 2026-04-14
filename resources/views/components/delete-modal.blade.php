@props([
    'name'           => null,
    'title'          => null,
    'message'        => '',
    'confirmMessage' => '',   // alias for message (used in table-row inline pattern)
    'action',
    'label'          => null, // if set, renders an inline trigger button
    'buttonClass'    => 'btn-danger py-1 px-3 text-xs',
])

@php
    $modalName = $name ?? 'delete-' . substr(md5($action), 0, 8);
    $bodyText  = $message ?: $confirmMessage;
    $titleText = $title  ?? __('common.are_you_sure');
@endphp

{{-- Inline trigger button (table-row pattern) --}}
@if ($label !== null)
    <button
        type="button"
        x-on:click="$dispatch('open-modal', '{{ $modalName }}')"
        class="{{ $buttonClass }}"
    >
        {{ $label }}
    </button>
@endif

<x-modal :name="$modalName" maxWidth="md">
    <form method="POST" action="{{ $action }}">
        @csrf
        @method('DELETE')

        <div class="p-6">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $titleText }}</h3>
                    @if ($bodyText)
                        <p class="mt-1 text-sm text-gray-600">{{ $bodyText }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <x-label for="del_pw_{{ $modalName }}" :value="__('common.confirm_password')" />
                <input
                    type="password"
                    name="password"
                    id="del_pw_{{ $modalName }}"
                    required
                    autocomplete="current-password"
                    placeholder="{{ __('common.enter_password') }}"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                />
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-xl">
            <x-secondary-button
                type="button"
                x-on:click="$dispatch('close-modal', '{{ $modalName }}')"
            >
                {{ __('common.cancel') }}
            </x-secondary-button>

            <x-danger-button type="submit">
                {{ __('common.confirm_delete') }}
            </x-danger-button>
        </div>
    </form>
</x-modal>
