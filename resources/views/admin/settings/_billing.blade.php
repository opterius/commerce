<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-6">{{ __('settings.invoice_settings') }}</h3>

    <form method="POST" action="{{ route('admin.settings.update', 'billing') }}">
        @csrf

        <div class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input name="invoice_prefix" :label="__('settings.invoice_prefix')"
                        :value="$settings['invoice_prefix'] ?? 'INV-'" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('settings.invoice_prefix_hint') }}</p>
                </div>
                <div>
                    <x-input name="invoice_due_days" type="number" :label="__('settings.invoice_due_days')"
                        :value="$settings['invoice_due_days'] ?? 14" min="1" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('settings.invoice_due_days_hint') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input name="grace_period_days" type="number" :label="__('settings.grace_period_days')"
                        :value="$settings['grace_period_days'] ?? 7" min="0" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('settings.grace_period_days_hint') }}</p>
                </div>
                <div>
                    <x-input name="auto_close_days" type="number" :label="__('settings.auto_close_days')"
                        :value="$settings['auto_close_days'] ?? 30" min="1" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('settings.auto_close_days_hint') }}</p>
                </div>
            </div>

            <div>
                <x-checkbox name="invoice_yearly_reset" value="1"
                    :checked="($settings['invoice_yearly_reset'] ?? false)"
                    :label="__('settings.invoice_yearly_reset')" />
                <p class="mt-1 ml-6 text-xs text-gray-500">{{ __('settings.invoice_yearly_reset_hint') }}</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-button type="submit">{{ __('common.save_changes') }}</x-button>
        </div>
    </form>
</div>
