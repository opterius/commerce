@extends('layouts.admin')

@section('title', __('provisioning.servers'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('provisioning.servers') }}</h1>
        <a href="{{ route('admin.servers.create') }}" class="btn-primary">
            {{ __('provisioning.create_server') }}
        </a>
    </div>

    @if ($servers->isEmpty())
        <x-empty-state :message="__('provisioning.no_servers')" />
    @else
        <div class="card overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-th">{{ __('common.name') }}</th>
                        <th class="table-th">{{ __('provisioning.hostname') }}</th>
                        <th class="table-th">{{ __('provisioning.server_group') }}</th>
                        <th class="table-th">{{ __('provisioning.accounts') }}</th>
                        <th class="table-th">{{ __('common.status') }}</th>
                        <th class="table-th">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($servers as $server)
                        <tr>
                            <td class="table-td font-medium">
                                <a href="{{ route('admin.servers.edit', $server) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $server->name }}
                                </a>
                                @if ($server->ip_address)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $server->ip_address }}</p>
                                @endif
                            </td>
                            <td class="table-td text-sm text-gray-600">{{ $server->hostname }}</td>
                            <td class="table-td text-sm">{{ $server->serverGroup?->name ?? '—' }}</td>
                            <td class="table-td text-sm">
                                {{ $server->account_count }}
                                @if ($server->max_accounts > 0)
                                    / {{ $server->max_accounts }}
                                @endif
                            </td>
                            <td class="table-td">
                                <x-badge :color="$server->is_active ? 'green' : 'gray'">
                                    {{ $server->is_active ? __('common.active') : __('common.inactive') }}
                                </x-badge>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center gap-3 text-sm">
                                    <form method="POST" action="{{ route('admin.servers.test', $server) }}">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:underline">{{ __('provisioning.test_connection') }}</button>
                                    </form>
                                    <a href="{{ route('admin.servers.edit', $server) }}" class="text-indigo-600 hover:underline">{{ __('common.edit') }}</a>
                                    <x-delete-modal
                                        :action="route('admin.servers.destroy', $server)"
                                        :label="__('common.delete')"
                                        :confirmMessage="__('common.are_you_sure')"
                                    />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@push('modals')
@endpush
@endsection
