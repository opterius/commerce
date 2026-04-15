<x-client-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('api_tokens.title') }}</h2>
    </x-slot>

    <div class="max-w-2xl space-y-6">

        {{-- New token just created --}}
        @if (session('new_token'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-green-900 mb-1">{{ __('api_tokens.created_success', ['name' => session('new_token_name')]) }}</h3>
                <p class="text-xs text-green-700 mb-3">{{ __('api_tokens.copy_warning') }}</p>
                <div class="bg-white border border-green-300 rounded-lg px-4 py-3 font-mono text-sm text-gray-900 break-all select-all">
                    {{ session('new_token') }}
                </div>
            </div>
        @endif

        {{-- Create token --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-1">{{ __('api_tokens.create_token') }}</h3>
            <p class="text-sm text-gray-500 mb-4">{{ __('api_tokens.create_help') }}</p>
            <form method="POST" action="{{ route('client.api-tokens.store') }}" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label class="form-label">{{ __('api_tokens.token_name') }}</label>
                    <input type="text" name="name" class="form-input w-full" placeholder="{{ __('api_tokens.name_placeholder') }}" required maxlength="100">
                </div>
                <button type="submit" class="btn-primary">{{ __('api_tokens.create') }}</button>
            </form>
        </div>

        {{-- Token list --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">{{ __('api_tokens.your_tokens') }}</h3>
            </div>

            @if ($tokens->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-400">
                    {{ __('api_tokens.no_tokens') }}
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($tokens as $token)
                        <div class="flex items-center justify-between px-6 py-4"
                             x-data="{ showRevoke: false }">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $token->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ __('api_tokens.created') }}: {{ $token->created_at->format('M d, Y') }}
                                    @if ($token->last_used_at)
                                        &middot; {{ __('api_tokens.last_used') }}: {{ $token->last_used_at->diffForHumans() }}
                                    @else
                                        &middot; {{ __('api_tokens.never_used') }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                <button @click="showRevoke = true"
                                        class="text-sm text-red-600 hover:text-red-800 font-medium">
                                    {{ __('api_tokens.revoke') }}
                                </button>
                            </div>

                            {{-- Revoke confirmation --}}
                            <div x-show="showRevoke" x-cloak
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                                 @keydown.escape.window="showRevoke = false">
                                <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4" @click.stop>
                                    <h3 class="text-base font-semibold text-gray-900 mb-1">{{ __('api_tokens.revoke_confirm_title') }}</h3>
                                    <p class="text-sm text-gray-500 mb-4">{{ __('api_tokens.revoke_confirm_body', ['name' => $token->name]) }}</p>
                                    <form method="POST" action="{{ route('client.api-tokens.destroy', $token) }}">
                                        @csrf
                                        @method('DELETE')
                                        <div class="mb-4">
                                            <label class="form-label">{{ __('common.confirm_password') }}</label>
                                            <input type="password" name="password" class="form-input w-full" required autofocus>
                                        </div>
                                        <div class="flex gap-3 justify-end">
                                            <button type="button" @click="showRevoke = false" class="btn-secondary">{{ __('common.cancel') }}</button>
                                            <button type="submit" class="btn-danger">{{ __('api_tokens.revoke') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- API Reference --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('api_tokens.reference_title') }}</h3>
            <p class="text-sm text-gray-500 mb-4">{{ __('api_tokens.reference_desc') }}</p>
            <div class="rounded-lg bg-gray-900 text-gray-100 px-4 py-3 font-mono text-sm overflow-x-auto">
                curl -H "Authorization: Bearer YOUR_TOKEN" \<br>
                &nbsp;&nbsp;&nbsp;&nbsp;{{ url('/api/v1/me') }}
            </div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-500">
                <div><span class="font-mono text-gray-700">GET /api/v1/me</span> — {{ __('api_tokens.endpoint_me') }}</div>
                <div><span class="font-mono text-gray-700">GET /api/v1/services</span> — {{ __('api_tokens.endpoint_services') }}</div>
                <div><span class="font-mono text-gray-700">GET /api/v1/invoices</span> — {{ __('api_tokens.endpoint_invoices') }}</div>
                <div><span class="font-mono text-gray-700">GET /api/v1/invoices/{id}</span> — {{ __('api_tokens.endpoint_invoice') }}</div>
                <div><span class="font-mono text-gray-700">GET /api/v1/orders</span> — {{ __('api_tokens.endpoint_orders') }}</div>
                <div><span class="font-mono text-gray-700">GET /api/v1/domains</span> — {{ __('api_tokens.endpoint_domains') }}</div>
            </div>
        </div>

    </div>
</x-client-layout>
