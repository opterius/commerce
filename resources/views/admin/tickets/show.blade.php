<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.tickets.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">#{{ $ticket->id }} — {{ $ticket->subject }}</h2>
            <x-badge :color="$ticket->statusBadgeColor()">{{ __('tickets.status_' . $ticket->status) }}</x-badge>
            <x-badge :color="$ticket->priorityBadgeColor()">{{ __('tickets.priority_' . $ticket->priority) }}</x-badge>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{
        replyBody: '',
        isInternal: false,
        cannedId: '',
        cannedResponses: {{ $cannedResponses->map(fn($r) => ['id' => $r->id, 'title' => $r->title, 'body' => $r->body])->toJson() }},
        applyCanned() {
            const resp = this.cannedResponses.find(r => r.id == this.cannedId);
            if (resp) { this.replyBody = resp.body; this.cannedId = ''; }
        },
        mergeOpen: false,
    }">

        {{-- Thread --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Replies --}}
            @foreach ($ticket->replies as $reply)
                <div class="rounded-xl shadow-sm overflow-hidden {{ $reply->is_internal_note ? 'border-2 border-amber-300' : '' }}">
                    <div class="px-5 py-3 flex items-center justify-between
                        {{ $reply->is_internal_note ? 'bg-amber-50' : ($reply->isFromStaff() ? 'bg-indigo-50' : 'bg-white') }}">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-800">{{ $reply->authorName() }}</span>
                            @if ($reply->is_internal_note)
                                <x-badge color="amber">{{ __('tickets.internal_note') }}</x-badge>
                            @elseif ($reply->isFromStaff())
                                <x-badge color="indigo">{{ __('tickets.staff') }}</x-badge>
                            @else
                                <x-badge color="gray">{{ __('tickets.client') }}</x-badge>
                            @endif
                        </div>
                        <span class="text-xs text-gray-400">{{ $reply->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="px-5 py-4 bg-white text-sm text-gray-800 whitespace-pre-wrap">{{ $reply->body }}</div>

                    {{-- Attachments --}}
                    @if ($reply->attachments->isNotEmpty())
                        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-2">
                            @foreach ($reply->attachments as $att)
                                <a href="{{ route('admin.tickets.attachment', [$ticket, $att]) }}"
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
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-800">{{ __('tickets.add_reply') }}</h3>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" x-model="isInternal" class="rounded border-gray-300 text-amber-500">
                            <span :class="isInternal ? 'text-amber-600 font-medium' : 'text-gray-600'">
                                {{ __('tickets.internal_note') }}
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Canned response picker --}}
                <div class="mb-3 flex items-center gap-2">
                    <select x-model="cannedId" @change="applyCanned" class="form-input text-sm w-64">
                        <option value="">{{ __('tickets.insert_canned_response') }}…</option>
                        @foreach ($cannedResponses as $cr)
                            <option value="{{ $cr->id }}">{{ $cr->title }}</option>
                        @endforeach
                    </select>
                </div>

                <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="is_internal_note" :value="isInternal ? '1' : '0'">

                    <textarea
                        name="body"
                        rows="7"
                        x-model="replyBody"
                        class="form-input font-mono text-sm w-full"
                        :class="isInternal ? 'bg-amber-50 border-amber-300' : ''"
                        required
                        placeholder="{{ __('tickets.reply_placeholder') }}"
                    ></textarea>

                    <div class="mt-3">
                        <label class="form-label text-xs">{{ __('tickets.attachments') }}</label>
                        <input type="file" name="attachments[]" multiple class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                    </div>

                    <div class="mt-4 flex gap-3">
                        <button type="submit" class="btn-primary" :class="isInternal ? 'bg-amber-500 hover:bg-amber-600' : ''">
                            <span x-text="isInternal ? '{{ __('tickets.save_note') }}' : '{{ __('tickets.send_reply') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">

            {{-- Ticket properties --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('tickets.ticket_properties') }}</h3>
                <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="form-label">{{ __('common.status') }}</label>
                        <select name="status" class="form-input">
                            @foreach (\App\Models\Ticket::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ $ticket->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">{{ __('tickets.priority') }}</label>
                        <select name="priority" class="form-input">
                            @foreach (\App\Models\Ticket::PRIORITIES as $key => $label)
                                <option value="{{ $key }}" {{ $ticket->priority === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">{{ __('tickets.department') }}</label>
                        <select name="department_id" class="form-input">
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $ticket->department_id === $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">{{ __('tickets.assigned_to') }}</label>
                        <select name="assigned_staff_id" class="form-input">
                            <option value="">— {{ __('common.none') }} —</option>
                            @foreach ($staffList as $staff)
                                <option value="{{ $staff->id }}" {{ $ticket->assigned_staff_id === $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">{{ __('tickets.tags') }}</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach ($tags as $tag)
                                <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                        {{ $ticket->tags->contains($tag->id) ? 'checked' : '' }}
                                        class="rounded border-gray-300">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full" style="background:{{ $tag->color }}"></span>
                                    {{ $tag->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full">{{ __('common.save_changes') }}</button>
                </form>
            </div>

            {{-- Client --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-3">{{ __('common.client') }}</h3>
                <a href="{{ route('admin.clients.show', $ticket->client) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                    {{ $ticket->client->full_name }}
                </a>
                <p class="text-sm text-gray-500 mt-0.5">{{ $ticket->client->email }}</p>
            </div>

            {{-- Timestamps --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-3">{{ __('common.timestamps') }}</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">{{ __('common.created_at') }}</dt>
                        <dd>{{ $ticket->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    @if ($ticket->last_reply_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">{{ __('tickets.last_reply') }}</dt>
                            <dd>{{ $ticket->last_reply_at->format('M d, Y H:i') }}</dd>
                        </div>
                    @endif
                    @if ($ticket->closed_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">{{ __('tickets.closed_at') }}</dt>
                            <dd>{{ $ticket->closed_at->format('M d, Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Merge --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <button @click="mergeOpen = !mergeOpen" class="text-sm font-medium text-gray-600 hover:text-gray-900 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 0-4.5H6v4.5Zm0 0H3.375m2.625 0v4.5m0 0H3.375M6 19.5h.008v.008H6V19.5Z"/></svg>
                    {{ __('tickets.merge_ticket') }}
                </button>
                <div x-show="mergeOpen" x-cloak class="mt-3">
                    <form method="POST" action="{{ route('admin.tickets.merge', $ticket) }}">
                        @csrf
                        <x-input name="merge_into_id" type="number" :label="__('tickets.merge_into_id')" min="1" />
                        <button type="submit" class="mt-2 btn-secondary text-sm">{{ __('tickets.merge') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
