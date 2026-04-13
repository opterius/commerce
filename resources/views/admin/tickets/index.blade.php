<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('tickets.tickets') }}</h2>
    </x-slot>

    <div class="space-y-6">

        {{-- Filters --}}
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-4 items-end">
            <div>
                <label class="form-label">{{ __('tickets.department') }}</label>
                <select name="department_id" class="form-input w-44">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('common.status') }}</label>
                <select name="status" class="form-input w-40">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach (\App\Models\Ticket::STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('tickets.priority') }}</label>
                <select name="priority" class="form-input w-36">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach (\App\Models\Ticket::PRIORITIES as $key => $label)
                        <option value="{{ $key }}" {{ request('priority') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('tickets.assigned_to') }}</label>
                <select name="assigned_staff_id" class="form-input w-44">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach ($staffList as $staff)
                        <option value="{{ $staff->id }}" {{ request('assigned_staff_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('common.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="{{ __('tickets.search_placeholder') }}">
            </div>
            <button type="submit" class="btn-primary">{{ __('common.filter') }}</button>
            @if (request()->hasAny(['department_id','status','priority','assigned_staff_id','search']))
                <a href="{{ route('admin.tickets.index') }}" class="btn-secondary">{{ __('common.all') }}</a>
            @endif
        </form>

        @if ($tickets->isEmpty())
            <x-empty-state :message="__('tickets.no_tickets')" />
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.subject') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.client') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.department') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.priority') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.last_reply') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($tickets as $ticket)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-400">#{{ $ticket->id }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $ticket->subject }}
                                    </a>
                                    @if ($ticket->assignedStaff)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ __('tickets.assigned_to') }}: {{ $ticket->assignedStaff->name }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.clients.show', $ticket->client) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                        {{ $ticket->client->full_name }}
                                    </a>
                                    <p class="text-xs text-gray-400">{{ $ticket->client->email }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $ticket->department?->name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$ticket->priorityBadgeColor()">{{ __('tickets.priority_' . $ticket->priority) }}</x-badge>
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$ticket->statusBadgeColor()">{{ __('tickets.status_' . $ticket->status) }}</x-badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $ticket->last_reply_at?->diffForHumans() ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>{{ $tickets->links() }}</div>
        @endif
    </div>
</x-admin-layout>
