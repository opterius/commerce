@extends('layouts.admin')

@section('title', __('provisioning.create_server'))

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.servers.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('provisioning.create_server') }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.servers.store') }}" class="card p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-2 gap-5">
            <x-input name="name" :label="__('common.name')" :value="old('name')" required />
            <x-input name="hostname" :label="__('provisioning.hostname')" :value="old('hostname')" required />
        </div>

        <div class="grid grid-cols-2 gap-5">
            <x-input name="ip_address" :label="__('provisioning.ip_address')" :value="old('ip_address')" />
            <div>
                <label class="form-label">{{ __('provisioning.server_group') }}</label>
                <select name="server_group_id" class="form-input">
                    <option value="">— {{ __('common.none') }} —</option>
                    @foreach ($serverGroups as $group)
                        <option value="{{ $group->id }}" {{ old('server_group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <x-input name="api_url" :label="__('provisioning.api_url')" :value="old('api_url')" placeholder="https://panel.example.com" required />
        <x-input name="api_token" :label="__('provisioning.api_token')" :value="old('api_token')" type="password" required />

        <div class="grid grid-cols-3 gap-5">
            <x-input name="max_accounts" :label="__('provisioning.max_accounts')" :value="old('max_accounts', 0)" type="number" min="0" />
            <x-input name="ns1" :label="__('provisioning.ns1')" :value="old('ns1')" placeholder="ns1.example.com" />
            <x-input name="ns2" :label="__('provisioning.ns2')" :value="old('ns2')" placeholder="ns2.example.com" />
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded border-gray-300">
            <label for="is_active" class="text-sm text-gray-700">{{ __('common.active') }}</label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">{{ __('common.create') }}</button>
            <a href="{{ route('admin.servers.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
