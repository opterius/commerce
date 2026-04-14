<header class="bg-white border-b border-gray-200">
    <div class="flex items-center justify-between h-16 px-6">
        {{-- Left: mobile menu toggle --}}
        <div class="flex items-center gap-4">
            {{-- Mobile menu button --}}
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="text-gray-500 hover:text-gray-700 lg:hidden"
            >
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </div>

        {{-- Right: staff dropdown --}}
        <div class="relative" x-data="{ open: false }">
            <button
                @click="open = !open"
                @click.outside="open = false"
                class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none"
            >
                <span>{{ auth()->user()->name ?? __('common.admin') }}</span>
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            {{-- Dropdown menu --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                style="display: none;"
            >
                <x-dropdown-link :href="route('admin.profile')">
                    {{ __('navigation.profile') }}
                </x-dropdown-link>

                <div class="border-t border-gray-100 my-1"></div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <x-dropdown-link
                        :href="route('admin.logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                    >
                        {{ __('common.logout') }}
                    </x-dropdown-link>
                </form>
            </div>
        </div>
    </div>
</header>
