{{-- Dashboard --}}
<x-sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard*')">
    <x-slot:icon>
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
    </x-slot:icon>
    {{ __('navigation.dashboard') }}
</x-sidebar-link>

{{-- Clients --}}
<x-sidebar-link :href="route('admin.clients.index')" :active="request()->routeIs('admin.clients*')">
    <x-slot:icon>
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        </svg>
    </x-slot:icon>
    {{ __('navigation.clients') }}
</x-sidebar-link>

{{-- Products (dropdown) --}}
<div x-data="{ open: {{ request()->routeIs('admin.products*', 'admin.product-groups*', 'admin.configurable-options*', 'admin.promo-codes*') ? 'true' : 'false' }} }">
    <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.products*', 'admin.product-groups*', 'admin.configurable-options*', 'admin.promo-codes*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
        <span class="flex items-center">
            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
            </svg>
            {{ __('navigation.products') }}
        </span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
    </button>
    <div x-show="open" x-cloak class="mt-1 ml-6 space-y-1">
        <a href="{{ route('admin.products.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.products.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('products.products') }}</a>
        <a href="{{ route('admin.product-groups.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.product-groups.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('products.product_groups') }}</a>
        <a href="{{ route('admin.configurable-options.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.configurable-options.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('products.configurable_options') }}</a>
        <a href="{{ route('admin.promo-codes.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.promo-codes.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('products.promo_codes') }}</a>
    </div>
</div>

{{-- Billing (dropdown) --}}
@php
    $billingActive = request()->routeIs('admin.orders*', 'admin.services*', 'admin.invoices*', 'admin.payments*', 'admin.tax-rules*');
@endphp
<div x-data="{ open: {{ $billingActive ? 'true' : 'false' }} }">
    <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-medium rounded-lg transition {{ $billingActive ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
        <span class="flex items-center">
            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
            </svg>
            {{ __('navigation.billing') }}
        </span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
    </button>
    <div x-show="open" x-cloak class="mt-1 ml-6 space-y-1">
        <a href="{{ route('admin.orders.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.orders*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('orders.orders') }}</a>
        <a href="{{ route('admin.services.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.services*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.services') }}</a>
        <a href="{{ route('admin.invoices.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.invoices*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('invoices.invoices') }}</a>
        <a href="{{ route('admin.payments.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.payments*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.payments') }}</a>
        <a href="{{ route('admin.tax-rules.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.tax-rules*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.tax_rules') }}</a>
    </div>
</div>

{{-- Support > Tickets --}}
<x-sidebar-link href="#" :disabled="true">
    <x-slot:icon>
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
        </svg>
    </x-slot:icon>
    {{ __('navigation.tickets') }}
</x-sidebar-link>

{{-- Divider --}}
<div class="my-4 border-t border-gray-800"></div>

{{-- Settings --}}
<x-sidebar-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings*')">
    <x-slot:icon>
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
    </x-slot:icon>
    {{ __('navigation.settings') }}
</x-sidebar-link>
