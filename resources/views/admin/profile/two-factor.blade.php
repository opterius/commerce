<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('navigation.two_factor_auth') }}</h2>
    </x-slot>

    <div class="max-w-2xl space-y-6">

        {{-- Status & flash messages --}}
        @if (session('status'))
            <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3">
                <p class="text-sm text-green-800">{{ session('status') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Backup codes flash --}}
        @if (isset($backupCodes) && $backupCodes)
            <div class="rounded-xl bg-amber-50 border border-amber-200 p-6">
                <p class="text-sm font-semibold text-amber-900 mb-3">
                    Save these backup codes somewhere safe — they won't be shown again.
                </p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($backupCodes as $code)
                        <code class="block bg-white border border-amber-200 rounded px-3 py-1.5 text-sm font-mono text-gray-800 text-center tracking-widest">
                            {{ $code }}
                        </code>
                    @endforeach
                </div>
            </div>
        @endif

        @if (isset($sessionSecret) && $sessionSecret)
            {{-- Step 2: Scan QR code and confirm --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-2">Scan with your authenticator app</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Use an authenticator app (Google Authenticator, Authy, 1Password, etc.) to scan this QR code.
                </p>

                {{-- QR code rendered by qrcodejs --}}
                <div id="qrcode" class="flex justify-center mb-4"></div>
                <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        new QRCode(document.getElementById('qrcode'), {
                            text: "{{ addslashes($provisioningUri) }}",
                            width: 200,
                            height: 200,
                        });
                    });
                </script>

                <p class="text-sm text-gray-600 mb-1 text-center">Or enter this secret manually:</p>
                <div class="flex justify-center">
                    <code class="bg-gray-100 px-4 py-2 rounded text-sm font-mono tracking-widest text-gray-800">
                        {{ $sessionSecret }}
                    </code>
                </div>

                <hr class="my-6">

                <h3 class="text-base font-semibold text-gray-800 mb-2">Confirm your authenticator code</h3>
                <p class="text-sm text-gray-600 mb-4">Enter the 6-digit code from your authenticator app to activate 2FA.</p>

                @if ($errors->has('code'))
                    <div class="mb-3 rounded-md bg-red-50 border border-red-200 px-4 py-2">
                        <p class="text-sm text-red-800">{{ $errors->first('code') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('staff.two-factor.confirm') }}" class="flex gap-3">
                    @csrf
                    <input
                        name="code"
                        type="text"
                        maxlength="6"
                        autocomplete="one-time-code"
                        placeholder="000000"
                        required
                        class="flex-1 rounded-md border-gray-300 shadow-sm font-mono text-center text-lg tracking-widest focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                        Activate 2FA
                    </button>
                </form>
            </div>

        @elseif ($staff->two_factor_confirmed_at)
            {{-- 2FA is enabled --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <x-badge color="green">2FA is enabled</x-badge>
                    <span class="text-sm text-gray-500">
                        Activated {{ $staff->two_factor_confirmed_at->format('M d, Y') }}
                    </span>
                </div>
                <p class="text-sm text-gray-600">
                    Two-factor authentication is active on your account. You'll be asked for a code on every login.
                </p>
            </div>

            {{-- Regenerate backup codes --}}
            <div class="bg-white rounded-xl shadow-sm p-6" x-data="{ open: false }">
                <h3 class="text-base font-semibold text-gray-800 mb-1">Backup Codes</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Regenerate your backup codes if you've lost them. Old codes will be invalidated.
                </p>
                <button @click="open = !open"
                    class="px-4 py-2 rounded-md bg-white border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Regenerate Backup Codes
                </button>
                <div x-show="open" x-cloak class="mt-4">
                    @if ($errors->has('password') && request()->is('*/recovery-codes'))
                        <div class="mb-3 rounded-md bg-red-50 border border-red-200 px-4 py-2">
                            <p class="text-sm text-red-800">{{ $errors->first('password') }}</p>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('staff.two-factor.recovery-codes') }}" class="flex gap-3 items-center">
                        @csrf
                        <input
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Confirm your password"
                            required
                            class="flex-1 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        <button type="submit"
                            class="px-4 py-2 rounded-md bg-amber-600 text-white text-sm font-medium hover:bg-amber-700 transition">
                            Regenerate
                        </button>
                    </form>
                </div>
            </div>

            {{-- Disable 2FA --}}
            <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6" x-data="{ open: false }">
                <h3 class="text-base font-semibold text-red-700 mb-1">Danger Zone</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Disabling 2FA will remove the extra layer of protection from your account.
                </p>
                <button @click="open = !open"
                    class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition">
                    Disable 2FA
                </button>
                <div x-show="open" x-cloak class="mt-4">
                    @if ($errors->has('password') && request()->is('*/disable'))
                        <div class="mb-3 rounded-md bg-red-50 border border-red-200 px-4 py-2">
                            <p class="text-sm text-red-800">{{ $errors->first('password') }}</p>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('staff.two-factor.disable') }}" class="flex gap-3 items-center">
                        @csrf
                        <input
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Confirm your password"
                            required
                            class="flex-1 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        <button type="submit"
                            class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition">
                            Confirm Disable
                        </button>
                    </form>
                </div>
            </div>

        @else
            {{-- 2FA not enabled --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <x-badge color="gray">2FA is disabled</x-badge>
                </div>
                <h3 class="text-base font-semibold text-gray-800 mb-2">Protect your account with 2FA</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Two-factor authentication adds an extra layer of security to your account. When enabled, you'll need
                    to enter a time-based code from your authenticator app on every login.
                </p>
                <form method="POST" action="{{ route('staff.two-factor.enable') }}">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                        Enable 2FA
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-admin-layout>
