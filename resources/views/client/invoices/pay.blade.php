<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('client.invoices.show', $invoice) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">
                {{ __('invoices.pay') }}: {{ $invoice->invoice_number }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-lg" x-data="stripePayment(@js($paymentIntent['client_secret']), @js(config('services.stripe.key')))">

        {{-- Summary --}}
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

        {{-- Payment form --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-5">{{ __('invoices.payment_details') }}</h3>

            <form id="payment-form" action="{{ route('client.invoices.process-payment', $invoice) }}" method="POST">
                @csrf
                <input type="hidden" name="payment_intent_id" x-model="paymentIntentId" />

                {{-- Saved payment methods --}}
                @if ($savedMethods->count())
                    <div class="mb-5">
                        <p class="text-sm font-medium text-gray-700 mb-3">{{ __('invoices.saved_cards') }}</p>
                        <div class="space-y-2">
                            @foreach ($savedMethods as $method)
                                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                    :class="selectedMethod === '{{ $method->stripe_pm_id }}' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                    <input type="radio" name="payment_method" value="{{ $method->stripe_pm_id }}"
                                        x-on:change="selectedMethod = '{{ $method->stripe_pm_id }}'; useNewCard = false"
                                        class="text-indigo-600" />
                                    <span class="text-sm text-gray-700">
                                        {{ ucfirst($method->brand) }} &bull;&bull;&bull;&bull; {{ $method->last4 }}
                                        <span class="text-gray-400 ml-2">{{ $method->expiry }}</span>
                                        @if ($method->is_default) <x-badge color="green">{{ __('common.default') }}</x-badge> @endif
                                        @if ($method->is_expired) <x-badge color="red">{{ __('invoices.expired') }}</x-badge> @endif
                                    </span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                :class="useNewCard ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                <input type="radio" name="payment_method" value="new"
                                    x-on:change="selectedMethod = null; useNewCard = true"
                                    class="text-indigo-600" />
                                <span class="text-sm text-gray-700">{{ __('invoices.use_new_card') }}</span>
                            </label>
                        </div>
                    </div>
                @endif

                {{-- Stripe Elements card field --}}
                <div x-show="useNewCard || {{ $savedMethods->count() === 0 ? 'true' : 'false' }}" class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('invoices.card_details') }}</label>
                    <div id="card-element"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-3 shadow-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 bg-white">
                    </div>
                    <div id="card-errors" class="mt-2 text-sm text-red-600" role="alert"></div>

                    <div class="mt-3">
                        <x-checkbox name="save_method" value="1" :label="__('invoices.save_card')" />
                    </div>
                </div>

                {{-- Pay button --}}
                <div class="mt-6">
                    <x-button type="submit" class="w-full justify-center" x-bind:disabled="processing">
                        <span x-show="!processing">
                            {{ __('invoices.pay') }} {{ $invoice->currency_code }} {{ number_format($invoice->amount_due / 100, 2) }}
                        </span>
                        <span x-show="processing">{{ __('common.loading') }}</span>
                    </x-button>
                </div>
            </form>
        </div>

        <p class="mt-4 text-center text-xs text-gray-400">
            {{ __('invoices.stripe_secure') }}
        </p>
    </div>

    @push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
    function stripePayment(clientSecret, publishableKey) {
        return {
            processing: false,
            paymentIntentId: '',
            selectedMethod: null,
            useNewCard: {{ $savedMethods->count() === 0 ? 'true' : 'false' }},
            stripe: null,
            cardElement: null,

            init() {
                this.stripe = Stripe(publishableKey);
                const elements = this.stripe.elements();

                this.cardElement = elements.create('card', {
                    style: {
                        base: {
                            fontSize: '15px',
                            color: '#374151',
                            '::placeholder': { color: '#9CA3AF' },
                        },
                    },
                });
                this.cardElement.mount('#card-element');

                this.cardElement.on('change', ({ error }) => {
                    document.getElementById('card-errors').textContent = error ? error.message : '';
                });

                document.getElementById('payment-form').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    this.processing = true;

                    let result;
                    if (this.selectedMethod && !this.useNewCard) {
                        result = await this.stripe.confirmCardPayment(clientSecret, {
                            payment_method: this.selectedMethod,
                        });
                    } else {
                        result = await this.stripe.confirmCardPayment(clientSecret, {
                            payment_method: { card: this.cardElement },
                        });
                    }

                    if (result.error) {
                        document.getElementById('card-errors').textContent = result.error.message;
                        this.processing = false;
                    } else if (result.paymentIntent.status === 'succeeded') {
                        this.paymentIntentId = result.paymentIntent.id;
                        this.$nextTick(() => e.target.submit());
                    }
                });
            }
        };
    }
    </script>
    @endpush
</x-client-layout>
