<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('settings.settings') }}</h2>
    </x-slot>

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Settings navigation --}}
        <div class="w-full lg:w-56 flex-shrink-0">
            <nav class="bg-white rounded-xl shadow-sm p-2 space-y-1">
                @foreach (['company', 'branding', 'portal', 'currencies', 'billing', 'invoices', 'tickets', 'registrar'] as $cat)
                    <a
                        href="{{ route('admin.settings', $cat) }}"
                        class="block px-4 py-2.5 rounded-lg text-sm font-medium transition {{ $category === $cat ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        {{ __('settings.nav_' . $cat) }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            @if ($category === 'company')
                @include('admin.settings._company')
            @elseif ($category === 'branding')
                @include('admin.settings._branding')
            @elseif ($category === 'currencies')
                @include('admin.settings._currencies')
            @elseif ($category === 'billing')
                @include('admin.settings._billing')
            @elseif ($category === 'tickets')
                @include('admin.settings._tickets')
            @elseif ($category === 'registrar')
                @include('admin.settings._registrar')
            @elseif ($category === 'portal')
                @include('admin.settings._portal')
            @elseif ($category === 'invoices')
                @include('admin.settings._invoices')
            @endif
        </div>
    </div>
</x-admin-layout>
