<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.contact-messages.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('contact.message_detail') }}</h2>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('contact.from') }}</p>
                <p class="text-sm font-semibold text-gray-900">{{ $message->name }}</p>
                <p class="text-sm text-gray-500">
                    <a href="mailto:{{ $message->email }}" class="text-indigo-600 hover:underline">{{ $message->email }}</a>
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('contact.subject') }}</p>
                <p class="text-base font-semibold text-gray-900">{{ $message->subject }}</p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('contact.message_body') }}</p>
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $message->message }}</div>
            </div>

            <div class="grid grid-cols-2 gap-5 pt-4 border-t border-gray-100 text-xs text-gray-500">
                <div>
                    <span class="text-gray-400">{{ __('common.created_at') }}:</span>
                    <span class="text-gray-700">{{ $message->created_at->format('Y-m-d H:i') }}</span>
                </div>
                @if ($message->ip_address)
                    <div>
                        <span class="text-gray-400">IP:</span>
                        <span class="text-gray-700 font-mono">{{ $message->ip_address }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <a href="mailto:{{ $message->email }}?subject=Re: {{ urlencode($message->subject) }}" class="btn-primary">
                {{ __('contact.reply_by_email') }}
            </a>
            <x-delete-modal
                :action="route('admin.contact-messages.destroy', $message)"
                :label="__('common.delete')"
                :confirmMessage="__('common.are_you_sure')"
                buttonClass="btn-danger"
            />
        </div>
    </div>
</x-admin-layout>
