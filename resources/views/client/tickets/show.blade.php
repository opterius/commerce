<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('client.tickets.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>
                <h2 class="text-lg font-semibold text-gray-800">#{{ $ticket->id }} — {{ $ticket->subject }}</h2>
                <x-badge :color="$ticket->statusBadgeColor()">{{ __('tickets.status_' . $ticket->status) }}</x-badge>
            </div>
            @if ($ticket->status !== 'closed')
                <form method="POST" action="{{ route('client.tickets.close', $ticket) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-secondary text-sm">{{ __('tickets.close_ticket') }}</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-4">

        {{-- Info bar --}}
        <div class="bg-white rounded-xl shadow-sm px-5 py-3 flex flex-wrap gap-4 text-sm text-gray-500">
            <span>{{ __('tickets.department') }}: <span class="font-medium text-gray-700">{{ $ticket->department?->name }}</span></span>
            <span>{{ __('tickets.priority') }}: <x-badge :color="$ticket->priorityBadgeColor()" size="xs">{{ __('tickets.priority_' . $ticket->priority) }}</x-badge></span>
            <span>{{ __('common.created_at') }}: <span class="font-medium text-gray-700">{{ $ticket->created_at->format('M d, Y') }}</span></span>
        </div>

        {{-- Replies --}}
        @foreach ($ticket->publicReplies as $reply)
            <div class="rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 flex items-center justify-between {{ $reply->isFromStaff() ? 'bg-indigo-50' : 'bg-gray-50' }}">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-800">{{ $reply->authorName() }}</span>
                        @if ($reply->isFromStaff())
                            <x-badge color="indigo">{{ __('tickets.support_team') }}</x-badge>
                        @else
                            <x-badge color="gray">{{ __('tickets.you') }}</x-badge>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400">{{ $reply->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="px-5 py-4 bg-white text-sm text-gray-800 whitespace-pre-wrap">{{ $reply->body }}</div>

                @if ($reply->attachments->isNotEmpty())
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-2">
                        @foreach ($reply->attachments as $att)
                            <a href="{{ route('client.tickets.attachment', [$ticket, $att]) }}"
                               class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-indigo-600 hover:bg-indigo-50">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/></svg>
                                {{ $att->original_name }}
                                <span class="text-gray-400">({{ $att->formattedSize() }})</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Reply form --}}
        @if ($ticket->status !== 'closed')
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('tickets.add_reply') }}</h3>
                <form method="POST" action="{{ route('client.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <textarea name="body" rows="6" class="form-input w-full" required
                                  placeholder="{{ __('tickets.reply_placeholder') }}">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label text-xs">{{ __('tickets.attachments') }}</label>
                        <input type="file" name="attachments[]" multiple
                               class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                    </div>

                    <button type="submit" class="btn-primary">{{ __('tickets.send_reply') }}</button>
                </form>
            </div>
        @else
            <div class="bg-gray-50 rounded-xl p-5 text-center text-sm text-gray-500">
                {{ __('tickets.ticket_is_closed') }}
                <form method="POST" action="{{ route('client.tickets.reply', $ticket) }}" class="inline">
                    @csrf
                    <input type="hidden" name="body" value="{{ __('tickets.reopen_message') }}">
                    {{-- A reply to a closed ticket re-opens it --}}
                </form>
                <a href="{{ route('client.tickets.create') }}" class="text-indigo-600 hover:underline ml-1">{{ __('tickets.open_new_ticket') }}</a>
            </div>
        @endif
    </div>
</x-client-layout>
