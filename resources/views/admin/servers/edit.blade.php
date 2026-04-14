@extends('layouts.admin')

@section('title', __('provisioning.edit_server'))

@section('content')
@php
    $existingCredentials = collect($modules[$server->type]['fields'] ?? [])
        ->mapWithKeys(fn($f) => [
            $f['name'] => ($f['secret'] ?? false) ? '' : ($server->credentials[$f['name']] ?? ''),
        ])->all();
@endphp

<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.servers.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('provisioning.edit_server') }}</h1>
    </div>

    <form
        method="POST"
        action="{{ route('admin.servers.update', $server) }}"
        class="card p-6 space-y-6"
        x-data="serverForm({{ json_encode($modules) }}, '{{ old('type', $server->type) }}', {{ json_encode(old('credentials', $existingCredentials)) }})"
    >
        @csrf
        @method('PUT')

        {{-- Name + Hostname --}}
        <div class="grid grid-cols-2 gap-5">
            <x-input name="name" :label="__('common.name')" :value="old('name', $server->name)" required />
            <x-input name="hostname" :label="__('provisioning.hostname')" :value="old('hostname', $server->hostname)" required />
        </div>

        {{-- IP + Server Group --}}
        <div class="grid grid-cols-2 gap-5">
            <x-input name="ip_address" :label="__('provisioning.ip_address')" :value="old('ip_address', $server->ip_address)" />
            <div>
                <label class="form-label">{{ __('provisioning.server_group') }}</label>
                <select name="server_group_id" class="form-input">
                    <option value="">— {{ __('common.none') }} —</option>
                    @foreach ($serverGroups as $group)
                        <option value="{{ $group->id }}" {{ old('server_group_id', $server->server_group_id) == $group->id ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Provider cards --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">{{ __('provisioning.provider') }}</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <template x-for="mod in allModules" :key="mod.id">
                    <button
                        type="button"
                        @click="type = mod.id"
                        :class="type === mod.id
                            ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500'
                            : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'"
                        class="relative flex items-center justify-between gap-3 rounded-xl border-2 px-4 py-3 text-left transition-all cursor-pointer"
                    >
                        <span class="text-sm font-semibold text-gray-900" x-text="mod.label"></span>

                        <span
                            x-show="type === mod.id"
                            class="flex-shrink-0 flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600"
                        >
                            <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    </button>
                </template>
            </div>

            <input type="hidden" name="type" :value="type">

            {{-- Provider info + credential fields --}}
            <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-5 space-y-4">
                <div>
                    <p class="text-sm font-semibold text-gray-800" x-text="currentModule.label"></p>
                    <p class="mt-1 text-sm text-gray-500 leading-relaxed" x-text="currentModule.description"></p>
                </div>

                <template x-if="currentFields.length > 0">
                    <div class="space-y-4 border-t border-gray-200 pt-4">
                        <template x-for="field in currentFields" :key="field.name">
                            <div>
                                <label class="form-label" x-text="field.label"></label>
                                <input
                                    :type="field.type"
                                    :name="'credentials[' + field.name + ']'"
                                    :placeholder="field.secret ? '{{ __('provisioning.leave_blank_to_keep') }}' : field.placeholder"
                                    :required="field.required && !field.secret"
                                    :value="field.secret ? '' : (credentials[field.name] ?? '')"
                                    class="form-input"
                                >
                                <p x-show="field.secret" class="mt-1 text-xs text-gray-400">{{ __('provisioning.leave_blank_to_keep') }}</p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Nameservers --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">{{ __('provisioning.nameservers') }}</p>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-5">
                    <x-input name="ns1" :label="__('provisioning.ns1')" :value="old('ns1', $server->ns1)" placeholder="ns1.example.com" />
                    <x-input name="ns2" :label="__('provisioning.ns2')" :value="old('ns2', $server->ns2)" placeholder="ns2.example.com" />
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <x-input name="ns3" :label="__('provisioning.ns3')" :value="old('ns3', $server->ns3)" placeholder="ns3.example.com" />
                    <x-input name="ns4" :label="__('provisioning.ns4')" :value="old('ns4', $server->ns4)" placeholder="ns4.example.com" />
                </div>
            </div>
        </div>

        {{-- Max Accounts + Active --}}
        <div class="grid grid-cols-2 gap-5 items-end">
            <x-input name="max_accounts" :label="__('provisioning.max_accounts')" :value="old('max_accounts', $server->max_accounts)" type="number" min="0" />
            <div class="flex items-center gap-2 pb-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $server->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                <label for="is_active" class="text-sm text-gray-700">{{ __('common.active') }}</label>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
            <a href="{{ route('admin.servers.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>

<script>
function serverForm(modules, selectedType, existingCredentials) {
    return {
        type: selectedType,
        allModules: Object.values(modules),
        credentials: existingCredentials,
        get currentModule() {
            return modules[this.type] ?? { label: '', description: '', fields: [] };
        },
        get currentFields() {
            return this.currentModule.fields ?? [];
        },
    };
}
</script>
@endsection
