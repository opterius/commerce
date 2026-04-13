<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('client.invoices.show', $invoice) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">
                {{ __('invoices.pay') }}: {{ $invoice->invoice_number }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-lg">

        {{-- Amount summary --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ __('invoices.amount_due') }}</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $invoice->currency_code }} {{ number_format($invoice->amount_due / 100, 2) }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">{{ __('invoices.due_date') }}</p>
                    <p class="text-sm text-gray-700">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Gateway selection (only shown when multiple gateways are active) --}}
        @if (count($gateways) > 1)
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('invoices.choose_payment_method') }}</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($gateways as $slug => $gw)
                        <a href="{{ route('client.invoices.pay', [$invoice, $slug]) }}"
                           class="px-4 py-2 text-sm rounded-lg border transition-colors
                                  {{ $activeGateway->slug() === $slug
                                      ? 'bg-indigo-600 text-white border-indigo-600'
                                      : 'bg-white text-gray-700 border-gray-200 hover:border-indigo-400' }}">
                            {{ $gw->name() }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Payment form --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-5">{{ $activeGateway->name() }}</h3>

            <form id="gateway-form"
                  action="{{ route('client.invoices.process-payment', [$invoice, $activeGateway->slug()]) }}"
                  method="POST">
                @csrf
                @include($activeGateway->formView(), array_merge($gatewayData, ['invoice' => $invoice]))
            </form>
        </div>

    </div>
</x-client-layout>
