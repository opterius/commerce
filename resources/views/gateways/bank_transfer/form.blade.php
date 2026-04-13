<div class="space-y-4">
    <p class="text-sm text-gray-600">{{ __('invoices.bank_transfer_intro') }}</p>

    <div class="bg-gray-50 rounded-lg p-4 space-y-3 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">{{ __('invoices.bank_name') }}</span>
            <span class="font-medium text-gray-900">{{ $bankName }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">{{ __('invoices.account_name') }}</span>
            <span class="font-medium text-gray-900">{{ $accountName }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">{{ __('invoices.account_number') }}</span>
            <span class="font-medium font-mono text-gray-900">{{ $accountNumber }}</span>
        </div>
        @if ($routingNumber)
            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('invoices.routing_number') }}</span>
                <span class="font-medium font-mono text-gray-900">{{ $routingNumber }}</span>
            </div>
        @endif
        @if ($iban)
            <div class="flex justify-between">
                <span class="text-gray-500">IBAN</span>
                <span class="font-medium font-mono text-gray-900">{{ $iban }}</span>
            </div>
        @endif
        <div class="flex justify-between border-t border-gray-200 pt-3">
            <span class="text-gray-500">{{ __('invoices.payment_reference') }}</span>
            <span class="font-semibold text-indigo-600">{{ $invoice->invoice_number }}</span>
        </div>
    </div>

    @if ($instructions)
        <p class="text-xs text-gray-500">{{ $instructions }}</p>
    @endif

    <p class="text-xs text-amber-600">{{ __('invoices.bank_transfer_note') }}</p>

    <x-button type="submit" class="w-full justify-center mt-2">
        {{ __('invoices.bank_transfer_confirm') }}
    </x-button>
</div>
