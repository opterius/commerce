<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('faq.title') }}</h2>
            <a href="{{ route('admin.faqs.create') }}" class="btn-primary">{{ __('faq.create') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($faqs->isEmpty())
            <x-empty-state :message="__('faq.no_faqs')" />
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('faq.question') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.sort_order') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($faqs as $faq)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.faqs.edit', $faq) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ Str::limit($faq->question, 100) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $faq->sort_order }}</td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$faq->is_published ? 'green' : 'gray'">
                                        {{ $faq->is_published ? __('kb.published') : __('kb.draft') }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn-secondary py-1 px-3 text-xs">{{ __('common.edit') }}</a>
                                        <x-delete-modal
                                            :action="route('admin.faqs.destroy', $faq)"
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
