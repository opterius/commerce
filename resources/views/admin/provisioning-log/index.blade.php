@extends('layouts.admin')

@section('title', __('provisioning.provisioning_log'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('provisioning.provisioning_log') }}</h1>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card p-4 flex flex-wrap gap-4 items-end">
        <div>
            <label class="form-label">{{ __('common.status') }}</label>
            <select name="status" class="form-input w-36">
                <option value="">{{ __('common.all') }}</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>{{ __('provisioning.status_pending') }}</option>
                <option value="success"  {{ request('status') === 'success'  ? 'selected' : '' }}>{{ __('provisioning.status_success') }}</option>
                <option value="failed"   {{ request('status') === 'failed'   ? 'selected' : '' }}>{{ __('provisioning.status_failed') }}</option>
            </select>
        </div>
        <div>
            <label class="form-label">{{ __('provisioning.action') }}</label>
            <select name="action" class="form-input w-40">
                <option value="">{{ __('common.all') }}</option>
                @foreach (['create','suspend','unsuspend','terminate','info'] as $act)
                    <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">{{ __('common.filter') }}</button>
        @if (request()->hasAny(['status','action','service_id']))
            <a href="{{ route('admin.provisioning-log.index') }}" class="btn-secondary">{{ __('common.all') }}</a>
        @endif
    </form>

    @if ($logs->isEmpty())
        <x-empty-state :message="__('provisioning.no_logs')" />
    @else
        <div class="card overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-th">{{ __('common.service') }}</th>
                        <th class="table-th">{{ __('provisioning.action') }}</th>
                        <th class="table-th">{{ __('common.status') }}</th>
                        <th class="table-th">{{ __('provisioning.triggered_by') }}</th>
                        <th class="table-th">{{ __('common.created_at') }}</th>
                        <th class="table-th"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($logs as $log)
                        <tr>
                            <td class="table-td">
                                @if ($log->service)
                                    <a href="{{ route('admin.services.show', $log->service) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                        {{ $log->service->domain ?? "Service #{$log->service_id}" }}
                                    </a>
                                    <p class="text-xs text-gray-400">{{ $log->service->client?->email }}</p>
                                @else
                                    <span class="text-gray-400 text-sm">{{ __('common.deleted') }}</span>
                                @endif
                            </td>
                            <td class="table-td text-sm font-mono">{{ $log->action }}</td>
                            <td class="table-td">
                                @php
                                    $badgeColor = match($log->status) {
                                        'success' => 'green',
                                        'failed'  => 'red',
                                        default   => 'yellow',
                                    };
                                @endphp
                                <x-badge :color="$badgeColor">{{ $log->status }}</x-badge>
                            </td>
                            <td class="table-td text-sm text-gray-600">{{ $log->staff?->name ?? __('provisioning.system') }}</td>
                            <td class="table-td text-sm text-gray-500">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td class="table-td">
                                <a href="{{ route('admin.provisioning-log.show', $log) }}" class="text-indigo-600 hover:underline text-sm">{{ __('common.view') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>{{ $logs->links() }}</div>
    @endif
</div>
@endsection
