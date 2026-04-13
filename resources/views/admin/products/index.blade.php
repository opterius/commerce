<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('products.products') }}</h2>
            <a href="{{ route('admin.products.create') }}">
                <x-button type="button">{{ __('products.create_product') }}</x-button>
            </a>
        </div>
    </x-slot>

    {{-- Search / Filter bar --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-col sm:flex-row items-end gap-4">
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

            {{-- Group filter --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.group') }}</label>
                <select name="group" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" @selected(request('group') == $group->id)>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status filter --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.status') }}</label>
                <select name="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">{{ __('common.all') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('products.status_active') }}</option>
                    <option value="hidden" @selected(request('status') === 'hidden')>{{ __('products.status_hidden') }}</option>
                    <option value="retired" @selected(request('status') === 'retired')>{{ __('products.status_retired') }}</option>
                </select>
            </div>

            {{-- Filter button --}}
            <x-button type="submit">{{ __('common.filter') }}</x-button>
        </form>
    </div>

    {{-- Products table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.group') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.sort_order') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($product->productGroup)
                                    <x-badge color="indigo">{{ $product->productGroup->name }}</x-badge>
                                @else
                                    <span class="text-sm text-gray-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge color="blue">{{ __('products.type_' . $product->type) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColor = match($product->status) {
                                        'active'  => 'green',
                                        'hidden'  => 'amber',
                                        'retired' => 'red',
                                        default   => 'gray',
                                    };
                                @endphp
                                <x-badge :color="$statusColor">{{ __('products.status_' . $product->status) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sort_order }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('admin.products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">{{ __('common.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">
                                {{ __('products.no_products') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($products->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $products->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
