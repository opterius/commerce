@php
    $priorityStyles = [
        'info'     => ['accent' => '#3b82f6', 'label' => 'bg-blue-100 text-blue-700'],
        'success'  => ['accent' => '#10b981', 'label' => 'bg-green-100 text-green-700'],
        'warning'  => ['accent' => '#f59e0b', 'label' => 'bg-amber-100 text-amber-700'],
        'critical' => ['accent' => '#ef4444', 'label' => 'bg-red-100 text-red-700'],
    ];
    $s = $priorityStyles[$announcement->priority] ?? $priorityStyles['info'];
@endphp

<x-portal-layout>

    <article class="max-w-3xl mx-auto" style="padding: 3rem 1.5rem;">

        <nav class="text-sm text-gray-400 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('portal.announcements') }}" class="hover:text-gray-700">{{ __('announcements.portal_title') }}</a>
        </nav>

        <div class="mb-4 flex items-center gap-3">
            <span class="text-xs px-2.5 py-1 rounded-full {{ $s['label'] }}">{{ __('announcements.priority_' . $announcement->priority) }}</span>
            <span class="text-sm text-gray-400">{{ $announcement->published_at->format('M j, Y · g:i A') }}</span>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-6 leading-tight">{{ $announcement->title }}</h1>

        <div class="bg-white rounded-2xl border border-gray-200 p-7" style="border-left: 4px solid {{ $s['accent'] }};">
            <div class="text-gray-700 leading-relaxed" style="font-size: 1rem; white-space: pre-wrap;">{!! nl2br(e($announcement->content)) !!}</div>
        </div>

        <div class="mt-8">
            <a href="{{ route('portal.announcements') }}" class="text-sm text-gray-500 hover:text-gray-800">← {{ __('announcements.back_to_list') }}</a>
        </div>
    </article>

</x-portal-layout>
