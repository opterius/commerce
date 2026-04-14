<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('clients.client_groups') }}</h2>
            <a href="{{ route('admin.client-groups.create') }}" class="btn-primary">{{ __('clients.create_group') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($groups->isEmpty())
            <x-empty-state :message="__('clients.no_groups')" />
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('clients.discount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('clients.clients_count') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($groups as $grp)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($grp->color)
                                            <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $grp->color }};"></span>
                                        @endif
                                        <a href="{{ route('admin.client-groups.edit', $grp) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                            {{ $grp->name }}
                                        </a>
                                    </div>
                                    @if ($grp->description)
                                        <p class="text-xs text-gray-400 mt-1 ml-6">{{ Str::limit($grp->description, 80) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    @if ($grp->discount_percent > 0)
                                        <span class="font-semibold text-green-600">{{ number_format($grp->discount_percent / 100, 2) }}%</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $grp->clients_count }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.client-groups.edit', $grp) }}" class="btn-secondary py-1 px-3 text-xs">{{ __('common.edit') }}</a>
                                        <x-delete-modal
                                            :action="route('admin.client-groups.destroy', $grp)"
                                            label="{{ __('common.delete') }}"
                                            :confirmMessage="__('clients.delete_group_warning')"
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
