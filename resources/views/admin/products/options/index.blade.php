<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('products.configurable_options') }}</h2>
            <a href="{{ route('admin.configurable-options.create') }}">
                <x-button type="button">{{ __('products.create_option_group') }}</x-button>
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.description') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.options') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.linked_products') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($groups as $group)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $group->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ Str::limit($group->description, 50) ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge color="blue">{{ $group->options_count }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge color="indigo">{{ $group->products_count }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                <a href="{{ route('admin.configurable-options.show', $group) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">{{ __('common.view') }}</a>
                                <x-danger-button
                                    type="button"
                                    x-data=""
                                    x-on:click="$dispatch('open-modal', 'delete-group-{{ $group->id }}')"
                                    class="!px-3 !py-1 !text-xs"
                                >
                                    {{ __('common.delete') }}
                                </x-danger-button>
                            </td>
                        </tr>

                        @push('modals')
                            <x-delete-modal
                                name="delete-group-{{ $group->id }}"
                                :title="__('common.are_you_sure')"
                                :message="__('common.this_action_cannot_be_undone')"
                                :action="route('admin.configurable-options.destroy', $group)"
                            />
                        @endpush
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">
                                {{ __('products.no_option_groups') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
