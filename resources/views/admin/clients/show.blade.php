<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.clients.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <h2 class="text-lg font-semibold text-gray-800">{{ $client->first_name }} {{ $client->last_name }}</h2>
                @php
                    $statusColor = match($client->status) {
                        'active'   => 'green',
                        'inactive' => 'amber',
                        'closed'   => 'red',
                        default    => 'gray',
                    };
                @endphp
                <x-badge :color="$statusColor">{{ __('clients.status_' . $client->status) }}</x-badge>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.clients.edit', $client) }}">
                    <x-secondary-button type="button">{{ __('common.edit') }}</x-secondary-button>
                </a>
                <form action="{{ route('admin.login-as-client', $client) }}" method="POST" class="inline">
                    @csrf
                    <x-secondary-button type="submit">{{ __('clients.login_as_client') }}</x-secondary-button>
                </form>
                <x-danger-button
                    type="button"
                    x-data=""
                    x-on:click="$dispatch('open-modal', 'delete-client')"
                >
                    {{ __('common.delete') }}
                </x-danger-button>
            </div>
        </div>
    </x-slot>

    {{-- Tabs --}}
    <div x-data="{ tab: '{{ request('tab', 'overview') }}' }">
        {{-- Tab navigation --}}
        <div class="bg-white rounded-xl shadow-sm mb-6">
            <nav class="flex border-b border-gray-200 px-6" aria-label="Tabs">
                @foreach (['overview', 'contacts', 'notes', 'activity'] as $t)
                    <button
                        @click="tab = '{{ $t }}'"
                        :class="tab === '{{ $t }}' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm transition-colors"
                    >
                        {{ __('clients.tab_' . $t) }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- ========== Overview tab ========== --}}
        <div x-show="tab === 'overview'" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Contact info --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('clients.contact_info') }}</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('common.email') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $client->email }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('common.phone') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $client->phone ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('clients.company_name') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $client->company_name ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('clients.tax_id') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $client->tax_id ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('common.address') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 text-right">
                                @if ($client->address_1)
                                    {{ $client->address_1 }}<br>
                                    @if ($client->address_2){{ $client->address_2 }}<br>@endif
                                    {{ $client->city }}, {{ $client->state }} {{ $client->postcode }}<br>
                                    {{ $client->country_code }}
                                @else
                                    &mdash;
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Account info --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('clients.account_info') }}</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('clients.group') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                @if ($client->group)
                                    <x-badge :color="$client->group->color ?? 'gray'">{{ $client->group->name }}</x-badge>
                                @else
                                    {{ __('clients.no_group') }}
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('clients.currency') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $client->currency_code }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('clients.language') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $client->language ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('common.created_at') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $client->created_at->format('M d, Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('clients.last_login') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $client->last_login_at ? $client->last_login_at->diffForHumans() : __('clients.never') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Tags --}}
            <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('clients.tags') }}</h3>
                @if ($client->tags && $client->tags->count())
                    <div class="flex flex-wrap gap-2">
                        @foreach ($client->tags as $tag)
                            <x-badge :color="$tag->color ?? 'indigo'">{{ $tag->name }}</x-badge>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400">{{ __('clients.no_tags') }}</p>
                @endif
            </div>
        </div>

        {{-- ========== Contacts tab ========== --}}
        <div x-show="tab === 'contacts'" x-cloak>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">{{ __('clients.contacts') }}</h3>
                    <x-button
                        type="button"
                        x-data=""
                        x-on:click="$dispatch('open-modal', 'add-contact')"
                    >
                        {{ __('clients.add_contact') }}
                    </x-button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.email') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('clients.contact_role') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.active') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($client->contacts ?? [] as $contact)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $contact->first_name }} {{ $contact->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-badge color="blue">{{ __('clients.role_' . $contact->role) }}</x-badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-badge :color="$contact->is_active ? 'green' : 'gray'">
                                            {{ $contact->is_active ? __('common.active') : __('common.inactive') }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                        <button
                                            type="button"
                                            x-data=""
                                            x-on:click="$dispatch('open-modal', 'edit-contact-{{ $contact->id }}')"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ __('common.edit') }}
                                        </button>
                                        <button
                                            type="button"
                                            x-data=""
                                            x-on:click="$dispatch('open-modal', 'delete-contact-{{ $contact->id }}')"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            {{ __('common.delete') }}
                                        </button>
                                    </td>
                                </tr>

                                {{-- Edit contact modal --}}
                                @push('modals')
                                    <x-modal name="edit-contact-{{ $contact->id }}" maxWidth="md">
                                        <form method="POST" action="{{ route('admin.clients.contacts.update', [$client, $contact]) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="p-6">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('clients.edit_contact') }}</h3>
                                                <div class="space-y-4">
                                                    <x-input name="first_name" :label="__('clients.first_name')" :value="$contact->first_name" required />
                                                    <x-input name="last_name" :label="__('clients.last_name')" :value="$contact->last_name" required />
                                                    <x-input name="email" type="email" :label="__('clients.email')" :value="$contact->email" required />
                                                    <div>
                                                        <x-input name="password" type="password" :label="__('clients.password')" />
                                                        <p class="mt-1 text-xs text-gray-500">{{ __('clients.password_keep') }}</p>
                                                    </div>
                                                    <x-input name="phone" :label="__('clients.phone')" :value="$contact->phone" />
                                                    <x-select name="role" :label="__('clients.contact_role')" :options="[
                                                        'billing'   => __('clients.role_billing'),
                                                        'technical' => __('clients.role_technical'),
                                                        'admin'     => __('clients.role_admin'),
                                                    ]" :selected="$contact->role" />
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-xl">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'edit-contact-{{ $contact->id }}')">
                                                    {{ __('common.cancel') }}
                                                </x-secondary-button>
                                                <x-button>{{ __('common.save_changes') }}</x-button>
                                            </div>
                                        </form>
                                    </x-modal>
                                @endpush

                                {{-- Delete contact modal --}}
                                @push('modals')
                                    <x-delete-modal
                                        name="delete-contact-{{ $contact->id }}"
                                        :title="__('common.are_you_sure')"
                                        :message="__('common.this_action_cannot_be_undone')"
                                        :action="route('admin.clients.contacts.destroy', [$client, $contact])"
                                    />
                                @endpush
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">
                                        {{ __('common.no_results') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Add contact modal --}}
            @push('modals')
                <x-modal name="add-contact" maxWidth="md">
                    <form method="POST" action="{{ route('admin.clients.contacts.store', $client) }}">
                        @csrf
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('clients.add_contact') }}</h3>
                            <div class="space-y-4">
                                <x-input name="first_name" :label="__('clients.first_name')" required />
                                <x-input name="last_name" :label="__('clients.last_name')" required />
                                <x-input name="email" type="email" :label="__('clients.email')" required />
                                <div>
                                    <x-input name="password" type="password" :label="__('clients.password')" />
                                    <p class="mt-1 text-xs text-gray-500">{{ __('common.optional') }}</p>
                                </div>
                                <x-input name="phone" :label="__('clients.phone')" />
                                <x-select name="role" :label="__('clients.contact_role')" :options="[
                                    'billing'   => __('clients.role_billing'),
                                    'technical' => __('clients.role_technical'),
                                    'admin'     => __('clients.role_admin'),
                                ]" :selected="'billing'" />
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-xl">
                            <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'add-contact')">
                                {{ __('common.cancel') }}
                            </x-secondary-button>
                            <x-button>{{ __('clients.add_contact') }}</x-button>
                        </div>
                    </form>
                </x-modal>
            @endpush
        </div>

        {{-- ========== Notes tab ========== --}}
        <div x-show="tab === 'notes'" x-cloak>
            {{-- Add note form --}}
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('clients.add_note') }}</h3>
                <form method="POST" action="{{ route('admin.clients.notes.store', $client) }}">
                    @csrf
                    <div class="space-y-4">
                        <x-textarea name="body" :label="__('clients.notes')" rows="3" required />
                        <div class="flex items-center justify-between">
                            <x-checkbox name="is_sticky" value="1" :label="__('clients.sticky_note')" />
                            <x-button>{{ __('clients.add_note') }}</x-button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Notes list --}}
            <div class="space-y-4">
                @forelse ($client->notes ?? [] as $note)
                    <div class="bg-white rounded-xl shadow-sm p-6 {{ $note->is_sticky ? 'border-l-4 border-amber-400' : '' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                @if ($note->is_sticky)
                                    <div class="flex items-center gap-1 mb-2">
                                        <svg class="w-4 h-4 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                                        </svg>
                                        <span class="text-xs font-medium text-amber-600">{{ __('clients.sticky_note') }}</span>
                                    </div>
                                @endif
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $note->body }}</p>
                                <div class="mt-3 flex items-center gap-3 text-xs text-gray-400">
                                    @if ($note->staff)
                                        <span>{{ $note->staff->name }}</span>
                                        <span>&middot;</span>
                                    @endif
                                    <span>{{ $note->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.clients.notes.destroy', [$client, $note]) }}" class="ml-4">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-gray-400 hover:text-red-600 transition-colors"
                                    title="{{ __('common.delete') }}"
                                >
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                        <p class="text-sm text-gray-400">{{ __('clients.no_notes') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ========== Activity tab ========== --}}
        <div x-show="tab === 'activity'" x-cloak>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">{{ __('clients.activity') }}</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($recentActivity ?? [] as $activity)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-900">
                                    {{ $activity->action }}
                                    @if ($activity->entity_name)
                                        &mdash; <span class="font-medium">{{ $activity->entity_name }}</span>
                                    @endif
                                </p>
                                @if ($activity->staff)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $activity->staff->name }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400 whitespace-nowrap">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-400">
                            {{ __('clients.no_activity') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Delete client modal --}}
    @push('modals')
        <x-delete-modal
            name="delete-client"
            :title="__('clients.delete_client')"
            :message="__('clients.delete_client_confirm')"
            :action="route('admin.clients.destroy', $client)"
        />
    @endpush
</x-admin-layout>
