<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('client.store.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ $product->name }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('client.store.order', $product->slug) }}"
          x-data="orderForm()" x-init="init()">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: product + options --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Product info --}}
                @if ($product->description)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('store.product_description') }}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $product->description }}</p>
                    </div>
                @endif

                {{-- Billing cycle --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('store.billing_cycle') }}</h3>

                    <div class="space-y-3">
                        @foreach ($cycles as $cycleKey => $pricing)
                            <label class="flex items-center justify-between p-4 border rounded-lg cursor-pointer transition-colors"
                                   :class="cycle === '{{ $cycleKey }}' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="billing_cycle" value="{{ $cycleKey }}"
                                           x-model="cycle"
                                           @change="updateTotal()"
                                           class="text-indigo-600 focus:ring-indigo-500"
                                           {{ $loop->first ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-gray-800">{{ __('store.cycles.' . $cycleKey) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-semibold text-gray-900">{{ $currency->format($pricing->price) }}</span>
                                    @if ($pricing->setup_fee > 0)
                                        <span class="block text-xs text-gray-400">+ {{ $currency->format($pricing->setup_fee) }} {{ __('store.setup_fee') }}</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Configurable options --}}
                @foreach ($product->configurableOptionGroups as $group)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">{{ $group->name }}</h3>
                        @if ($group->description)
                            <p class="text-xs text-gray-400 mb-4">{{ $group->description }}</p>
                        @else
                            <div class="mb-4"></div>
                        @endif

                        <div class="space-y-2">
                            @foreach ($group->options as $option)
                                @if ($option->option_type === 'dropdown' || $option->option_type === 'radio')
                                    <div>
                                        <label class="form-label text-xs">{{ $option->name }}</label>

                                        @if ($option->option_type === 'dropdown')
                                            <select name="config_options[]"
                                                    class="form-input text-sm"
                                                    @change="updateTotal()">
                                                <option value="">— {{ __('store.none') }} —</option>
                                                @foreach ($option->values as $value)
                                                    @php
                                                        $vPrice = $value->pricing
                                                            ->where('currency_code', $currencyCode)
                                                            ->where('billing_cycle', $cycles->keys()->first())
                                                            ->first()
                                                            ?? $value->pricing->first();
                                                    @endphp
                                                    <option value="{{ $value->id }}"
                                                            data-price="{{ $vPrice?->price ?? 0 }}"
                                                            {{ old("config_options.{$option->id}") == $value->id ? 'selected' : '' }}>
                                                        {{ $value->label }}
                                                        @if ($vPrice && $vPrice->price > 0)
                                                            (+{{ $currency->format($vPrice->price) }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <div class="space-y-2 mt-1">
                                                @foreach ($option->values as $value)
                                                    @php
                                                        $vPrice = $value->pricing
                                                            ->where('currency_code', $currencyCode)
                                                            ->where('billing_cycle', $cycles->keys()->first())
                                                            ->first()
                                                            ?? $value->pricing->first();
                                                    @endphp
                                                    <label class="flex items-center gap-2 text-sm">
                                                        <input type="radio" name="config_options[]" value="{{ $value->id }}"
                                                               data-price="{{ $vPrice?->price ?? 0 }}"
                                                               class="text-indigo-600"
                                                               @change="updateTotal()">
                                                        {{ $value->label }}
                                                        @if ($vPrice && $vPrice->price > 0)
                                                            <span class="text-gray-400">(+{{ $currency->format($vPrice->price) }})</span>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @elseif ($option->option_type === 'checkbox')
                                    @foreach ($option->values as $value)
                                        @php
                                            $vPrice = $value->pricing
                                                ->where('currency_code', $currencyCode)
                                                ->where('billing_cycle', $cycles->keys()->first())
                                                ->first()
                                                ?? $value->pricing->first();
                                        @endphp
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" name="config_options[]" value="{{ $value->id }}"
                                                   data-price="{{ $vPrice?->price ?? 0 }}"
                                                   class="rounded text-indigo-600"
                                                   @change="updateTotal()">
                                            {{ $value->label }}
                                            @if ($vPrice && $vPrice->price > 0)
                                                <span class="text-gray-400">(+{{ $currency->format($vPrice->price) }})</span>
                                            @endif
                                        </label>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Promo code --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('store.promo_code') }}</h3>
                    <div class="flex gap-2">
                        <input type="text" name="promo_code" id="promo_code"
                               class="form-input text-sm flex-1 uppercase"
                               placeholder="{{ __('store.enter_promo_code') }}"
                               :value="promoCode"
                               @input="promoCode = $event.target.value.toUpperCase()">
                        <button type="button" @click="applyPromo()"
                                class="px-4 py-2 text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                            {{ __('store.apply') }}
                        </button>
                    </div>
                    <p x-show="promoMessage" x-text="promoMessage"
                       :class="promoValid ? 'text-green-600' : 'text-red-500'"
                       class="text-xs mt-2"></p>
                </div>

            </div>

            {{-- Right: order summary --}}
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('store.order_summary') }}</h3>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>{{ $product->name }}</span>
                            <span x-text="formatPrice(basePrice)">—</span>
                        </div>

                        <div x-show="setupFee > 0" class="flex justify-between text-gray-500 text-xs">
                            <span>{{ __('store.setup_fee') }}</span>
                            <span x-text="formatPrice(setupFee)"></span>
                        </div>

                        <div x-show="optionTotal > 0" class="flex justify-between text-gray-600">
                            <span>{{ __('store.options') }}</span>
                            <span x-text="formatPrice(optionTotal)"></span>
                        </div>

                        <div x-show="discount > 0" class="flex justify-between text-green-600">
                            <span>{{ __('store.discount') }} (<span x-text="promoCode"></span>)</span>
                            <span>-<span x-text="formatPrice(discount)"></span></span>
                        </div>

                        <div class="border-t border-gray-100 pt-3 mt-3 flex justify-between font-semibold text-gray-900">
                            <span>{{ __('store.total') }}</span>
                            <span x-text="formatPrice(total)">—</span>
                        </div>

                        <p class="text-xs text-gray-400 mt-1">{{ __('store.billed', ['cycle' => '']) }}
                            <span x-text="cycleLabel()"></span>
                        </p>
                    </div>

                    @error('billing_cycle') <p class="text-xs text-red-500 mt-3">{{ $message }}</p> @enderror

                    <button type="submit"
                            class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg transition-colors">
                        {{ __('store.place_order') }}
                    </button>

                    <p class="text-xs text-gray-400 text-center mt-3">{{ __('store.invoice_note') }}</p>
                </div>
            </div>

        </div>
    </form>
</x-client-layout>

@push('scripts')
<script>
function orderForm() {
    const cycles = @json($cycles->map(fn($p) => ['price' => $p->price, 'setup_fee' => $p->setup_fee]));
    const cycleLabels = @json(collect($cycles->keys())->mapWithKeys(fn($k) => [$k => __('store.cycles.' . $k)]));
    const symbol = '{{ $currency->prefix ?: $currency->symbol }}';
    const decimals = {{ $currency->decimal_places }};
    const divisor = Math.pow(10, decimals);

    return {
        cycle: '{{ $cycles->keys()->first() }}',
        basePrice: 0,
        setupFee: 0,
        optionTotal: 0,
        discount: 0,
        total: 0,
        promoCode: '',
        promoMessage: '',
        promoValid: false,
        promoDiscount: 0,

        init() {
            this.updateTotal();
        },

        updateTotal() {
            const p = cycles[this.cycle] || { price: 0, setup_fee: 0 };
            this.basePrice = p.price;
            this.setupFee  = p.setup_fee;

            // Sum selected option prices
            let opts = 0;
            document.querySelectorAll('input[name="config_options[]"]:checked, select[name="config_options[]"]').forEach(el => {
                const price = parseInt(el.options ? (el.options[el.selectedIndex]?.dataset?.price || 0) : (el.dataset.price || 0));
                opts += price;
            });
            this.optionTotal = opts;

            this.discount = this.promoDiscount;
            const subtotal = this.basePrice + this.setupFee + this.optionTotal;
            this.total = Math.max(0, subtotal - this.discount);
        },

        cycleLabel() {
            return cycleLabels[this.cycle] || this.cycle;
        },

        formatPrice(cents) {
            return symbol + (cents / divisor).toFixed(decimals);
        },

        async applyPromo() {
            if (!this.promoCode) return;

            try {
                const res = await fetch('{{ route('client.store.promo-check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        code: this.promoCode,
                        product_id: {{ $product->id }},
                        currency_code: '{{ $currencyCode }}',
                        billing_cycle: this.cycle,
                    }),
                });

                const data = await res.json();

                if (data.valid) {
                    this.promoValid    = true;
                    this.promoDiscount = data.discount;
                    this.promoMessage  = data.message;
                } else {
                    this.promoValid    = false;
                    this.promoDiscount = 0;
                    this.promoMessage  = data.message;
                }
            } catch {
                this.promoMessage = '{{ __("store.promo_check_failed") }}';
                this.promoValid   = false;
            }

            this.updateTotal();
        },
    };
}
</script>
@endpush
