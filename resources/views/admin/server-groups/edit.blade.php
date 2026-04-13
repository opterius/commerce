@extends('layouts.admin')

@section('title', __('provisioning.edit_server_group'))

@section('content')
<div class="max-w-xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.server-groups.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('provisioning.edit_server_group') }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.server-groups.update', $serverGroup) }}" class="card p-6 space-y-5">
        @csrf
        @method('PUT')

        <x-input name="name" :label="__('common.name')" :value="old('name', $serverGroup->name)" required />

        <div>
            <label class="form-label">{{ __('common.description') }}</label>
            <textarea name="description" rows="3" class="form-input">{{ old('description', $serverGroup->description) }}</textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $serverGroup->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
            <label for="is_active" class="text-sm text-gray-700">{{ __('common.active') }}</label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
            <a href="{{ route('admin.server-groups.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
