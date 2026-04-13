@props([
    'name',
    'maxWidth' => 'lg',
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm'  => 'sm:max-w-sm',
        'md'  => 'sm:max-w-md',
        'lg'  => 'sm:max-w-lg',
        'xl'  => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        default => 'sm:max-w-lg',
    };
@endphp

<div
    x-data="{ show: false, name: '{{ $name }}' }"
    x-show="show"
    x-on:open-modal.window="show = ($event.detail === name)"
    x-on:close-modal.window="show = ($event.detail === name || !$event.detail) ? false : show"
    x-on:keydown.escape.window="show = false"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="show = false"></div>

    {{-- Modal panel --}}
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="w-full {{ $maxWidthClass }} bg-white rounded-xl shadow-xl transform transition-all"
            >
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
