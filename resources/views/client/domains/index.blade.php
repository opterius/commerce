<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('domains.my_domains') }}</h2>
            <a href="{{ route('client.domains.search') }}" class="btn-primary">{{ __('domains.register_new') }}</a>
        </div>
    </x-slot>

    <div class="space-y-6">

        @if ($domains->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <p class="text-gray-400 mb-4">{{ __('domains.no_domains_yet') }}</p>
                <a href="{{ route('client.domains.search') }}" class="btn-primary">{{ __('domains.search_and_register') }}</a>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('domains.domain_name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('domains.expiry') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('domains.auto_renew') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($domains as $domain)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    {{ $domain->domain_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge :color="$domain->statusBadgeColor()" :label="$domain->status" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $domain->expiry_date?->format('Y-m-d') ?? '—' }}
                                    @if ($domain->expiry_date && $domain->expiry_date->isPast())
                                        <span class="ml-1 text-red-500 text-xs font-medium">{{ __('domains.expired') }}</span>
                                    @elseif ($domain->expiry_date && $domain->expiry_date->diffInDays() <= 30)
                                        <span class="ml-1 text-orange-500 text-xs font-medium">{{ __('domains.expires_soon') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($domain->auto_renew)
                                        <span class="text-green-600 font-medium">{{ __('common.on') }}</span>
                                    @else
                                        <span class="text-gray-400">{{ __('common.off') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('client.domains.show', $domain) }}" class="text-indigo-600 hover:underline">{{ __('common.manage') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($domains->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $domains->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-client-layout>
