<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('products.product_groups') }}</h2>
            <a href="{{ route('admin.product-groups.create') }}">
                <x-button type="button">{{ __('products.create_group') }}</x-button>
            </a>
        </div>
    </x-slot>

    {{-- Groups table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.slug') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.products') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.visible') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.sort_order') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($groups as $group)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $group->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $group->slug }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ __('products.products_count', ['count' => $group->products_count]) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge :color="$group->is_visible ? 'green' : 'gray'">
                                    {{ $group->is_visible ? __('products.visible') : __('products.hidden') }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $group->sort_order }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                <a href="{{ route('admin.product-groups.edit', $group) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">{{ __('common.edit') }}</a>
                                <button
                                    type="button"
                                    x-data=""
                                    x-on:click="$dispatch('open-modal', 'delete-group-{{ $group->id }}')"
                                    class="text-red-600 hover:text-red-900 font-medium"
                                >
                                    {{ __('common.delete') }}
                                </button>
                            </td>
                        </tr>

                        @push('modals')
                            <x-delete-modal
                                name="delete-group-{{ $group->id }}"
                                :title="__('products.delete_group')"
                                :message="__('products.delete_group_confirm')"
                                :action="route('admin.product-groups.destroy', $group)"
                            />
                        @endpush
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">
                                {{ __('products.no_groups') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
