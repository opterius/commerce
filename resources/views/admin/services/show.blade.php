<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.services.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">
                {{ $service->domain ?: ($service->product?->name ?? 'Service #' . $service->id) }}
            </h2>
            @php
                $statusColor = match($service->status) {
                    'pending'    => 'amber',
                    'active'     => 'green',
                    'suspended'  => 'orange',
                    'terminated' => 'red',
                    'cancelled'  => 'gray',
                    default      => 'gray',
                };
            @endphp
            <x-badge :color="$statusColor">{{ ucfirst($service->status) }}</x-badge>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Service details + invoices --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Details --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.details') }}</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.product') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $service->product?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.domain') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $service->domain ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders.cycle') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ __('orders.cycle_' . $service->billing_cycle) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.amount') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $service->currency_code }} {{ number_format($service->amount / 100, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('services.next_due_date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $service->next_due_date?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('services.registration_date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $service->registration_date?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                    @if ($service->username)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('services.username') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $service->username }}</dd>
                        </div>
                    @endif
                    @if ($service->suspended_at)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('services.suspended_at') }}</dt>
                            <dd class="mt-1 text-sm text-red-700">{{ $service->suspended_at->format('M d, Y') }}</dd>
                        </div>
                    @endif
                    @if ($service->terminated_at)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('services.terminated_at') }}</dt>
                            <dd class="mt-1 text-sm text-red-700">{{ $service->terminated_at->format('M d, Y') }}</dd>
                        </div>
                    @endif
                </dl>
                @if ($service->notes)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('common.notes') }}</dt>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $service->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Provisioning --}}
            @if ($service->product?->provisioning_module)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-800">{{ __('provisioning.provisioning') }}</h3>
                    <a href="{{ route('admin.provisioning-log.index', ['service_id' => $service->id]) }}" class="text-xs text-indigo-600 hover:underline">
                        {{ __('provisioning.view_all_logs') }}
                    </a>
                </div>

                {{-- Manual action buttons --}}
                <div class="flex flex-wrap gap-2 mb-5">
                    @foreach (['create','suspend','unsuspend','terminate'] as $act)
                        <form method="POST" action="{{ route('admin.services.provision', $service) }}">
                            @csrf
                            <input type="hidden" name="action" value="{{ $act }}">
                            <button type="submit"
                                class="px-3 py-1.5 text-xs font-medium rounded border {{ $act === 'terminate' ? 'border-red-300 text-red-700 hover:bg-red-50' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                {{ __('provisioning.action_' . $act) }}
                            </button>
                        </form>
                    @endforeach
                </div>

                {{-- Server info --}}
                @if ($service->server)
                    <dl class="grid grid-cols-2 gap-3 text-sm mb-4">
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">{{ __('provisioning.server') }}</dt>
                            <dd class="mt-1 font-medium">{{ $service->server->name }}</dd>
                        </div>
                        @if ($service->panel_account_id)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wider">{{ __('provisioning.panel_account_id') }}</dt>
                                <dd class="mt-1 font-mono text-xs">{{ $service->panel_account_id }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                {{-- Recent provisioning log --}}
                @if ($provisioningLogs->isNotEmpty())
                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">{{ __('provisioning.recent_log') }}</p>
                        <div class="space-y-2">
                            @foreach ($provisioningLogs as $log)
                                @php
                                    $lColor = match($log->status) { 'success' => 'green', 'failed' => 'red', default => 'yellow' };
                                @endphp
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-mono text-gray-700">{{ $log->action }}</span>
                                    <x-badge :color="$lColor" size="xs">{{ $log->status }}</x-badge>
                                    <span class="text-gray-400">{{ $log->created_at->format('M d, H:i') }}</span>
                                    <a href="{{ route('admin.provisioning-log.show', $log) }}" class="text-indigo-600 hover:underline">{{ __('common.view') }}</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            @endif

            {{-- Related invoices --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">{{ __('invoices.invoices') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('invoices.invoice') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.total') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.date') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($invoices as $invoice)
                                @php
                                    $iColor = match($invoice->status) {
                                        'paid' => 'green', 'unpaid' => 'amber', 'overdue' => 'red',
                                        'cancelled' => 'gray', 'refunded' => 'blue', default => 'gray',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-900">{{ $invoice->currency_code }} {{ number_format($invoice->total / 100, 2) }}</td>
                                    <td class="px-6 py-4"><x-badge :color="$iColor">{{ __('invoices.status_' . $invoice->status) }}</x-badge></td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">{{ __('common.view') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-400">{{ __('common.no_results') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar: client + order links --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.client') }}</h3>
                <dl class="space-y-2">
                    <div>
                        <a href="{{ route('admin.clients.show', $service->client) }}"
                           class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                            {{ $service->client->first_name }} {{ $service->client->last_name }}
                        </a>
                    </div>
                    <div class="text-sm text-gray-500">{{ $service->client->email }}</div>
                </dl>
            </div>

            @if ($service->order)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">{{ __('orders.order') }}</h3>
                    <a href="{{ route('admin.orders.show', $service->order) }}"
                       class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                        #{{ $service->order->id }}
                    </a>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.timestamps') }}</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">{{ __('common.created_at') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $service->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-admin-layout>
