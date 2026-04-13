@php
    $flashTypes = [
        'success' => [
            'bg'   => 'bg-green-50 border-green-200',
            'text'  => 'text-green-800',
            'icon'  => 'text-green-400',
            'svg'   => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        ],
        'error' => [
            'bg'   => 'bg-red-50 border-red-200',
            'text'  => 'text-red-800',
            'icon'  => 'text-red-400',
            'svg'   => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
        ],
        'warning' => [
            'bg'   => 'bg-amber-50 border-amber-200',
            'text'  => 'text-amber-800',
            'icon'  => 'text-amber-400',
            'svg'   => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        ],
        'info' => [
            'bg'   => 'bg-blue-50 border-blue-200',
            'text'  => 'text-blue-800',
            'icon'  => 'text-blue-400',
            'svg'   => 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
        ],
    ];
@endphp

@foreach ($flashTypes as $type => $styles)
    @if (session($type))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="mx-6 mt-6 rounded-lg border px-4 py-3 {{ $styles['bg'] }}"
        >
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0 {{ $styles['icon'] }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $styles['svg'] }}" />
                </svg>
                <p class="flex-1 text-sm font-medium {{ $styles['text'] }}">
                    {{ session($type) }}
                </p>
                <button @click="show = false" class="{{ $styles['text'] }} hover:opacity-75">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif
@endforeach
