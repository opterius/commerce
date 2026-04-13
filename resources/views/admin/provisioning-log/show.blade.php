@extends('layouts.admin')

@section('title', __('provisioning.log_entry'))

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.provisioning-log.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('provisioning.log_entry') }} #{{ $provisioningLog->id }}</h1>
    </div>

    <div class="card p-6 space-y-4">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div>
                <dt class="text-gray-500">{{ __('common.service') }}</dt>
                <dd class="font-medium">
                    @if ($provisioningLog->service)
                        <a href="{{ route('admin.services.show', $provisioningLog->service) }}" class="text-indigo-600 hover:underline">
                            {{ $provisioningLog->service->domain ?? "Service #{$provisioningLog->service_id}" }}
                        </a>
                    @else
                        <span class="text-gray-400">{{ __('common.deleted') }}</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('provisioning.action') }}</dt>
                <dd class="font-mono">{{ $provisioningLog->action }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('common.status') }}</dt>
                <dd>
                    @php
                        $badgeColor = match($provisioningLog->status) {
                            'success' => 'green',
                            'failed'  => 'red',
                            default   => 'yellow',
                        };
                    @endphp
                    <x-badge :color="$badgeColor">{{ $provisioningLog->status }}</x-badge>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('provisioning.triggered_by') }}</dt>
                <dd>{{ $provisioningLog->staff?->name ?? __('provisioning.system') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('common.created_at') }}</dt>
                <dd>{{ $provisioningLog->created_at->format('Y-m-d H:i:s') }}</dd>
            </div>
        </dl>

        @if ($provisioningLog->error)
            <div class="bg-red-50 border border-red-200 rounded p-3 text-sm text-red-700">
                <p class="font-medium mb-1">{{ __('provisioning.error') }}</p>
                {{ $provisioningLog->error }}
            </div>
        @endif
    </div>

    @if ($provisioningLog->request)
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('provisioning.request') }}</h3>
            <pre class="text-xs bg-gray-50 rounded p-3 overflow-x-auto">{{ json_encode($provisioningLog->request, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif

    @if ($provisioningLog->response)
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('provisioning.response') }}</h3>
            <pre class="text-xs bg-gray-50 rounded p-3 overflow-x-auto">{{ json_encode($provisioningLog->response, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif
</div>
@endsection
