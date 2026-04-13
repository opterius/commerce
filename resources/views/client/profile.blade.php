<x-client-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('navigation.my_profile') }}</h2>
    </x-slot>

    <form method="POST" action="{{ route('client.profile.update') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Personal information --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.personal_info') }}</h3>
                <div class="space-y-4">
                    <x-input name="first_name" :label="__('clients.first_name')" :value="old('first_name', $client->first_name)" required />
                    <x-input name="last_name" :label="__('clients.last_name')" :value="old('last_name', $client->last_name)" required />
                    <x-input name="email" type="email" :label="__('clients.email')" :value="old('email', $client->email)" required />
                    <x-input name="phone" :label="__('clients.phone')" :value="old('phone', $client->phone)" />
                    <x-input name="company_name" :label="__('clients.company_name')" :value="old('company_name', $client->company_name)" />
                </div>
            </div>

            {{-- Address --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('common.address') }}</h3>
                <div class="space-y-4">
                    <x-input name="address_1" :label="__('clients.address_1')" :value="old('address_1', $client->address_1)" />
                    <x-input name="address_2" :label="__('clients.address_2')" :value="old('address_2', $client->address_2)" />
                    <div class="grid grid-cols-2 gap-4">
                        <x-input name="city" :label="__('clients.city')" :value="old('city', $client->city)" />
                        <x-input name="state" :label="__('clients.state')" :value="old('state', $client->state)" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <x-input name="postcode" :label="__('clients.postcode')" :value="old('postcode', $client->postcode)" />
                        <x-select name="country_code" :label="__('clients.country')" :options="$countries ?? []" :selected="old('country_code', $client->country_code)" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Billing preferences --}}
        <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('clients.billing_preferences') }}</h3>
            <div class="max-w-xs">
                <label class="form-label">{{ __('clients.preferred_currency') }}</label>
                <select name="currency_code" class="form-input">
                    <option value="">— {{ __('clients.use_default') }} —</option>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency->code }}"
                            {{ old('currency_code', $client->currency_code) === $currency->code ? 'selected' : '' }}>
                            {{ $currency->code }} — {{ $currency->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">{{ __('clients.currency_note') }}</p>
            </div>
        </div>

        {{-- Save --}}
        <div class="mt-6 flex justify-end">
            <x-button>{{ __('common.save_changes') }}</x-button>
        </div>
    </form>
</x-client-layout>
