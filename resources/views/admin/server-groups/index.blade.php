@extends('layouts.admin')

@section('title', __('provisioning.server_groups'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('provisioning.server_groups') }}</h1>
        <a href="{{ route('admin.server-groups.create') }}" class="btn-primary">
            {{ __('provisioning.create_server_group') }}
        </a>
    </div>

    @if ($groups->isEmpty())
        <x-empty-state :message="__('provisioning.no_server_groups')" />
    @else
        <div class="card overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-th">{{ __('common.name') }}</th>
                        <th class="table-th">{{ __('provisioning.servers') }}</th>
                        <th class="table-th">{{ __('common.status') }}</th>
                        <th class="table-th">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($groups as $group)
                        <tr>
                            <td class="table-td font-medium">
                                <a href="{{ route('admin.server-groups.edit', $group) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $group->name }}
                                </a>
                                @if ($group->description)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $group->description }}</p>
                                @endif
                            </td>
                            <td class="table-td">{{ $group->servers_count }}</td>
                            <td class="table-td">
                                <x-badge :color="$group->is_active ? 'green' : 'gray'">
                                    {{ $group->is_active ? __('common.active') : __('common.inactive') }}
                                </x-badge>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.server-groups.edit', $group) }}" class="btn-secondary py-1 px-3 text-xs">{{ __('common.edit') }}</a>
                                    <x-delete-modal
                                        :action="route('admin.server-groups.destroy', $group)"
                                        label="{{ __('common.delete') }}"
                                        :confirmMessage="__('common.are_you_sure')"
                                        buttonClass="btn-danger py-1 px-3 text-xs"
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
