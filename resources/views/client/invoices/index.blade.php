<x-client-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('navigation.my_invoices') }}</h2>
    </x-slot>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('client.invoices.index') }}" class="flex items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.status') }}</label>
                <select name="status" class="block rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach (['unpaid', 'paid', 'overdue', 'cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ __('invoices.status_' . $s) }}</option>
                    @endforeach
                </select>
            </div>
            <x-button type="submit">{{ __('common.filter') }}</x-button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.invoice') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.total') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.due_date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($invoices as $invoice)
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
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $invoice->invoice_number }}
                                <div class="text-xs text-gray-400">{{ $invoice->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                {{ $invoice->currency_code }} {{ number_format($invoice->total / 100, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $invoice->due_date?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge :color="$statusColor">{{ __('invoices.status_' . $invoice->status) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                                <a href="{{ route('client.invoices.show', $invoice) }}"
                                   class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                    {{ __('common.view') }}
                                </a>
                                @if ($invoice->amount_due > 0)
                                    <a href="{{ route('client.invoices.pay', $invoice) }}"
                                       class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700">
                                        {{ __('invoices.pay_now') }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">
                                {{ __('invoices.no_invoices') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</x-client-layout>
