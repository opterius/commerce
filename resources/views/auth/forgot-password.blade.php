<x-guest-layout>
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('auth.forgot_password') }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ __('auth.forgot_password_text') }}</p>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ $guard === 'staff' ? route('staff.password.email') : route('client.password.email') }}">
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

                    {{-- Submit --}}
                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            {{ __('auth.send_reset_link') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-600">
            <a href="{{ $guard === 'staff' ? route('staff.login') : route('client.login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                {{ __('auth.back_to_login') }}
            </a>
        </p>
    </div>
</x-guest-layout>
