@php
    $priorityStyles = [
        'info'     => ['border' => 'border-blue-200',  'accent' => '#3b82f6', 'label' => 'bg-blue-100 text-blue-700'],
        'success'  => ['border' => 'border-green-200', 'accent' => '#10b981', 'label' => 'bg-green-100 text-green-700'],
        'warning'  => ['border' => 'border-amber-200', 'accent' => '#f59e0b', 'label' => 'bg-amber-100 text-amber-700'],
        'critical' => ['border' => 'border-red-200',   'accent' => '#ef4444', 'label' => 'bg-red-100 text-red-700'],
    ];
@endphp

<x-portal-layout>

    <section class="portal-hero" style="padding: 4rem 1.5rem;">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-white font-extrabold" style="font-size: clamp(1.75rem, 4vw, 2.5rem)">{{ __('announcements.portal_title') }}</h1>
            <p style="color: rgba(255,255,255,.78); margin-top: .75rem; font-size: 1rem;">{{ __('announcements.portal_subtitle') }}</p>
        </div>
    </section>

    <section class="max-w-3xl mx-auto" style="padding: 3rem 1.5rem;">
        @if ($announcements->isEmpty())
            <p class="text-center text-sm text-gray-400 py-12">{{ __('announcements.no_announcements_yet') }}</p>
        @else
            <div class="space-y-4">
                @foreach ($announcements as $ann)
                    @php $s = $priorityStyles[$ann->priority] ?? $priorityStyles['info']; @endphp
                    <a href="{{ route('portal.announcement', $ann) }}"
                       class="block bg-white rounded-xl border {{ $s['border'] }} p-5 hover:shadow-md transition"
                       style="border-left: 4px solid {{ $s['accent'] }};">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <h2 class="font-semibold text-gray-900 text-base">{{ $ann->title }}</h2>
                            <span class="text-xs px-2 py-1 rounded-full {{ $s['label'] }} whitespace-nowrap">{{ __('announcements.priority_' . $ann->priority) }}</span>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed" style="white-space: pre-wrap;">{{ Str::limit($ann->content, 180) }}</p>
                        <p class="mt-3 text-xs text-gray-400">{{ $ann->published_at->format('M j, Y · g:i A') }}</p>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $announcements->links() }}</div>
        @endif
    </section>

</x-portal-layout>
