<x-guest-layout>
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('auth.reset_password') }}</h1>
            </div>

            <form method="POST" action="{{ $guard === 'staff' ? route('staff.password.update') : route('client.password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

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
                            value="{{ old('email', request('email')) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('email') border-red-300 @enderror"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('auth.new_password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('password') border-red-300 @enderror"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('auth.confirm_password') }}</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                    </div>

                    {{-- Submit --}}
                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            {{ __('auth.reset_password') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
