<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('staff.staff_members') }}</h2>
            @staffcan('staff.manage')
                <a href="{{ route('admin.staff.create') }}">
                    <x-button type="button">{{ __('staff.add_staff') }}</x-button>
                </a>
            @endstaffcan
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.email') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('staff.role') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('staff.permissions_count') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('staff.last_login') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($staffMembers as $member)
                    @php
                        $effective = $member->permissions ?? \App\Support\StaffPermissions::forRole($member->role);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $member->name }}
                            @if ($member->id === auth('staff')->id())
                                <span class="text-xs text-indigo-500 ml-1">({{ __('common.you') }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $member->email }}</td>
                        <td class="px-6 py-4 text-sm">
                            @php
                                $roleColors = [
                                    'super_admin' => 'purple',
                                    'admin'       => 'indigo',
                                    'support'     => 'blue',
                                    'billing'     => 'amber',
                                ];
                            @endphp
                            <x-badge color="{{ $roleColors[$member->role] ?? 'gray' }}">
                                {{ __('staff.role_' . $member->role) }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if ($member->role === 'super_admin')
                                <span class="text-purple-600 font-medium">{{ __('staff.all_permissions') }}</span>
                            @else
                                {{ count($effective) }} / {{ count(\App\Support\StaffPermissions::all()) }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ($member->is_active)
                                <x-badge color="green">{{ __('common.active') }}</x-badge>
                            @else
                                <x-badge color="gray">{{ __('common.inactive') }}</x-badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $member->last_login_at ? $member->last_login_at->diffForHumans() : __('common.never') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @staffcan('staff.manage')
                                <a href="{{ route('admin.staff.edit', $member) }}"
                                   class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ __('common.edit') }}
                                </a>

                                @if ($member->id !== auth('staff')->id())
                                    <form method="POST" action="{{ route('admin.staff.destroy', $member) }}"
                                          class="inline ml-4"
                                          x-data
                                          @submit.prevent="$dispatch('open-modal', { name: 'confirm-delete-{{ $member->id }}' })">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>

                                    <x-confirm-modal
                                        name="confirm-delete-{{ $member->id }}"
                                        :title="__('staff.delete_confirm_title')"
                                        :body="__('staff.delete_confirm_body', ['name' => $member->name])"
                                        :confirm-label="__('common.delete')"
                                        confirm-color="red"
                                        :form-action="route('admin.staff.destroy', $member)"
                                        form-method="DELETE"
                                    />
                                @endif
                            @endstaffcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
