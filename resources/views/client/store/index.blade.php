<x-client-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('store.order_services') }}</h2>
    </x-slot>

    {{-- Currency notice --}}
    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-gray-500">
            {{ __('store.prices_in', ['currency' => $currencyCode]) }}
            <a href="{{ route('client.profile') }}" class="text-indigo-600 hover:underline ml-1">{{ __('store.change_currency') }}</a>
        </p>
    </div>

    @if ($groups->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <p class="text-gray-400 text-sm">{{ __('store.no_products') }}</p>
        </div>
    @endif

    @foreach ($groups as $group)
        <div class="mb-10">
            <h3 class="text-base font-semibold text-gray-800 mb-1">{{ $group->name }}</h3>
            @if ($group->description)
                <p class="text-sm text-gray-500 mb-4">{{ $group->description }}</p>
            @else
                <div class="mb-4"></div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($group->products as $product)
                    @php
                        // Find the cheapest cycle price in client currency, fall back to default
                        $lowestPricing = $product->pricing
                            ->filter(fn($p) => $p->currency_code === $currencyCode)
                            ->sortBy('price')
                            ->first()
                            ?? $product->pricing->sortBy('price')->first();
                    @endphp

                    <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col">
                        <h4 class="text-base font-semibold text-gray-800 mb-1">{{ $product->name }}</h4>

                        @if ($product->description)
                            <p class="text-sm text-gray-500 mb-4 flex-1">{{ Str::limit($product->description, 120) }}</p>
                        @else
                            <div class="flex-1"></div>
                        @endif

                        <div class="mt-3">
                            @if ($lowestPricing)
                                <p class="text-sm text-gray-500 mb-1">{{ __('store.starting_from') }}</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ $currency->format($lowestPricing->price) }}
                                    <span class="text-sm font-normal text-gray-400">/ {{ __('store.cycles.' . $lowestPricing->billing_cycle) }}</span>
                                </p>
                                @if ($lowestPricing->setup_fee > 0)
                                    <p class="text-xs text-gray-400 mt-1">+ {{ $currency->format($lowestPricing->setup_fee) }} {{ __('store.setup_fee') }}</p>
                                @endif
                            @else
                                <p class="text-sm text-gray-400">{{ __('store.contact_for_pricing') }}</p>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('client.store.show', $product->slug) }}"
                               class="block text-center w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                                {{ __('store.order_now') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

</x-client-layout>
