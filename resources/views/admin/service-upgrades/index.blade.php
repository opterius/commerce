<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('navigation.service_upgrades') }}</h2>
        </div>
    </x-slot>

    {{-- Status filter tabs --}}
    <div class="mb-6 flex gap-2">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.service-upgrades.index', ['status' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition
                   {{ $status === $key ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
            <p class="text-sm text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan Change</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Proration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($upgradeRequests as $req)
                        <tr x-data="{ showReject: false }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $req->client?->full_name ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $req->client?->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $req->service?->domain ?: ('Service #' . $req->service_id) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <span class="font-medium">{{ $req->fromProduct?->name ?? '—' }}</span>
                                    <span class="text-gray-400 mx-1">({{ ucfirst(str_replace('_', ' ', $req->from_billing_cycle)) }})</span>
                                </div>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <span class="text-sm font-medium text-indigo-700">{{ $req->toProduct?->name ?? '—' }}</span>
                                    <span class="text-xs text-gray-400">({{ ucfirst(str_replace('_', ' ', $req->to_billing_cycle)) }})</span>
                                    @if ($req->isUpgrade())
                                        <x-badge color="green">Upgrade</x-badge>
                                    @else
                                        <x-badge color="amber">Downgrade</x-badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @php
                                    $charge = $req->proration_charge;
                                    $currency = $req->service?->currency_code ?? 'USD';
                                @endphp
                                @if ($charge > 0)
                                    <span class="text-sm font-medium text-red-600">
                                        +{{ $currency }} {{ number_format($charge / 100, 2) }}
                                    </span>
                                @elseif ($charge < 0)
                                    <span class="text-sm font-medium text-green-600">
                                        {{ $currency }} {{ number_format($charge / 100, 2) }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">No charge</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $req->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $badgeColor = match($req->status) {
                                        'pending'  => 'amber',
                                        'approved' => 'green',
                                        'rejected' => 'red',
                                        default    => 'gray',
                                    };
                                @endphp
                                <x-badge :color="$badgeColor">{{ ucfirst($req->status) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @if ($req->status === 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.service-upgrades.approve', $req) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-green-600 text-white hover:bg-green-700 transition">
                                                Approve
                                            </button>
                                        </form>
                                        <button @click="showReject = !showReject"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-red-600 text-white hover:bg-red-700 transition">
                                            Reject
                                        </button>
                                    </div>
                                    {{-- Inline reject form --}}
                                    <div x-show="showReject" x-cloak class="mt-2">
                                        <form method="POST" action="{{ route('admin.service-upgrades.reject', $req) }}" class="flex items-end gap-2">
                                            @csrf
                                            <div class="flex-1">
                                                <textarea name="notes" rows="2" placeholder="Reason for rejection (optional)"
                                                    class="w-full text-xs rounded border border-gray-300 px-2 py-1 focus:outline-none focus:ring-1 focus:ring-red-400"></textarea>
                                            </div>
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-md text-xs font-medium bg-red-600 text-white hover:bg-red-700 transition">
                                                Confirm Reject
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($req->status === 'approved' && $req->invoice)
                                    <a href="{{ route('admin.invoices.show', $req->invoice) }}"
                                        class="text-xs text-indigo-600 hover:underline">View Invoice</a>
                                @else
                                    @if ($req->processedBy)
                                        <span class="text-xs text-gray-400">by {{ $req->processedBy->name }}</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                No upgrade requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($upgradeRequests->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $upgradeRequests->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
