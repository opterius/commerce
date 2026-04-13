<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('navigation.reports') }}</h2>
            <a href="{{ route('admin.reports.export-csv') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export CSV
            </a>
        </div>
    </x-slot>

    {{-- Top stats cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        {{-- MRR --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Monthly Recurring Revenue</p>
                    @if (count($mrr) === 0)
                        <p class="text-2xl font-bold text-gray-900">—</p>
                    @else
                        @foreach ($mrr as $currency => $amount)
                            <p class="text-xl font-bold text-gray-900">{{ $currency }} {{ number_format($amount / 100, 2) }}</p>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- Total revenue --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Revenue (All Time)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalRevenue / 100, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Overdue --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Overdue Invoices</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $overdueCount }}</p>
                    @if ($overdueAmount > 0)
                        <p class="text-xs text-red-600">{{ number_format($overdueAmount / 100, 2) }} outstanding</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Active services --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Services</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeServicesCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue bar chart (last 12 months) --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <h3 class="text-base font-semibold text-gray-800 mb-6">Revenue — Last 12 Months</h3>

        <div x-data="{}" class="flex items-end gap-2 h-48 border-b border-gray-200 pb-0">
            @foreach ($revenueData as $month)
                @php
                    $pct = $maxRevenue > 0 ? round(($month['amount'] / $maxRevenue) * 100) : 0;
                    $label = number_format($month['amount'] / 100, 2);
                @endphp
                <div class="flex-1 flex flex-col items-center group">
                    <div class="relative w-full flex flex-col justify-end" style="height: 160px;">
                        <div
                            title="{{ $month['label'] }}: {{ $label }}"
                            style="height: {{ max(2, $pct) }}%;"
                            class="w-full bg-indigo-500 rounded-t group-hover:bg-indigo-600 transition-colors cursor-default"
                        ></div>
                        <span class="absolute -top-6 left-0 right-0 text-center text-xs text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            {{ $label }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 text-center leading-tight" style="font-size:10px;">
                        {{ $month['label'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Two-column: Upcoming renewals + Revenue by product --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Upcoming renewals --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">Upcoming Renewals (Next 30 Days)</h3>
            </div>
            @if ($upcomingRenewals->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-400">No renewals due in the next 30 days.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($upcomingRenewals as $service)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $service->client?->full_name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ $service->domain ?: ($service->product?->name ?? 'Service #' . $service->id) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ $service->next_due_date?->format('M d') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                        {{ $service->currency_code }} {{ number_format($service->amount / 100, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Revenue by product --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">Revenue by Product (Top 10)</h3>
            </div>
            @if ($revenueByProduct->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-400">No paid revenue data yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($revenueByProduct as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['product_name'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700">
                                        {{ number_format($row['total'] / 100, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
