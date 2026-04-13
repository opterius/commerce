<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('domains.tld_manager') }}</h2>
            <a href="{{ route('admin.tlds.create') }}" class="btn-primary">{{ __('domains.add_tld') }}</a>
        </div>
    </x-slot>

    <x-flash-messages />

    @if ($tlds->isEmpty())
        <x-empty-state :message="__('domains.no_tlds')" />
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TLD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('domains.register_price') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('domains.renew_price') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('domains.transfer_price') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('domains.whois_privacy') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EPP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.active') }}</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($tlds as $tld)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">.{{ $tld->tld }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tld->currency_code }} {{ $tld->registerPriceFormatted() }}/yr</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tld->currency_code }} {{ $tld->renewPriceFormatted() }}/yr</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tld->currency_code }} {{ $tld->transferPriceFormatted() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($tld->whois_privacy_available)
                                    <span class="text-green-600">✓</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($tld->epp_required)
                                    <span class="text-orange-500">Required</span>
                                @else
                                    <span class="text-gray-400">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($tld->is_active)
                                    <span class="text-green-600 text-xs font-medium">Active</span>
                                @else
                                    <span class="text-gray-400 text-xs">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                <a href="{{ route('admin.tlds.edit', $tld) }}" class="text-indigo-600 hover:underline">{{ __('common.edit') }}</a>
                                <form method="POST" action="{{ route('admin.tlds.destroy', $tld) }}" class="inline" onsubmit="return confirm('Delete .{{ $tld->tld }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">{{ __('common.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin-layout>
