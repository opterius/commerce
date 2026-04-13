<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('common.app_name') }} - {{ __('common.client_area') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">
    {{-- Impersonation bar --}}
    @if (session('impersonating_staff_id'))
        <div class="bg-amber-500 text-white text-center text-sm py-2 px-4 flex items-center justify-center gap-3">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <span>{{ __('common.impersonating_client', ['name' => auth()->user()->name ?? '']) }}</span>
            <a href="{{ route('admin.clients.stop-impersonation') }}" class="underline font-medium hover:no-underline">
                {{ __('common.stop_impersonating') }}
            </a>
        </div>
    @endif

    <div class="min-h-screen flex">
        {{-- Mobile sidebar overlay --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-gray-600/75 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:flex lg:flex-col"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex flex-col h-full">
                {{-- Logo --}}
                <div class="flex items-center h-16 px-6 border-b border-gray-800">
                    <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                        </div>
                        <span class="text-white font-semibold text-lg">{{ __('common.app_name') }}</span>
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    @include('partials.client-sidebar')
                </nav>

                {{-- Client info --}}
                <div class="border-t border-gray-800 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-300">
                                {{ substr(auth()->user()->name ?? 'C', 0, 1) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-200 truncate">
                                {{ auth()->user()->name ?? __('common.client') }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('client.logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-white" title="{{ __('common.logout') }}">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            @include('partials.client-topbar')

            {{-- Flash messages --}}
            @include('partials.flash-messages')

            {{-- Page content --}}
            <main class="flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')
</body>
</html>
