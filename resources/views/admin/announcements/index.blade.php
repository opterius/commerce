<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('announcements.title') }}</h2>
            <a href="{{ route('admin.announcements.create') }}" class="btn-primary">{{ __('announcements.create') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($announcements->isEmpty())
            <x-empty-state :message="__('announcements.no_announcements')" />
        @else
            @php
                $priorityBadge = [
                    'info'     => 'blue',
                    'success'  => 'green',
                    'warning'  => 'amber',
                    'critical' => 'red',
                ];
            @endphp
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('announcements.field_title') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('announcements.priority') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('announcements.visibility') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('announcements.published') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('announcements.expires') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($announcements as $ann)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.announcements.edit', $ann) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $ann->title }}
                                    </a>
                                    @if ($ann->is_featured)
                                        <span class="ml-2 text-xs text-amber-600">★ {{ __('announcements.featured') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$priorityBadge[$ann->priority] ?? 'gray'">
                                        {{ __('announcements.priority_' . $ann->priority) }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div class="flex flex-col gap-0.5 text-xs">
                                        @if ($ann->show_public)  <span class="text-gray-600">🌐 {{ __('announcements.public') }}</span> @endif
                                        @if ($ann->show_client)  <span class="text-gray-600">👤 {{ __('announcements.client_area') }}</span> @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $ann->published_at?->format('Y-m-d H:i') ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $ann->expires_at?->format('Y-m-d H:i') ?: '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.announcements.edit', $ann) }}" class="btn-secondary py-1 px-3 text-xs">{{ __('common.edit') }}</a>
                                        <x-delete-modal
                                            :action="route('admin.announcements.destroy', $ann)"
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
            <div>{{ $announcements->links() }}</div>
        @endif
    </div>
</x-admin-layout>
