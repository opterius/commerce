<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('domains.edit_tld') }}: .{{ $tld->tld }}</h2>
            <a href="{{ route('admin.tlds.index') }}" class="btn-secondary">← {{ __('common.back') }}</a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.tlds.update', $tld) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">TLD</label>
                        <input type="text" value=".{{ $tld->tld }}" class="form-input bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="form-label">{{ __('domains.currency_code') }}</label>
                        <input type="text" name="currency_code" value="{{ old('currency_code', $tld->currency_code) }}" class="form-input w-24" maxlength="3" required>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-5">
                    <div>
                        <label class="form-label">{{ __('domains.register_price') }} (cents/yr)</label>
                        <input type="number" name="register_price" value="{{ old('register_price', $tld->register_price) }}" class="form-input" min="0" required>
                    </div>
                    <div>
                        <label class="form-label">{{ __('domains.renew_price') }} (cents/yr)</label>
                        <input type="number" name="renew_price" value="{{ old('renew_price', $tld->renew_price) }}" class="form-input" min="0" required>
                    </div>
                    <div>
                        <label class="form-label">{{ __('domains.transfer_price') }} (cents)</label>
                        <input type="number" name="transfer_price" value="{{ old('transfer_price', $tld->transfer_price) }}" class="form-input" min="0" required>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-5">
                    <div>
                        <label class="form-label">Min Years</label>
                        <input type="number" name="min_years" value="{{ old('min_years', $tld->min_years) }}" class="form-input" min="1" max="10" required>
                    </div>
                    <div>
                        <label class="form-label">Max Years</label>
                        <input type="number" name="max_years" value="{{ old('max_years', $tld->max_years) }}" class="form-input" min="1" max="10" required>
                    </div>
                    <div>
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $tld->sort_order) }}" class="form-input" min="0">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Grace Period (days)</label>
                        <input type="number" name="grace_period_days" value="{{ old('grace_period_days', $tld->grace_period_days) }}" class="form-input" min="0">
                    </div>
                    <div>
                        <label class="form-label">Redemption Period (days)</label>
                        <input type="number" name="redemption_period_days" value="{{ old('redemption_period_days', $tld->redemption_period_days) }}" class="form-input" min="0">
                    </div>
                </div>

                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tld->is_active) ? 'checked' : '' }} class="rounded">
                        {{ __('common.active') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="epp_required" value="1" {{ old('epp_required', $tld->epp_required) ? 'checked' : '' }} class="rounded">
                        EPP Required for Transfer
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="whois_privacy_available" value="1" {{ old('whois_privacy_available', $tld->whois_privacy_available) ? 'checked' : '' }} class="rounded">
                        WHOIS Privacy Available
                    </label>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">{{ __('common.save') }}</button>
                    <a href="{{ route('admin.tlds.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
