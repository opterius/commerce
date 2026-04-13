<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('domains.add_tld') }}</h2>
            <a href="{{ route('admin.tlds.index') }}" class="btn-secondary">← {{ __('common.back') }}</a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.tlds.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">TLD (e.g. com, net, co.uk)</label>
                        <input type="text" name="tld" value="{{ old('tld') }}" class="form-input" placeholder="com" required>
                        @error('tld') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('domains.currency_code') }}</label>
                        <input type="text" name="currency_code" value="{{ old('currency_code', 'USD') }}" class="form-input w-24" maxlength="3" required>
                        @error('currency_code') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-5">
                    <div>
                        <label class="form-label">{{ __('domains.register_price') }} (cents/yr)</label>
                        <input type="number" name="register_price" value="{{ old('register_price', 0) }}" class="form-input" min="0" required>
                        @error('register_price') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('domains.renew_price') }} (cents/yr)</label>
                        <input type="number" name="renew_price" value="{{ old('renew_price', 0) }}" class="form-input" min="0" required>
                        @error('renew_price') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('domains.transfer_price') }} (cents)</label>
                        <input type="number" name="transfer_price" value="{{ old('transfer_price', 0) }}" class="form-input" min="0" required>
                        @error('transfer_price') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-5">
                    <div>
                        <label class="form-label">Min Years</label>
                        <input type="number" name="min_years" value="{{ old('min_years', 1) }}" class="form-input" min="1" max="10" required>
                    </div>
                    <div>
                        <label class="form-label">Max Years</label>
                        <input type="number" name="max_years" value="{{ old('max_years', 10) }}" class="form-input" min="1" max="10" required>
                    </div>
                    <div>
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-input" min="0">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Grace Period (days)</label>
                        <input type="number" name="grace_period_days" value="{{ old('grace_period_days', 0) }}" class="form-input" min="0">
                    </div>
                    <div>
                        <label class="form-label">Redemption Period (days)</label>
                        <input type="number" name="redemption_period_days" value="{{ old('redemption_period_days', 0) }}" class="form-input" min="0">
                    </div>
                </div>

                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded">
                        {{ __('common.active') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="epp_required" value="1" {{ old('epp_required') ? 'checked' : '' }} class="rounded">
                        EPP Required for Transfer
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="whois_privacy_available" value="1" {{ old('whois_privacy_available') ? 'checked' : '' }} class="rounded">
                        WHOIS Privacy Available
                    </label>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">{{ __('common.create') }}</button>
                    <a href="{{ route('admin.tlds.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
