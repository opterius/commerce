<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('domains.domains') }}</h2>
    </x-slot>

    <div class="space-y-6">

        {{-- Filters --}}
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-4 items-end">
            <div>
                <label class="form-label">{{ __('common.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-input w-52" placeholder="example.com">
            </div>
            <div>
                <label class="form-label">{{ __('common.status') }}</label>
                <select name="status" class="form-input w-40">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">TLD</label>
                <select name="tld" class="form-input w-32">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach ($tlds as $t)
                        <option value="{{ $t }}" {{ request('tld') === $t ? 'selected' : '' }}>.{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('common.client') }}</label>
                <select name="client_id" class="form-input w-48">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach ($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->company_name ?: $c->first_name . ' ' . $c->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">{{ __('common.filter') }}</button>
            @if (request()->hasAny(['search','status','tld','client_id']))
                <a href="{{ route('admin.domains.index') }}" class="btn-secondary">{{ __('common.clear') }}</a>
            @endif
        </form>

        @if ($domains->isEmpty())
            <x-empty-state :message="__('domains.no_domains')" />
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('domains.domain_name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.client') }}</th>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <a href="{{ route('admin.clients.show', $domain->client_id) }}" class="hover:underline">
                                        {{ $domain->client?->company_name ?: $domain->client?->first_name . ' ' . $domain->client?->last_name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge :color="$domain->statusBadgeColor()" :label="$domain->status" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $domain->expiry_date?->format('Y-m-d') ?? '—' }}
                                    @if ($domain->expiry_date && $domain->expiry_date->isPast())
                                        <span class="ml-1 text-red-500 font-medium text-xs">Expired</span>
                                    @elseif ($domain->expiry_date && $domain->expiry_date->diffInDays() <= 30)
                                        <span class="ml-1 text-orange-500 font-medium text-xs">Soon</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($domain->auto_renew)
                                        <span class="text-green-600 text-xs font-medium">On</span>
                                    @else
                                        <span class="text-gray-400 text-xs">Off</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('admin.domains.show', $domain) }}" class="text-indigo-600 hover:underline">{{ __('common.view') }}</a>
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
</x-admin-layout>
