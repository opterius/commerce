@props([
    'name',
    'title',
    'action',
    'message' => '',
])

<x-modal :name="$name" maxWidth="md">
    <form method="POST" action="{{ $action }}">
        @csrf
        @method('DELETE')

        <div class="p-6">
            {{-- Header --}}
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                    @if ($message)
                        <p class="mt-1 text-sm text-gray-600">{{ $message }}</p>
                    @endif
                </div>
            </div>

            {{-- Password confirmation --}}
            <div class="mt-6">
                <x-label for="delete_password_{{ $name }}" :value="__('common.confirm_password')" />
                <input
                    type="password"
                    name="password"
                    id="delete_password_{{ $name }}"
                    required
                    autocomplete="current-password"
                    placeholder="{{ __('common.enter_password') }}"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                />
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-xl">
            <x-secondary-button
                type="button"
                x-on:click="$dispatch('close-modal', '{{ $name }}')"
            >
                {{ __('common.cancel') }}
            </x-secondary-button>

            <x-danger-button type="submit">
                {{ __('common.confirm_delete') }}
            </x-danger-button>
        </div>
    </form>
</x-modal>
