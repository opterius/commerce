<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('kb.categories') }}</h2>
            <a href="{{ route('admin.kb-categories.create') }}" class="btn-primary">{{ __('kb.create_category') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($categories->isEmpty())
            <x-empty-state :message="__('kb.no_categories')" />
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.slug') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('kb.articles') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($categories as $cat)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.kb-categories.edit', $cat) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $cat->name }}
                                    </a>
                                    @if ($cat->description)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($cat->description, 80) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $cat->slug }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $cat->articles_count }}</td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$cat->is_visible ? 'green' : 'gray'">
                                        {{ $cat->is_visible ? __('common.visible') : __('common.hidden') }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.kb-categories.edit', $cat) }}" class="btn-secondary py-1 px-3 text-xs">{{ __('common.edit') }}</a>
                                        <x-delete-modal
                                            :action="route('admin.kb-categories.destroy', $cat)"
                                            label="{{ __('common.delete') }}"
                                            :confirmMessage="__('kb.delete_category_warning')"
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
