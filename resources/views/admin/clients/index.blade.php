<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('clients.clients') }}</h2>
            <a href="{{ route('admin.clients.create') }}">
                <x-button type="button">{{ __('clients.create_client') }}</x-button>
            </a>
        </div>
    </x-slot>

    {{-- Search / Filter bar --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex flex-col sm:flex-row items-end gap-4">
            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.search') }}</label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ __('common.search') }}..."
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                />
            </div>

            {{-- Status filter --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('clients.status') }}</label>
                <select name="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">{{ __('common.all') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('clients.status_active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('clients.status_inactive') }}</option>
                    <option value="closed" @selected(request('status') === 'closed')>{{ __('clients.status_closed') }}</option>
                </select>
            </div>

            {{-- Group filter --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('clients.group') }}</label>
                <select name="group" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" @selected(request('group') == $group->id)>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter button --}}
            <x-button type="submit">{{ __('common.filter') }}</x-button>
        </form>
    </div>

    {{-- Clients table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.email') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('clients.company_name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('clients.group') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.created_at') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($clients as $client)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $client->first_name }} {{ $client->last_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $client->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $client->company_name ?: "\u{2014}" }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($client->group)
                                    <x-badge :color="$client->group->color ?? 'gray'">{{ $client->group->name }}</x-badge>
                                @else
                                    <span class="text-sm text-gray-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColor = match($client->status) {
                                        'active'   => 'green',
                                        'inactive' => 'amber',
                                        'closed'   => 'red',
                                        default    => 'gray',
                                    };
                                @endphp
                                <x-badge :color="$statusColor">{{ __('clients.status_' . $client->status) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $client->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('admin.clients.show', $client) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">{{ __('common.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-400">
                                {{ __('common.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($clients->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $clients->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
