<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.clients.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('clients.create_client') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.clients.store') }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Left column --}}
            <div class="space-y-6">
                {{-- Personal information --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.personal_info') }}</h3>
                    <div class="space-y-4">
                        <x-input name="first_name" :label="__('clients.first_name')" :value="old('first_name')" required />
                        <x-input name="last_name" :label="__('clients.last_name')" :value="old('last_name')" required />
                        <x-input name="email" type="email" :label="__('clients.email')" :value="old('email')" required />
                        <div>
                            <x-input name="password" type="password" :label="__('clients.password')" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('clients.password_help') }}</p>
                        </div>
                        <x-input name="phone" :label="__('clients.phone')" :value="old('phone')" />
                    </div>
                </div>

                {{-- Company information --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.company_info') }}</h3>
                    <div class="space-y-4">
                        <x-input name="company_name" :label="__('clients.company_name')" :value="old('company_name')" />
                        <x-input name="tax_id" :label="__('clients.tax_id')" :value="old('tax_id')" />
                    </div>
                </div>
            </div>

            {{-- Right column --}}
            <div class="space-y-6">
                {{-- Address --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.address') }}</h3>
                    <div class="space-y-4">
                        <x-input name="address_1" :label="__('clients.address_1')" :value="old('address_1')" />
                        <x-input name="address_2" :label="__('clients.address_2')" :value="old('address_2')" />
                        <x-input name="city" :label="__('clients.city')" :value="old('city')" />
                        <x-input name="state" :label="__('clients.state')" :value="old('state')" />
                        <x-input name="postcode" :label="__('clients.postcode')" :value="old('postcode')" />
                        <x-select name="country_code" :label="__('clients.country')" :options="$countries ?? []" :selected="old('country_code')" />
                    </div>
                </div>

                {{-- Account settings --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.account_settings') }}</h3>
                    <div class="space-y-4">
                        <x-select name="currency_code" :label="__('clients.currency')" :options="$currencies ?? []" :selected="old('currency_code')" />
                        <x-select name="language" :label="__('clients.language')" :options="$languages ?? []" :selected="old('language')" />
                        <x-select name="client_group_id" :label="__('clients.group')" :options="$groupOptions ?? []" :selected="old('client_group_id')" />
                        <x-select name="status" :label="__('clients.status')" :options="[
                            'active'   => __('clients.status_active'),
                            'inactive' => __('clients.status_inactive'),
                            'closed'   => __('clients.status_closed'),
                        ]" :selected="old('status', 'active')" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Tags --}}
        @if (!empty($tags) && count($tags))
            <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('clients.tags') }}</h3>
                <div class="flex flex-wrap gap-4">
                    @foreach ($tags as $tag)
                        <label class="inline-flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="tags[]"
                                value="{{ $tag->id }}"
                                @checked(in_array($tag->id, old('tags', [])))
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <span class="text-sm text-gray-700">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Custom fields --}}
        @if (!empty($customFields) && count($customFields))
            <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('clients.custom_fields') }}</h3>
                <div class="space-y-4">
                    @foreach ($customFields as $field)
                        @switch($field->field_type)
                            @case('text')
                                <x-input
                                    name="custom_fields[{{ $field->id }}]"
                                    :label="$field->label"
                                    :value="old('custom_fields.' . $field->id)"
                                    :required="$field->is_required"
                                />
                                @break
                            @case('textarea')
                                <x-textarea
                                    name="custom_fields[{{ $field->id }}]"
                                    :label="$field->label"
                                    :value="old('custom_fields.' . $field->id)"
                                />
                                @break
                            @case('select')
                                <x-select
                                    name="custom_fields[{{ $field->id }}]"
                                    :label="$field->label"
                                    :options="$field->options ?? []"
                                    :selected="old('custom_fields.' . $field->id)"
                                    :required="$field->is_required"
                                />
                                @break
                            @case('checkbox')
                                <x-checkbox
                                    name="custom_fields[{{ $field->id }}]"
                                    :label="$field->label"
                                    :checked="old('custom_fields.' . $field->id)"
                                />
                                @break
                        @endswitch
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('admin.clients.index') }}">
                <x-secondary-button type="button">{{ __('common.cancel') }}</x-secondary-button>
            </a>
            <x-button>{{ __('clients.create_client') }}</x-button>
        </div>
    </form>
</x-admin-layout>
