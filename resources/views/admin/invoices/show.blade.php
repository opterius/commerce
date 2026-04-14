<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.invoices.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <h2 class="text-lg font-semibold text-gray-800">{{ $invoice->invoice_number }}</h2>
                @php
                    $statusColor = match($invoice->status) {
                        'draft'     => 'gray',
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
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn-secondary">
                    {{ __('invoices.download_pdf') }}
                </a>
                @if ($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                    <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}" class="inline">
                        @csrf
                        <x-secondary-button type="submit">{{ __('invoices.mark_sent') }}</x-secondary-button>
                    </form>
                    <form method="POST" action="{{ route('admin.invoices.void', $invoice) }}" class="inline">
                        @csrf
                        <x-danger-button type="submit"
                            onclick="return confirm('{{ __('invoices.void_confirm') }}')">
                            {{ __('invoices.void') }}
                        </x-danger-button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main: line items + payments --}}
        <div class="lg:col-span-2 space-y-6">

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
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.tax_amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item->description }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-500">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-900">{{ $invoice->currency_code }} {{ number_format($item->amount / 100, 2) }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-500">
                                        {{ $item->tax_amount > 0 ? $invoice->currency_code . ' ' . number_format($item->tax_amount / 100, 2) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-right text-sm text-gray-500">{{ __('invoices.subtotal') }}</td>
                                <td colspan="2" class="px-6 py-3 text-right text-sm text-gray-900">{{ $invoice->currency_code }} {{ number_format($invoice->subtotal / 100, 2) }}</td>
                            </tr>
                            @if ($invoice->tax > 0)
                                <tr>
                                    <td colspan="2" class="px-6 py-3 text-right text-sm text-gray-500">{{ __('invoices.tax') }}</td>
                                    <td colspan="2" class="px-6 py-3 text-right text-sm text-gray-900">{{ $invoice->currency_code }} {{ number_format($invoice->tax / 100, 2) }}</td>
                                </tr>
                            @endif
                            @if ($invoice->credit_applied > 0)
                                <tr>
                                    <td colspan="2" class="px-6 py-3 text-right text-sm text-gray-500">{{ __('invoices.credit_applied') }}</td>
                                    <td colspan="2" class="px-6 py-3 text-right text-sm text-green-600">&minus; {{ $invoice->currency_code }} {{ number_format($invoice->credit_applied / 100, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-right text-sm font-semibold text-gray-800">{{ __('common.total') }}</td>
                                <td colspan="2" class="px-6 py-3 text-right text-sm font-semibold text-gray-900">{{ $invoice->currency_code }} {{ number_format($invoice->total / 100, 2) }}</td>
                            </tr>
                            @if ($invoice->amount_due > 0)
                                <tr>
                                    <td colspan="2" class="px-6 py-3 text-right text-sm font-semibold text-red-700">{{ __('invoices.amount_due') }}</td>
                                    <td colspan="2" class="px-6 py-3 text-right text-sm font-semibold text-red-700">{{ $invoice->currency_code }} {{ number_format($invoice->amount_due / 100, 2) }}</td>
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Payments --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">{{ __('invoices.payments') }}</h3>
                    @if ($invoice->amount_due > 0)
                        <x-button type="button" x-data="" x-on:click="$dispatch('open-modal', 'manual-payment')">
                            {{ __('invoices.record_payment') }}
                        </x-button>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.gateway') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.transaction_id') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.amount') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($invoice->payments as $payment)
                                @php
                                    $pColor = match($payment->status) {
                                        'completed' => 'green', 'pending' => 'amber',
                                        'failed' => 'red', 'refunded' => 'blue', default => 'gray',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ ucfirst($payment->gateway) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $payment->transaction_id ?: '—' }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-900">{{ $invoice->currency_code }} {{ number_format($payment->amount / 100, 2) }}</td>
                                    <td class="px-6 py-4"><x-badge :color="$pColor">{{ ucfirst($payment->status) }}</x-badge></td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-400">{{ __('invoices.no_payments') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Credit notes --}}
            @if ($invoice->creditNotes->count())
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800">{{ __('invoices.credit_notes') }}</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach ($invoice->creditNotes as $cn)
                            <div class="px-6 py-4 flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-900">{{ $invoice->currency_code }} {{ number_format($cn->amount / 100, 2) }}</p>
                                    @if ($cn->reason)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $cn->reason }}</p>
                                    @endif
                                    @if ($cn->createdBy)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $cn->createdBy->name }}</p>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400">{{ $cn->created_at->format('M d, Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Client --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.client') }}</h3>
                <dl class="space-y-2">
                    <div>
                        <a href="{{ route('admin.clients.show', $invoice->client) }}"
                           class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                            {{ $invoice->client->first_name }} {{ $invoice->client->last_name }}
                        </a>
                    </div>
                    <div class="text-sm text-gray-500">{{ $invoice->client->email }}</div>
                    @if ($invoice->client->company_name)
                        <div class="text-sm text-gray-500">{{ $invoice->client->company_name }}</div>
                    @endif
                </dl>
            </div>

            {{-- Dates --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.details') }}</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">{{ __('invoices.issued') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $invoice->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">{{ __('invoices.due_date') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                    @if ($invoice->paid_date)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('invoices.paid_on') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $invoice->paid_date->format('M d, Y') }}</dd>
                        </div>
                    @endif
                    @if ($invoice->sent_at)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('invoices.sent_at') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $invoice->sent_at->format('M d, Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Apply credit --}}
            @if ($invoice->amount_due > 0 && $creditBalance > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-1">{{ __('invoices.apply_credit') }}</h3>
                    <p class="text-xs text-gray-500 mb-4">
                        {{ __('invoices.available_credit') }}: {{ $invoice->currency_code }} {{ number_format($creditBalance / 100, 2) }}
                    </p>
                    <form method="POST" action="{{ route('admin.invoices.apply-credit', $invoice) }}">
                        @csrf
                        <div class="flex gap-2">
                            <input type="number" name="amount" step="0.01" min="0.01"
                                max="{{ number_format(min($creditBalance, $invoice->amount_due) / 100, 2) }}"
                                placeholder="0.00"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            <x-button type="submit">{{ __('common.apply') }}</x-button>
                        </div>
                    </form>
                </div>
            @endif

            @if ($invoice->notes)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">{{ __('common.notes') }}</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Manual payment modal --}}
    @push('modals')
        <x-modal name="manual-payment" maxWidth="md">
            <form method="POST" action="{{ route('admin.invoices.manual-payment', $invoice) }}">
                @csrf
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('invoices.record_payment') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <x-label for="mp_amount" :value="__('invoices.amount')" />
                            <input type="number" id="mp_amount" name="amount" step="0.01" min="0.01"
                                value="{{ number_format($invoice->amount_due / 100, 2) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required />
                        </div>
                        <div>
                            <x-label for="mp_method" :value="__('invoices.method')" />
                            <select id="mp_method" name="method"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                @foreach (['bank_transfer', 'cash', 'check', 'other'] as $m)
                                    <option value="{{ $m }}">{{ __('invoices.method_' . $m) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-label for="mp_txn" :value="__('invoices.transaction_id') . ' (' . __('common.optional') . ')'" />
                            <input type="text" id="mp_txn" name="transaction_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        </div>
                        <div>
                            <x-label for="mp_notes" :value="__('common.notes') . ' (' . __('common.optional') . ')'" />
                            <textarea id="mp_notes" name="notes" rows="2"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-xl">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'manual-payment')">
                        {{ __('common.cancel') }}
                    </x-secondary-button>
                    <x-button type="submit">{{ __('invoices.record_payment') }}</x-button>
                </div>
            </form>
        </x-modal>
    @endpush
</x-admin-layout>
