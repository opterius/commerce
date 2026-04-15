<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('client.services.show', $service) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('cancellations.request_cancellation') }}</h2>
        </div>
    </x-slot>

    <div class="max-w-xl">
        <div class="bg-white rounded-xl shadow-sm p-6">

            <div class="mb-5 p-4 rounded-lg bg-amber-50 border border-amber-200">
                <p class="text-sm font-medium text-amber-900">{{ __('cancellations.cancelling_service') }}:</p>
                <p class="text-sm text-amber-800 mt-0.5 font-mono">
                    {{ $service->domain ?: ($service->product?->name ?? 'Service #' . $service->id) }}
                </p>
            </div>

            <form method="POST" action="{{ route('client.services.cancel.store', $service) }}" class="space-y-5">
                @csrf

                {{-- Cancel type --}}
                <div>
                    <label class="form-label">{{ __('cancellations.cancel_type') }}</label>
                    <div class="mt-2 space-y-2">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="radio" name="cancel_type" value="end_of_period" checked
                                   class="mt-0.5 rounded-full border-gray-300">
                            <div>
                                <span class="text-sm font-medium text-gray-800">{{ __('cancellations.end_of_period') }}</span>
                                <p class="text-xs text-gray-400 mt-0.5">{{ __('cancellations.end_of_period_help') }}</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="radio" name="cancel_type" value="immediate"
                                   class="mt-0.5 rounded-full border-gray-300">
                            <div>
                                <span class="text-sm font-medium text-gray-800">{{ __('cancellations.immediate') }}</span>
                                <p class="text-xs text-gray-400 mt-0.5">{{ __('cancellations.immediate_help') }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="form-label" for="reason">{{ __('cancellations.reason') }}</label>
                    <textarea name="reason" id="reason" rows="4" class="form-input"
                        placeholder="{{ __('cancellations.reason_placeholder') }}"
                        required maxlength="2000">{{ old('reason') }}</textarea>
                    <p class="mt-1 text-xs text-gray-400">{{ __('cancellations.reason_help') }}</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-danger">{{ __('cancellations.submit_request') }}</button>
                    <a href="{{ route('client.services.show', $service) }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-client-layout>
