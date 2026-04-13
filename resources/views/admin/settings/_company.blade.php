<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-6">{{ __('settings.company') }}</h3>

    <form method="POST" action="{{ route('admin.settings.update', 'company') }}">
        @csrf

        <div class="space-y-4">
            <x-input name="company_name" :label="__('settings.company_name')" :value="$settings['company_name'] ?? ''" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input name="company_email" type="email" :label="__('settings.company_email')" :value="$settings['company_email'] ?? ''" />
                <x-input name="company_phone" :label="__('settings.company_phone')" :value="$settings['company_phone'] ?? ''" />
            </div>

            <x-input name="company_website" type="url" :label="__('settings.company_website')" :value="$settings['company_website'] ?? ''" />

            <x-input name="company_address" :label="__('settings.company_address')" :value="$settings['company_address'] ?? ''" />

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-input name="company_city" :label="__('settings.company_city')" :value="$settings['company_city'] ?? ''" />
                <x-input name="company_state" :label="__('settings.company_state')" :value="$settings['company_state'] ?? ''" />
                <x-input name="company_postcode" :label="__('settings.company_postcode')" :value="$settings['company_postcode'] ?? ''" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input name="company_country" :label="__('settings.company_country')" :value="$settings['company_country'] ?? ''" placeholder="US" />
                <x-input name="company_tax_id" :label="__('settings.company_tax_id')" :value="$settings['company_tax_id'] ?? ''" />
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-button>{{ __('common.save_changes') }}</x-button>
        </div>
    </form>
</div>
