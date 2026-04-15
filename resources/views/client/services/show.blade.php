<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('client.services.index') }}" class="text-gray-400 hover:text-gray-600">
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

    <div class="max-w-2xl space-y-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-5">{{ __('common.details') }}</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.product') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $service->product?->name ?? '—' }}</dd>
                </div>
                @if ($service->domain)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.domain') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $service->domain }}</dd>
                    </div>
                @endif
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
            </dl>

            @if ($service->suspended_at)
                <div class="mt-5 p-4 rounded-lg bg-orange-50 border border-orange-200">
                    <p class="text-sm text-orange-800 font-medium">{{ __('services.suspended_notice') }}</p>
                    <p class="text-xs text-orange-600 mt-0.5">{{ __('services.suspended_since', ['date' => $service->suspended_at->format('M d, Y')]) }}</p>
                </div>
            @endif

            @if ($service->order)
                <div class="mt-5 pt-5 border-t border-gray-100">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">{{ __('orders.order') }}</dt>
                    <a href="{{ route('client.orders.show', $service->order) }}"
                       class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                        #{{ $service->order->id }}
                    </a>
                </div>
            @endif
        </div>

        {{-- Cancel service --}}
        @if (! in_array($service->status, ['cancelled', 'terminated']))
            @php
                $hasPending = \App\Models\ServiceCancellationRequest::where('service_id', $service->id)
                    ->where('status', 'pending')->exists();
            @endphp
            @if ($hasPending)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                    {{ __('cancellations.already_requested') }}
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ __('cancellations.request_cancellation') }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('cancellations.cancel_type') }}</p>
                    </div>
                    <a href="{{ route('client.services.cancel', $service) }}" class="btn-danger text-sm">
                        {{ __('cancellations.request_cancellation') }}
                    </a>
                </div>
            @endif
        @endif

    </div>
</x-client-layout>
