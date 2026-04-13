<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('client.orders.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('orders.order') }} #{{ $order->id }}</h2>
            @php
                $statusColor = match($order->status) {
                    'pending'   => 'amber',
                    'active'    => 'green',
                    'fraud'     => 'red',
                    'cancelled' => 'gray',
                    default     => 'gray',
                };
            @endphp
            <x-badge :color="$statusColor">{{ __('orders.status_' . $order->status) }}</x-badge>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">

        {{-- Items --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">{{ __('orders.items') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.product') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders.cycle') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.domain') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.price') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $item->product?->name ?? __('common.deleted') }}
                                    @if ($item->setup_fee > 0)
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            + {{ $order->currency_code }} {{ number_format($item->setup_fee / 100, 2) }} {{ __('orders.setup_fee') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ __('orders.cycle_' . $item->billing_cycle) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->domain ?: '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                    {{ $order->currency_code }} {{ number_format($item->price / 100, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        @if ($order->discount > 0)
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm text-gray-500">{{ __('orders.discount') }}</td>
                                <td class="px-6 py-3 text-right text-sm text-red-600">&minus; {{ $order->currency_code }} {{ number_format($order->discount / 100, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-800">{{ __('common.total') }}</td>
                            <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900">{{ $order->currency_code }} {{ number_format($order->total / 100, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Invoice link --}}
        @if ($order->invoice)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-3">{{ __('invoices.invoice') }}</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $order->invoice->invoice_number }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $order->currency_code }} {{ number_format($order->invoice->total / 100, 2) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        @php
                            $invColor = match($order->invoice->status) {
                                'paid' => 'green', 'unpaid' => 'amber', 'overdue' => 'red',
                                'cancelled' => 'gray', 'refunded' => 'blue', default => 'gray',
                            };
                        @endphp
                        <x-badge :color="$invColor">{{ __('invoices.status_' . $order->invoice->status) }}</x-badge>
                        @if ($order->invoice->amount_due > 0)
                            <a href="{{ route('client.invoices.pay', $order->invoice) }}">
                                <x-button type="button">{{ __('invoices.pay_now') }}</x-button>
                            </a>
                        @endif
                        <a href="{{ route('client.invoices.show', $order->invoice) }}"
                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                            {{ __('common.view') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Meta --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <dl class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.date') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('M d, Y') }}</dd>
                </div>
                @if ($order->promoCode)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders.promo_code') }}</dt>
                        <dd class="mt-1"><x-badge color="indigo">{{ $order->promoCode->code }}</x-badge></dd>
                    </div>
                @endif
            </dl>
        </div>

    </div>
</x-client-layout>
