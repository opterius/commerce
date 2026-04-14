@php
    $portalSettings = \App\Models\Setting::getGroup('portal');
    $showHero       = ($portalSettings['portal_show_hero']          ?? '1') === '1';
    $showProducts   = ($portalSettings['portal_show_products']      ?? '1') === '1';
    $showDomains    = ($portalSettings['portal_show_domain_search'] ?? '0') === '1';
    $heroTitle      = $portalSettings['portal_hero_title']    ?? __('portal.hero_title');
    $heroSubtitle   = $portalSettings['portal_hero_subtitle'] ?? __('portal.hero_subtitle');
    $hasProducts    = $showProducts && $groups->isNotEmpty();
@endphp

<x-portal-layout>

{{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
@if ($showHero)
<section class="portal-hero">
    <div class="max-w-3xl mx-auto text-center" style="padding: 6rem 1.5rem 7rem;">
        <h1 class="font-extrabold text-white tracking-tight leading-tight" style="font-size: clamp(2rem, 5vw, 3.25rem)">
            {{ $heroTitle }}
        </h1>
        @if ($heroSubtitle)
        <p class="mt-5 text-lg leading-relaxed" style="color: rgba(255,255,255,.78); max-width: 36rem; margin-left: auto; margin-right: auto;">
            {{ $heroSubtitle }}
        </p>
        @endif
        <div style="margin-top: 2.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 1rem;">
            <a href="{{ route('client.login') }}" class="portal-btn-white" style="padding:.8125rem 2.25rem; font-size:1rem;">
                {{ __('portal.get_started') }}
            </a>
            <a href="{{ route('client.login') }}" class="portal-btn-outline" style="padding:.8125rem 2.25rem; font-size:1rem;">
                {{ __('auth.sign_in') }}
            </a>
        </div>
    </div>
</section>
@endif

{{-- ── Feature highlights (always visible, gives substance) ─────────────── --}}
<section class="bg-white border-b border-gray-100">
    <div class="max-w-6xl mx-auto" style="padding: 3.5rem 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 2.5rem;">

            <div class="flex gap-4">
                <div class="portal-feature-icon flex-shrink-0">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-1">{{ __('portal.feature_speed_title') }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ __('portal.feature_speed_desc') }}</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="portal-feature-icon flex-shrink-0">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-1">{{ __('portal.feature_security_title') }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ __('portal.feature_security_desc') }}</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="portal-feature-icon flex-shrink-0">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-1">{{ __('portal.feature_support_title') }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ __('portal.feature_support_desc') }}</p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── Domain search ───────────────────────────────────────────────────────── --}}
@if ($showDomains)
<section class="bg-gray-50 border-b border-gray-100 py-14">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('portal.domain_search_title') }}</h2>
        <p class="mt-2 text-sm text-gray-500">{{ __('portal.domain_search_subtitle') }}</p>
        <div class="mt-6 flex rounded-lg shadow-sm overflow-hidden border border-gray-300 bg-white focus-within:ring-2" style="--tw-ring-color: var(--pa)">
            <input
                type="text"
                name="q"
                form="domain-search-form"
                placeholder="{{ __('portal.domain_search_placeholder') }}"
                class="flex-1 px-4 py-3 text-base text-gray-900 bg-transparent border-0 outline-none placeholder-gray-400"
                required
            >
            <button type="submit" form="domain-search-form" class="portal-search-btn">
                {{ __('portal.domain_search_btn') }}
            </button>
        </div>
        <form id="domain-search-form" method="GET" action="{{ route('client.domains.search') }}"></form>
    </div>
</section>
@endif

{{-- ── Products ─────────────────────────────────────────────────────────────── --}}
@if ($showProducts)
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16">

    @if ($groups->isEmpty())
        {{-- Nothing to show yet — page still looks good because of the sections above --}}
        <div class="text-center py-8">
            <p class="text-sm text-gray-400">{{ __('store.no_products') }}</p>
        </div>
    @else
        @foreach ($groups as $group)
        <div class="mb-16">
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-bold text-gray-900">{{ $group->name }}</h2>
                @if ($group->description)
                    <p class="mt-2 text-sm text-gray-500 max-w-xl mx-auto leading-relaxed">{{ $group->description }}</p>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach ($group->products as $product)
                @php
                    $currencyCode  = $currency?->code ?? 'USD';
                    $lowestPricing = $product->pricing
                        ->filter(fn($p) => $p->currency_code === $currencyCode)
                        ->sortBy('price')->first()
                        ?? $product->pricing->sortBy('price')->first();
                @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-7 flex flex-col hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $product->name }}</h3>
                    @if ($product->description)
                        <p class="text-sm text-gray-500 flex-1 leading-relaxed mb-6">{{ Str::limit($product->description, 140) }}</p>
                    @else
                        <div class="flex-1 mb-6"></div>
                    @endif
                    <div class="border-t border-gray-100 pt-5">
                        @if ($lowestPricing && $currency)
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">{{ __('store.starting_from') }}</p>
                            <p class="text-3xl font-extrabold text-gray-900">
                                {{ $currency->format($lowestPricing->price) }}
                                <span class="text-base font-normal text-gray-400">/ {{ __('store.cycles.' . $lowestPricing->billing_cycle) }}</span>
                            </p>
                            @if ($lowestPricing->setup_fee > 0)
                                <p class="text-xs text-gray-400 mt-1">+ {{ $currency->format($lowestPricing->setup_fee) }} {{ __('store.setup_fee') }}</p>
                            @endif
                        @else
                            <p class="text-sm text-gray-400">{{ __('store.contact_for_pricing') }}</p>
                        @endif
                        <a href="{{ route('client.store.show', $product->slug) }}" class="portal-order-btn mt-5">
                            {{ __('store.order_now') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    @endif

</section>
@endif

</x-portal-layout>
