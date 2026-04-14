<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('announcements.status_page') }}</h2>
            <a href="{{ route('admin.service-statuses.create') }}" class="btn-primary">{{ __('announcements.add_component') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($components->isEmpty())
            <x-empty-state :message="__('announcements.no_components')" />
        @else
            @php
                $statusBadge = [
                    'operational' => 'green',
                    'degraded'    => 'amber',
                    'outage'      => 'red',
                    'maintenance' => 'blue',
                ];
            @endphp
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('announcements.status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.sort_order') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($components as $c)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.service-statuses.edit', $c) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $c->name }}
                                    </a>
                                    @if ($c->description)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($c->description, 80) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$statusBadge[$c->status] ?? 'gray'">
                                        {{ __('announcements.status_' . $c->status) }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $c->sort_order }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.service-statuses.edit', $c) }}" class="btn-secondary py-1 px-3 text-xs">{{ __('common.edit') }}</a>
                                        <x-delete-modal
                                            :action="route('admin.service-statuses.destroy', $c)"
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
        @endif
    </div>
</x-admin-layout>
