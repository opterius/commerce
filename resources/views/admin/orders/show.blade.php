<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.orders.index') }}" class="text-gray-400 hover:text-gray-600">
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
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Order items --}}
        <div class="lg:col-span-2 space-y-6">
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
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $item->product?->name ?? __('common.deleted') }}
                                        </div>
                                        @if ($item->setup_fee > 0)
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                + {{ $order->currency_code }} {{ number_format($item->setup_fee / 100, 2) }} {{ __('orders.setup_fee') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ __('orders.cycle_' . $item->billing_cycle) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->domain ?: '—' }}
                                    </td>
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
                    <a href="{{ route('admin.invoices.show', $order->invoice) }}"
                       class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        {{ $order->invoice->invoice_number }}
                        &mdash;
                        {{ $order->currency_code }} {{ number_format($order->invoice->total / 100, 2) }}
                        @php
                            $invColor = match($order->invoice->status) {
                                'paid' => 'green', 'unpaid' => 'amber', 'overdue' => 'red',
                                'cancelled' => 'gray', 'refunded' => 'blue', default => 'gray',
                            };
                        @endphp
                        <x-badge :color="$invColor">{{ __('invoices.status_' . $order->invoice->status) }}</x-badge>
                    </a>
                </div>
            @endif
        </div>

        {{-- Sidebar: client info + update status --}}
        <div class="space-y-6">
            {{-- Client --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.client') }}</h3>
                <dl class="space-y-2">
                    <div>
                        <a href="{{ route('admin.clients.show', $order->client) }}"
                           class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                            {{ $order->client->first_name }} {{ $order->client->last_name }}
                        </a>
                    </div>
                    <div class="text-sm text-gray-500">{{ $order->client->email }}</div>
                    @if ($order->client->company_name)
                        <div class="text-sm text-gray-500">{{ $order->client->company_name }}</div>
                    @endif
                    <div class="text-sm text-gray-500">{{ $order->client->country_code }}</div>
                </dl>
            </div>

            {{-- Promo code --}}
            @if ($order->promoCode)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">{{ __('orders.promo_code') }}</h3>
                    <x-badge color="indigo">{{ $order->promoCode->code }}</x-badge>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ $order->promoCode->type === 'percent'
                            ? $order->promoCode->value . '%'
                            : $order->currency_code . ' ' . number_format($order->promoCode->value / 100, 2) }} {{ __('orders.discount') }}
                    </p>
                </div>
            @endif

            {{-- Update status --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('orders.update_status') }}</h3>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-3">
                        <select name="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach (['pending', 'active', 'fraud', 'cancelled'] as $s)
                                <option value="{{ $s }}" @selected($order->status === $s)>{{ __('orders.status_' . $s) }}</option>
                            @endforeach
                        </select>
                        <x-button type="submit" class="w-full">{{ __('common.update') }}</x-button>
                    </div>
                </form>
            </div>

            {{-- Meta --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.details') }}</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">{{ __('common.created_at') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    @if ($order->ip_address)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('orders.ip_address') }}</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $order->ip_address }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if ($order->notes)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">{{ __('common.notes') }}</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $order->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
