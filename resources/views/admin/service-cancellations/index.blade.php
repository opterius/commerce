<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('cancellations.title') }}</h2>
            <div class="flex gap-2">
                @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $s => $label)
                    <a href="{{ route('admin.service-cancellations.index', ['status' => $s]) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $status === $s ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">

        @if ($requests->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-10 text-center text-sm text-gray-400">
                {{ __('cancellations.no_requests') }}
            </div>
        @else
            @foreach ($requests as $req)
                <div class="bg-white rounded-xl shadow-sm p-6" x-data="{ showApprove: false, showReject: false }">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                @php
                                    $statusColor = match($req->status) {
                                        'pending'  => 'amber',
                                        'approved' => 'green',
                                        'rejected' => 'red',
                                        default    => 'gray',
                                    };
                                @endphp
                                <x-badge :color="$statusColor">{{ ucfirst($req->status) }}</x-badge>
                                <x-badge color="gray">{{ $req->cancel_type === 'immediate' ? __('cancellations.immediate') : __('cancellations.end_of_period') }}</x-badge>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">
                                <a href="{{ route('admin.services.show', $req->service) }}" class="hover:underline">
                                    {{ $req->service?->domain ?: ($req->service?->product?->name ?? 'Service #' . $req->service_id) }}
                                </a>
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ __('common.client') }}:
                                <a href="{{ route('admin.clients.show', $req->client) }}" class="text-indigo-600 hover:underline">
                                    {{ $req->client?->full_name }}
                                </a>
                                &middot; {{ $req->created_at->format('M d, Y') }}
                            </p>
                            <div class="mt-3">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ __('cancellations.reason') }}</p>
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $req->reason }}</p>
                            </div>
                            @if ($req->admin_notes)
                                <div class="mt-3 p-3 rounded-lg bg-gray-50 border border-gray-100">
                                    <p class="text-xs font-medium text-gray-500 mb-0.5">{{ __('cancellations.admin_notes') }}</p>
                                    <p class="text-sm text-gray-700">{{ $req->admin_notes }}</p>
                                </div>
                            @endif
                            @if ($req->processed_by)
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ __('cancellations.processed_by') }} {{ $req->processedBy?->name }}
                                    {{ $req->processed_at?->format('M d, Y') }}
                                </p>
                            @endif
                        </div>

                        @if ($req->status === 'pending')
                            <div class="flex gap-2 flex-shrink-0">
                                <button @click="showApprove = true" class="btn-primary text-sm">
                                    {{ __('cancellations.approve') }}
                                </button>
                                <button @click="showReject = true" class="btn-danger text-sm">
                                    {{ __('cancellations.reject') }}
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Approve modal --}}
                    <div x-show="showApprove" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                         @keydown.escape.window="showApprove = false">
                        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4" @click.stop>
                            <h3 class="text-base font-semibold text-gray-900 mb-1">{{ __('cancellations.approve_confirm_title') }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ __('cancellations.approve_confirm_body') }}</p>
                            <form method="POST" action="{{ route('admin.service-cancellations.approve', $req) }}">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label">{{ __('cancellations.admin_notes') }} ({{ __('common.optional') }})</label>
                                    <textarea name="admin_notes" rows="2" class="form-input" placeholder="{{ __('cancellations.notes_placeholder') }}"></textarea>
                                </div>
                                <div class="flex gap-3 justify-end">
                                    <button type="button" @click="showApprove = false" class="btn-secondary">{{ __('common.cancel') }}</button>
                                    <button type="submit" class="btn-primary">{{ __('cancellations.approve') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Reject modal --}}
                    <div x-show="showReject" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                         @keydown.escape.window="showReject = false">
                        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4" @click.stop>
                            <h3 class="text-base font-semibold text-gray-900 mb-1">{{ __('cancellations.reject_confirm_title') }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ __('cancellations.reject_confirm_body') }}</p>
                            <form method="POST" action="{{ route('admin.service-cancellations.reject', $req) }}">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label">{{ __('cancellations.admin_notes') }}</label>
                                    <textarea name="admin_notes" rows="2" class="form-input" required placeholder="{{ __('cancellations.reject_reason_placeholder') }}"></textarea>
                                </div>
                                <div class="flex gap-3 justify-end">
                                    <button type="button" @click="showReject = false" class="btn-secondary">{{ __('common.cancel') }}</button>
                                    <button type="submit" class="btn-danger">{{ __('cancellations.reject') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            @endforeach

            <div>{{ $requests->links() }}</div>
        @endif

    </div>
</x-admin-layout>
