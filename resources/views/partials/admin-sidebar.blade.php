{{-- Dashboard --}}
<x-sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard*')">
    <x-slot:icon>
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
    </x-slot:icon>
    {{ __('navigation.dashboard') }}
</x-sidebar-link>

{{-- Clients (dropdown) --}}
@php
    $clientsActive = request()->routeIs('admin.clients*', 'admin.client-groups*');
@endphp
<div x-data="{ open: {{ $clientsActive ? 'true' : 'false' }} }">
    <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-medium rounded-lg transition {{ $clientsActive ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
        <span class="flex items-center">
            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            {{ __('navigation.clients') }}
        </span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
    </button>
    <div x-show="open" x-cloak class="mt-1 ml-6 space-y-1">
        <a href="{{ route('admin.clients.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.clients.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.clients') }}</a>
        <a href="{{ route('admin.client-groups.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.client-groups.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('clients.client_groups') }}</a>
    </div>
</div>

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

{{-- Infrastructure (dropdown) --}}
@php
    $infraActive = request()->routeIs('admin.server-groups*', 'admin.servers*', 'admin.provisioning-log*');
@endphp
<div x-data="{ open: {{ $infraActive ? 'true' : 'false' }} }">
    <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-medium rounded-lg transition {{ $infraActive ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
        <span class="flex items-center">
            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.737 5.1a3.375 3.375 0 0 1 2.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 0 1 .9 2.7m0 0a3 3 0 0 1-3 3m0 3h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Zm-3 6h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Z" />
            </svg>
            {{ __('navigation.infrastructure') }}
        </span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
    </button>
    <div x-show="open" x-cloak class="mt-1 ml-6 space-y-1">
        <a href="{{ route('admin.server-groups.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.server-groups*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.server_groups') }}</a>
        <a href="{{ route('admin.servers.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.servers*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.servers') }}</a>
        <a href="{{ route('admin.provisioning-log.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.provisioning-log*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.provisioning_log') }}</a>
    </div>
</div>

{{-- Support (dropdown) --}}
@php
    $supportActive = request()->routeIs('admin.tickets*', 'admin.ticket-departments*', 'admin.canned-responses*');
@endphp
<div x-data="{ open: {{ $supportActive ? 'true' : 'false' }} }">
    <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-medium rounded-lg transition {{ $supportActive ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
        <span class="flex items-center">
            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
            </svg>
            {{ __('navigation.support') }}
        </span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
    </button>
    <div x-show="open" x-cloak class="mt-1 ml-6 space-y-1">
        <a href="{{ route('admin.tickets.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.tickets.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.tickets') }}</a>
        <a href="{{ route('admin.ticket-departments.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.ticket-departments.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.ticket_departments') }}</a>
        <a href="{{ route('admin.canned-responses.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.canned-responses.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.canned_responses') }}</a>
    </div>
</div>

{{-- Domains (dropdown) --}}
@php
    $domainsActive = request()->routeIs('admin.domains*', 'admin.tlds*');
@endphp
<div x-data="{ open: {{ $domainsActive ? 'true' : 'false' }} }">
    <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-medium rounded-lg transition {{ $domainsActive ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
        <span class="flex items-center">
            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            {{ __('navigation.domains') }}
        </span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
    </button>
    <div x-show="open" x-cloak class="mt-1 ml-6 space-y-1">
        <a href="{{ route('admin.domains.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.domains.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.domains') }}</a>
        <a href="{{ route('admin.tlds.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.tlds.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('navigation.tld_manager') }}</a>
    </div>
</div>

{{-- Content (dropdown) --}}
@php
    $contentActive = request()->routeIs('admin.kb-categories*', 'admin.kb-articles*', 'admin.faqs*', 'admin.contact-messages*', 'admin.announcements*', 'admin.service-statuses*');
@endphp
<div x-data="{ open: {{ $contentActive ? 'true' : 'false' }} }">
    <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-medium rounded-lg transition {{ $contentActive ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
        <span class="flex items-center">
            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
            </svg>
            {{ __('navigation.content') }}
        </span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
    </button>
    <div x-show="open" x-cloak class="mt-1 ml-6 space-y-1">
        <a href="{{ route('admin.announcements.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.announcements.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('announcements.title') }}</a>
        <a href="{{ route('admin.service-statuses.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.service-statuses.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('announcements.status_page') }}</a>
        <a href="{{ route('admin.kb-categories.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.kb-categories.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('kb.categories') }}</a>
        <a href="{{ route('admin.kb-articles.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.kb-articles.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('kb.articles') }}</a>
        <a href="{{ route('admin.faqs.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.faqs.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('faq.title') }}</a>
        <a href="{{ route('admin.contact-messages.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.contact-messages.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white' }}">{{ __('contact.inbox') }}</a>
    </div>
</div>

{{-- Reports --}}
@staffcan('reports.view')
<x-sidebar-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports*')">
    <x-slot:icon>
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
        </svg>
    </x-slot:icon>
    {{ __('navigation.reports') }}
</x-sidebar-link>
@endstaffcan

{{-- Divider --}}
<div class="my-4 border-t border-gray-800"></div>

{{-- Staff --}}
@staffcan('staff.view')
<x-sidebar-link :href="route('admin.staff.index')" :active="request()->routeIs('admin.staff*')">
    <x-slot:icon>
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        </svg>
    </x-slot:icon>
    {{ __('navigation.staff') }}
</x-sidebar-link>
@endstaffcan

{{-- Email Templates --}}
<x-sidebar-link :href="route('admin.email-templates.index')" :active="request()->routeIs('admin.email-templates*')">
    <x-slot:icon>
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
        </svg>
    </x-slot:icon>
    {{ __('email_templates.title') }}
</x-sidebar-link>

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
