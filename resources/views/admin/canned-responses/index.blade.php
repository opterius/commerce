<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('tickets.canned_responses') }}</h2>
            <a href="{{ route('admin.canned-responses.create') }}" class="btn-primary">{{ __('tickets.create_canned_response') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($responses->isEmpty())
            <x-empty-state :message="__('tickets.no_canned_responses')" />
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.department') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tickets.created_by') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($responses as $response)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $response->title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($response->body, 80) }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $response->department?->name ?? __('tickets.all_departments') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $response->staff?->name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3 text-sm">
                                        <a href="{{ route('admin.canned-responses.edit', $response) }}" class="text-indigo-600 hover:underline">{{ __('common.edit') }}</a>
                                        <x-delete-modal
                                            :action="route('admin.canned-responses.destroy', $response)"
                                            :label="__('common.delete')"
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
