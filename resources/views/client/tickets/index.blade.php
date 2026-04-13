<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('tickets.my_tickets') }}</h2>
            <a href="{{ route('client.tickets.create') }}" class="btn-primary">{{ __('tickets.open_ticket') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">

        {{-- Status filter --}}
        <form method="GET" class="flex items-center gap-3">
            <select name="status" class="form-input w-40 text-sm" onchange="this.form.submit()">
                <option value="">{{ __('common.all') }}</option>
                @foreach (\App\Models\Ticket::STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        @if ($tickets->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <p class="text-gray-400 mb-4">{{ __('tickets.no_tickets_yet') }}</p>
                <a href="{{ route('client.tickets.create') }}" class="btn-primary">{{ __('tickets.open_first_ticket') }}</a>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.subject') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.department') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.last_reply') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($tickets as $ticket)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-400">#{{ $ticket->id }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('client.tickets.show', $ticket) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $ticket->subject }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $ticket->department?->name ?? '—' }}</td>
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
</x-client-layout>
