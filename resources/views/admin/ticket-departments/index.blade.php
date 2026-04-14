<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('tickets.departments') }}</h2>
            <a href="{{ route('admin.ticket-departments.create') }}" class="btn-primary">{{ __('tickets.create_department') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($departments->isEmpty())
            <x-empty-state :message="__('tickets.no_departments')" />
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.dept_email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.tickets') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($departments as $dept)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.ticket-departments.edit', $dept) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $dept->name }}
                                    </a>
                                    @if ($dept->description)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($dept->description, 60) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $dept->email ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $dept->tickets_count }}</td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$dept->is_active ? 'green' : 'gray'">
                                        {{ $dept->is_active ? __('common.active') : __('common.inactive') }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.ticket-departments.edit', $dept) }}" class="btn-secondary py-1 px-3 text-xs">{{ __('common.edit') }}</a>
                                        <x-delete-modal
                                            :action="route('admin.ticket-departments.destroy', $dept)"
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
    @push('modals')
    @endpush
</x-admin-layout>
