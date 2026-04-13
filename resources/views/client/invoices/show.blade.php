<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('client.invoices.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <h2 class="text-lg font-semibold text-gray-800">{{ $invoice->invoice_number }}</h2>
                @php
                    $statusColor = match($invoice->status) {
                        'unpaid'    => 'amber',
                        'paid'      => 'green',
                        'overdue'   => 'red',
                        'cancelled' => 'gray',
                        'refunded'  => 'blue',
                        default     => 'gray',
                    };
                @endphp
                <x-badge :color="$statusColor">{{ __('invoices.status_' . $invoice->status) }}</x-badge>
            </div>
            @if ($invoice->amount_due > 0)
                <a href="{{ route('client.invoices.pay', $invoice) }}">
                    <x-button type="button">{{ __('invoices.pay_now') }}</x-button>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">

        {{-- Line items --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">{{ __('invoices.line_items') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.description') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.qty') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->description }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">{{ $invoice->currency_code }} {{ number_format(($item->amount + $item->tax_amount) / 100, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        @if ($invoice->tax > 0)
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-right text-sm text-gray-500">{{ __('invoices.tax') }}</td>
                                <td class="px-6 py-3 text-right text-sm text-gray-900">{{ $invoice->currency_code }} {{ number_format($invoice->tax / 100, 2) }}</td>
                            </tr>
                        @endif
                        @if ($invoice->credit_applied > 0)
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-right text-sm text-gray-500">{{ __('invoices.credit_applied') }}</td>
                                <td class="px-6 py-3 text-right text-sm text-green-600">&minus; {{ $invoice->currency_code }} {{ number_format($invoice->credit_applied / 100, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="2" class="px-6 py-3 text-right text-sm font-semibold text-gray-800">{{ __('common.total') }}</td>
                            <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900">{{ $invoice->currency_code }} {{ number_format($invoice->total / 100, 2) }}</td>
                        </tr>
                        @if ($invoice->amount_due > 0)
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-right text-sm font-bold text-red-700">{{ __('invoices.amount_due') }}</td>
                                <td class="px-6 py-3 text-right text-sm font-bold text-red-700">{{ $invoice->currency_code }} {{ number_format($invoice->amount_due / 100, 2) }}</td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Dates --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.issued') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->created_at->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.due_date') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</dd>
                </div>
                @if ($invoice->paid_date)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.paid_on') }}</dt>
                        <dd class="mt-1 text-sm text-green-700 font-medium">{{ $invoice->paid_date->format('M d, Y') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Payment history --}}
        @if ($invoice->payments->count())
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">{{ __('invoices.payment_history') }}</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach ($invoice->payments->where('status', 'completed') as $payment)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-900">{{ ucfirst($payment->gateway) }}</p>
                                @if ($payment->transaction_id)
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $payment->transaction_id }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ $invoice->currency_code }} {{ number_format($payment->amount / 100, 2) }}</p>
                                <p class="text-xs text-gray-400">{{ $payment->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</x-client-layout>
