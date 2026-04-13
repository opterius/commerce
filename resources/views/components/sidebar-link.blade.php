@props([
    'href' => '#',
    'active' => false,
    'disabled' => false,
])

@php
    $classes = match(true) {
        $disabled => 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 cursor-not-allowed opacity-50',
        $active   => 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium bg-gray-800 text-white',
        default   => 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-colors duration-150',
    };
@endphp

@if ($disabled)
    <span {{ $attributes->merge(['class' => $classes]) }}>
        {{ $icon }}
        <span class="flex-1">{{ $slot }}</span>
        <x-badge color="gray">{{ __('common.soon') }}</x-badge>
    </span>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $icon }}
        <span class="flex-1">{{ $slot }}</span>
    </a>
@endif
