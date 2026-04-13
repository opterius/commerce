<x-guest-layout>
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <div class="flex items-center justify-center w-14 h-14 rounded-full bg-indigo-100 mx-auto mb-4">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Two-Factor Authentication</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Enter the 6-digit code from your authenticator app, or one of your backup codes.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-4">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-800">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('client.two-factor.challenge') }}">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Authentication Code</label>
                        <input
                            id="code"
                            name="code"
                            type="text"
                            maxlength="10"
                            autocomplete="one-time-code"
                            autofocus
                            required
                            placeholder="000000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-center text-xl tracking-widest font-mono focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('code') border-red-300 @enderror"
                        >
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Verify
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-600">
            <a href="{{ route('client.login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                &larr; Back to login
            </a>
        </p>
    </div>
</x-guest-layout>
