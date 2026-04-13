@props([
    'align' => 'right',
    'width' => '48',
])

@php
    $alignmentClasses = match($align) {
        'left'  => 'left-0 origin-top-left',
        'right' => 'right-0 origin-top-right',
        default => 'right-0 origin-top-right',
    };

    $widthClass = match($width) {
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        default => 'w-48',
    };
@endphp

<div class="relative" x-data="{ open: false }">
    {{-- Trigger --}}
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    {{-- Content --}}
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $widthClass }} {{ $alignmentClasses }} bg-white rounded-lg shadow-lg border border-gray-200 py-1"
        style="display: none;"
    >
        {{ $content }}
    </div>
</div>
