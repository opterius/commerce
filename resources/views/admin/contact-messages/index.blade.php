<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">
                {{ __('contact.inbox') }}
                @if ($unreadCount > 0)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">{{ $unreadCount }} {{ __('contact.unread') }}</span>
                @endif
            </h2>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Filters --}}
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-3">
            <select name="status" class="form-input max-w-xs" onchange="this.form.submit()">
                <option value="">— {{ __('common.all') }} —</option>
                <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>{{ __('contact.unread') }}</option>
                <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>{{ __('contact.read') }}</option>
            </select>
        </form>

        @if ($messages->isEmpty())
            <x-empty-state :message="__('contact.no_messages')" />
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('contact.subject') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.created_at') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($messages as $msg)
                            <tr class="hover:bg-gray-50 {{ $msg->is_read ? '' : 'font-semibold' }}">
                                <td class="px-6 py-4">
                                    @if (! $msg->is_read)
                                        <span class="inline-block w-2 h-2 rounded-full bg-indigo-600 mr-2" title="{{ __('contact.unread') }}"></span>
                                    @endif
                                    <a href="{{ route('admin.contact-messages.show', $msg) }}" class="text-sm {{ $msg->is_read ? 'text-gray-700' : 'text-indigo-600' }} hover:text-indigo-900">
                                        {{ $msg->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $msg->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($msg->subject, 50) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $msg->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.contact-messages.show', $msg) }}" class="btn-secondary py-1 px-3 text-xs">{{ __('common.view') }}</a>
                                        <x-delete-modal
                                            :action="route('admin.contact-messages.destroy', $msg)"
                                            label="{{ __('common.delete') }}"
                                            :confirmMessage="__('common.are_you_sure')"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>{{ $messages->links() }}</div>
        @endif
    </div>
</x-admin-layout>
