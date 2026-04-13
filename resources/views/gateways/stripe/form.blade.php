<div x-data="stripePayment(@js($clientSecret), @js($publishableKey))">

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

    <input type="hidden" name="payment_intent_id" x-model="paymentIntentId" />

    {{-- Stripe card element --}}
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

    <x-button type="submit" class="w-full justify-center mt-4" x-bind:disabled="processing">
        <span x-show="!processing">{{ __('invoices.pay') }} {{ $invoice->formattedAmountDue() }}</span>
        <span x-show="processing">{{ __('common.loading') }}</span>
    </x-button>
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
                    base: { fontSize: '15px', color: '#374151', '::placeholder': { color: '#9CA3AF' } },
                },
            });
            this.cardElement.mount('#card-element');
            this.cardElement.on('change', ({ error }) => {
                document.getElementById('card-errors').textContent = error ? error.message : '';
            });

            document.getElementById('gateway-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                this.processing = true;

                const result = this.selectedMethod && !this.useNewCard
                    ? await this.stripe.confirmCardPayment(clientSecret, { payment_method: this.selectedMethod })
                    : await this.stripe.confirmCardPayment(clientSecret, { payment_method: { card: this.cardElement } });

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
