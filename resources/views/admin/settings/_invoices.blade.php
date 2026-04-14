<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-1">{{ __('settings.invoice_customisation') }}</h3>
    <p class="text-sm text-gray-500 mb-6">{{ __('settings.invoice_customisation_help') }}</p>

    <form method="POST" action="{{ route('admin.settings.update', 'invoices') }}" class="space-y-5">
        @csrf

        {{-- Show logo --}}
        <div class="flex items-start gap-3">
            <input type="hidden" name="invoice_show_logo" value="0">
            <input type="checkbox" name="invoice_show_logo" id="invoice_show_logo" value="1"
                {{ ($settings['invoice_show_logo'] ?? '1') === '1' ? 'checked' : '' }}
                class="rounded border-gray-300 mt-0.5">
            <div>
                <label for="invoice_show_logo" class="text-sm font-medium text-gray-800">{{ __('settings.invoice_show_logo') }}</label>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('settings.invoice_show_logo_help') }}</p>
            </div>
        </div>

        {{-- Accent colour --}}
        <div>
            <label class="form-label" for="invoice_accent_color">{{ __('settings.invoice_accent_color') }}</label>
            <div class="mt-1 flex items-center gap-3">
                <input type="color" name="invoice_accent_color" id="invoice_accent_color"
                    value="{{ $settings['invoice_accent_color'] ?? ($settings['brand_primary_color'] ?? '#4f46e5') }}"
                    class="h-10 w-16 rounded border-gray-300 cursor-pointer">
                <span class="text-sm text-gray-500">{{ $settings['invoice_accent_color'] ?? '(using brand colour)' }}</span>
            </div>
            <p class="mt-1 text-xs text-gray-400">{{ __('settings.invoice_accent_color_help') }}</p>
        </div>

        {{-- Payment terms --}}
        <div>
            <label class="form-label" for="invoice_payment_terms">{{ __('settings.invoice_payment_terms') }}</label>
            <textarea name="invoice_payment_terms" id="invoice_payment_terms" rows="3" class="form-input"
                placeholder="Payment is due within 7 days.">{{ $settings['invoice_payment_terms'] ?? '' }}</textarea>
            <p class="mt-1 text-xs text-gray-400">{{ __('settings.invoice_payment_terms_help') }}</p>
        </div>

        {{-- Footer text --}}
        <div>
            <label class="form-label" for="invoice_footer_text">{{ __('settings.invoice_footer_text') }}</label>
            <textarea name="invoice_footer_text" id="invoice_footer_text" rows="3" class="form-input"
                placeholder="Thank you for your business!">{{ $settings['invoice_footer_text'] ?? '' }}</textarea>
            <p class="mt-1 text-xs text-gray-400">{{ __('settings.invoice_footer_text_help') }}</p>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
        </div>
    </form>
</div>
