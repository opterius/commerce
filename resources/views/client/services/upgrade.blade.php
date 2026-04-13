<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('client.services.show', $service) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">Upgrade / Downgrade Service</h2>
        </div>
    </x-slot>

    <div class="max-w-4xl space-y-6">

        {{-- Current plan --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Current Plan</h3>
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-base font-semibold text-gray-900">{{ $service->product?->name ?? 'Unknown Plan' }}</p>
                    <p class="text-sm text-gray-500">
                        {{ ucfirst(str_replace('_', ' ', $service->billing_cycle)) }}
                        &mdash;
                        {{ $currencyCode }} {{ number_format($service->amount / 100, 2) }}
                    </p>
                    @if ($service->next_due_date)
                        <p class="text-xs text-gray-400 mt-0.5">Next due: {{ $service->next_due_date->format('M d, Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-800">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Available plans --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">Available Plans</h3>
                <p class="text-sm text-gray-500 mt-0.5">Proration is calculated based on remaining days in your current billing period.</p>
            </div>

            @if (count($pricingOptions) === 0)
                <div class="px-6 py-12 text-center text-sm text-gray-500">
                    No other plans are available for this service.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing Cycle</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Proration Charge</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($pricingOptions as $option)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900">{{ $option['product_name'] }}</span>
                                            @if ($option['is_upgrade'])
                                                <x-badge color="green">Upgrade</x-badge>
                                            @else
                                                <x-badge color="amber">Downgrade</x-badge>
                                            @endif
                                        </div>
                                        @if ($option['group_name'])
                                            <p class="text-xs text-gray-400">{{ $option['group_name'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ ucfirst(str_replace('_', ' ', $option['billing_cycle'])) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                        {{ $currencyCode }} {{ number_format($option['price'] / 100, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @php $net = $option['proration']['net']; @endphp
                                        @if ($net > 0)
                                            <span class="text-sm font-medium text-red-600">
                                                +{{ $currencyCode }} {{ number_format($net / 100, 2) }}
                                            </span>
                                            <p class="text-xs text-gray-400">Invoice will be created</p>
                                        @elseif ($net < 0)
                                            <span class="text-sm font-medium text-green-600">
                                                {{ $currencyCode }} {{ number_format($net / 100, 2) }} credit
                                            </span>
                                            <p class="text-xs text-gray-400">Credit added to account</p>
                                        @else
                                            <span class="text-sm text-gray-400">No charge</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form method="POST" action="{{ route('client.services.upgrade.store', $service) }}">
                                            @csrf
                                            <input type="hidden" name="to_product_id" value="{{ $option['product_id'] }}">
                                            <input type="hidden" name="to_billing_cycle" value="{{ $option['billing_cycle'] }}">
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition"
                                                onclick="return confirm('Switch to {{ addslashes($option['product_name']) }} ({{ ucfirst(str_replace('_', ' ', $option['billing_cycle'])) }})? This cannot be undone.')">
                                                Select
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Proration explanation --}}
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-sm text-blue-800 font-medium">How proration works</p>
            <p class="text-sm text-blue-700 mt-1">
                When you switch plans mid-cycle, we calculate the unused portion of your current plan and the cost of the remaining days on your new plan. If the new plan costs more, you'll be charged the difference. If it costs less, you'll receive an account credit.
            </p>
        </div>
    </div>
</x-client-layout>
