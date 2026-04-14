@php
    $statusStyles = [
        'operational' => ['dot' => '#10b981', 'bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-700'],
        'degraded'    => ['dot' => '#f59e0b', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700'],
        'outage'      => ['dot' => '#ef4444', 'bg' => 'bg-red-50',   'border' => 'border-red-200',   'text' => 'text-red-700'],
        'maintenance' => ['dot' => '#3b82f6', 'bg' => 'bg-blue-50',  'border' => 'border-blue-200',  'text' => 'text-blue-700'],
    ];
    $overallHero = [
        'operational' => ['title' => __('announcements.all_systems_operational'), 'subtitle' => __('announcements.all_systems_subtitle')],
        'degraded'    => ['title' => __('announcements.some_issues'),              'subtitle' => __('announcements.degraded_subtitle')],
        'outage'      => ['title' => __('announcements.major_outage'),             'subtitle' => __('announcements.outage_subtitle')],
        'maintenance' => ['title' => __('announcements.under_maintenance'),        'subtitle' => __('announcements.maintenance_subtitle')],
    ];
    $o = $overallHero[$overall] ?? $overallHero['operational'];
    $s = $statusStyles[$overall] ?? $statusStyles['operational'];
@endphp

<x-portal-layout>

    <section class="{{ $s['bg'] }} border-b border-gray-100" style="padding: 4rem 1.5rem;">
        <div class="max-w-3xl mx-auto text-center">
            <div class="inline-flex items-center gap-3 mb-4">
                <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $s['dot'] }}; box-shadow: 0 0 0 6px {{ $s['dot'] }}22;"></span>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $o['title'] }}</h1>
            </div>
            <p class="text-sm text-gray-500 leading-relaxed">{{ $o['subtitle'] }}</p>
        </div>
    </section>

    <section class="max-w-3xl mx-auto" style="padding: 3rem 1.5rem;">

        {{-- Components --}}
        @if ($components->isEmpty())
            <p class="text-center text-sm text-gray-400 py-12">{{ __('announcements.no_components_yet') }}</p>
        @else
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-10">
                @foreach ($components as $c)
                    @php $cs = $statusStyles[$c->status] ?? $statusStyles['operational']; @endphp
                    <div class="flex items-center justify-between gap-4 px-5 py-4 {{ ! $loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 text-sm">{{ $c->name }}</p>
                            @if ($c->description)
                                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $c->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $cs['dot'] }};"></span>
                            <span class="text-xs font-medium {{ $cs['text'] }}">{{ __('announcements.status_' . $c->status) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Recent incidents --}}
        @if ($recentIncidents->isNotEmpty())
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('announcements.recent_incidents') }}</h2>
            <div class="space-y-3">
                @foreach ($recentIncidents as $inc)
                    <a href="{{ route('portal.announcement', $inc) }}"
                       class="block bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 text-sm">{{ $inc->title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $inc->published_at->diffForHumans() }}</p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full whitespace-nowrap {{ $inc->priority === 'critical' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ __('announcements.priority_' . $inc->priority) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </section>

</x-portal-layout>
