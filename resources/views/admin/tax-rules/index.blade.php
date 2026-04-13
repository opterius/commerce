<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('navigation.tax_rules') }}</h2>
            <a href="{{ route('admin.tax-rules.create') }}">
                <x-button type="button">{{ __('tax_rules.create') }}</x-button>
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tax_rules.country') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tax_rules.state') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tax_rules.rate') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tax_rules.applies_to') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('tax_rules.eu_tax') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.sort_order') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($taxRules as $rule)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $rule->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $rule->country_code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $rule->state_code ?: '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $rule->rate }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge color="blue">{{ __('tax_rules.applies_' . $rule->applies_to) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($rule->is_eu_tax)
                                    <x-badge color="indigo">{{ __('tax_rules.eu') }}</x-badge>
                                @else
                                    <span class="text-sm text-gray-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge :color="$rule->is_active ? 'green' : 'gray'">
                                    {{ $rule->is_active ? __('common.active') : __('common.inactive') }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $rule->sort_order }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                                <a href="{{ route('admin.tax-rules.edit', $rule) }}"
                                   class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                    {{ __('common.edit') }}
                                </a>
                                <button type="button"
                                    x-data=""
                                    x-on:click="$dispatch('open-modal', 'delete-rule-{{ $rule->id }}')"
                                    class="text-red-600 hover:text-red-900 text-sm font-medium">
                                    {{ __('common.delete') }}
                                </button>
                            </td>
                        </tr>

                        @push('modals')
                            <x-delete-modal
                                name="delete-rule-{{ $rule->id }}"
                                :title="__('tax_rules.delete')"
                                :message="$rule->name"
                                :action="route('admin.tax-rules.destroy', $rule)"
                            />
                        @endpush
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-400">
                                {{ __('common.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($taxRules->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $taxRules->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
