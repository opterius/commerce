<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('products.promo_codes') }}</h2>
            <a href="{{ route('admin.promo-codes.create') }}">
                <x-button type="button">{{ __('products.create_promo') }}</x-button>
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.promo_code_field') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.promo_type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.promo_value') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.promo_recurring') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.promo_uses') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.promo_start_date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('products.promo_end_date') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($promos as $promo)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono font-medium text-gray-900">{{ $promo->code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($promo->type === 'percent')
                                    <x-badge color="blue">{{ __('products.promo_type_percent') }}</x-badge>
                                @else
                                    <x-badge color="indigo">{{ __('products.promo_type_fixed') }}</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if ($promo->type === 'percent')
                                    {{ $promo->value / 100 }}%
                                @else
                                    ${{ number_format($promo->value / 100, 2) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($promo->recurring)
                                    <x-badge color="green">{{ __('common.yes') }}</x-badge>
                                @else
                                    <x-badge color="gray">{{ __('common.no') }}</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $promo->uses_count }}
                                /
                                {{ $promo->max_uses ? $promo->max_uses : '&infin;' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($promo->isValid())
                                    <x-badge color="green">{{ __('products.promo_valid') }}</x-badge>
                                @elseif ($promo->max_uses && $promo->uses_count >= $promo->max_uses)
                                    <x-badge color="amber">{{ __('products.promo_exhausted') }}</x-badge>
                                @else
                                    <x-badge color="red">{{ __('products.promo_expired') }}</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $promo->start_date ? $promo->start_date->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $promo->end_date ? $promo->end_date->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                <a href="{{ route('admin.promo-codes.edit', $promo) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">{{ __('common.edit') }}</a>
                                <x-danger-button
                                    type="button"
                                    x-data=""
                                    x-on:click="$dispatch('open-modal', 'delete-promo-{{ $promo->id }}')"
                                    class="!px-3 !py-1 !text-xs"
                                >
                                    {{ __('common.delete') }}
                                </x-danger-button>
                            </td>
                        </tr>

                        @push('modals')
                            <x-delete-modal
                                name="delete-promo-{{ $promo->id }}"
                                :title="__('products.delete_promo')"
                                :message="__('products.delete_promo_confirm')"
                                :action="route('admin.promo-codes.destroy', $promo)"
                            />
                        @endpush
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-400">
                                {{ __('products.no_promo_codes') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($promos->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $promos->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
