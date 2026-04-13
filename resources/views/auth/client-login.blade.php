<x-guest-layout>
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('auth.client_login_title') }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ __('auth.client_login_subtitle') }}</p>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('client.login') }}">
                @csrf

                <div class="space-y-5">
                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('auth.email') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            value="{{ old('email') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('email') border-red-300 @enderror"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('auth.password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('password') border-red-300 @enderror"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember me & Forgot password --}}
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="remember"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            >
                            <span class="ml-2 text-sm text-gray-600">{{ __('auth.remember_me') }}</span>
                        </label>

                        <a href="{{ route('client.password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            {{ __('auth.forgot_password') }}
                        </a>
                    </div>

                    {{-- Submit --}}
                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            {{ __('auth.login') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-600">
            <a href="{{ route('staff.login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                {{ __('auth.staff_login_link') }}
            </a>
        </p>
    </div>
</x-guest-layout>
