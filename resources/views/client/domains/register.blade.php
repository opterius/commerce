<x-client-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('domains.register_domain') }}</h2>
            <a href="{{ route('client.domains.search') }}" class="btn-secondary">← {{ __('domains.back_to_search') }}</a>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-6">

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-100">
                <div>
                    <p class="text-sm text-gray-500">{{ __('domains.registering') }}</p>
                    <p class="text-xl font-bold text-gray-900">{{ $sld }}.{{ $tld->tld }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('client.domains.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="sld" value="{{ $sld }}">
                <input type="hidden" name="tld" value="{{ $tld->tld }}">

                {{-- Registration period --}}
                <div>
                    <label class="form-label">{{ __('domains.registration_period') }}</label>
                    <select name="billing_cycle" class="form-input w-48" id="billing-cycle" x-data x-on:change="updatePrice()">
                        @for ($y = $tld->min_years; $y <= $tld->max_years; $y++)
                            <option value="{{ $y }}year" data-price="{{ $tld->register_price * $y }}" {{ old('billing_cycle') === "{$y}year" ? 'selected' : ($y === 1 ? 'selected' : '') }}>
                                {{ $y }} {{ $y === 1 ? __('domains.year') : __('domains.years') }}
                                — {{ $tld->currency_code }} {{ number_format($tld->register_price * $y / 100, 2) }}
                            </option>
                        @endfor
                    </select>
                </div>

                @if ($tld->whois_privacy_available)
                    <div>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="whois_privacy" value="1" {{ old('whois_privacy') ? 'checked' : '' }} class="rounded">
                            {{ __('domains.enable_whois_privacy') }}
                        </label>
                        <p class="mt-1 text-xs text-gray-500">{{ __('domains.whois_privacy_help') }}</p>
                    </div>
                @endif

                {{-- Registrant contact --}}
                <div class="pt-4 border-t border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('domains.registrant_contact') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">{{ __('common.first_name') }} *</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $client->first_name) }}" class="form-input" required>
                            @error('first_name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('common.last_name') }} *</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $client->last_name) }}" class="form-input" required>
                            @error('last_name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('common.company') }}</label>
                            <input type="text" name="company" value="{{ old('company', $client->company_name) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">{{ __('common.email') }} *</label>
                            <input type="email" name="email" value="{{ old('email', $client->email) }}" class="form-input" required>
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('common.phone') }} * (E.164)</label>
                            <input type="tel" name="phone" value="{{ old('phone', $client->phone) }}" class="form-input" placeholder="+12025551234" required>
                            @error('phone') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="form-label">{{ __('common.address_1') }} *</label>
                            <input type="text" name="address_1" value="{{ old('address_1', $client->address_1) }}" class="form-input" required>
                            @error('address_1') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="form-label">{{ __('common.address_2') }}</label>
                            <input type="text" name="address_2" value="{{ old('address_2', $client->address_2) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">{{ __('common.city') }} *</label>
                            <input type="text" name="city" value="{{ old('city', $client->city) }}" class="form-input" required>
                            @error('city') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('common.state') }}</label>
                            <input type="text" name="state" value="{{ old('state', $client->state) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">{{ __('common.postcode') }} *</label>
                            <input type="text" name="postcode" value="{{ old('postcode', $client->postcode) }}" class="form-input" required>
                            @error('postcode') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('common.country_code') }} *</label>
                            <input type="text" name="country_code" value="{{ old('country_code', $client->country_code) }}" class="form-input w-24" maxlength="2" placeholder="US" required>
                            @error('country_code') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex gap-3">
                    <button type="submit" class="btn-primary">{{ __('domains.proceed_to_payment') }}</button>
                    <a href="{{ route('client.domains.search') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </div>

    </div>
</x-client-layout>
