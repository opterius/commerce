<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('dashboard.dashboard') }}</h2>
    </x-slot>

    <!-- Welcome -->
    <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-900">{{ __('dashboard.welcome_back', ['name' => auth('staff')->user()->name]) }}</h3>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Clients -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('dashboard.total_clients') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalClients }}</p>
                </div>
            </div>
        </div>

        <!-- Active Services (placeholder) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" /></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('dashboard.active_services') }}</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>

        <!-- Revenue (placeholder) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('dashboard.revenue_this_month') }}</p>
                    <p class="text-2xl font-bold text-gray-900">$0.00</p>
                </div>
            </div>
        </div>

        <!-- Open Tickets (placeholder) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('dashboard.open_tickets') }}</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-800">{{ __('dashboard.recent_activity') }}</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentActivity as $activity)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-900">
                            {{ $activity->description ?? $activity->action }}
                            @if($activity->entity_name)
                                &mdash; <span class="font-medium">{{ $activity->entity_name }}</span>
                            @endif
                        </p>
                        @if($activity->staff)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $activity->staff->name }}</p>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">
                    {{ __('dashboard.no_activity') }}
                </div>
            @endforelse
        </div>
    </div>
</x-admin-layout>
