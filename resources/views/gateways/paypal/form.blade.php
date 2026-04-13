<input type="hidden" name="paypal_order_id" id="paypal_order_id" value="">

<div id="paypal-button-container" class="min-h-[48px]"></div>
<div id="paypal-error" class="mt-3 text-sm text-red-600 hidden"></div>

@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ strtoupper($currency) }}&intent=capture"></script>
<script>
paypal.Buttons({
    createOrder: function () {
        return '{{ $paypalOrderId }}';
    },

    onApprove: function (data) {
        document.getElementById('paypal_order_id').value = data.orderID;
        document.getElementById('gateway-form').submit();
    },

    onCancel: function () {
        document.getElementById('paypal-error').textContent = '{{ __('invoices.paypal_cancel') }}';
        document.getElementById('paypal-error').classList.remove('hidden');
    },

    onError: function (err) {
        console.error('PayPal error:', err);
        document.getElementById('paypal-error').textContent = '{{ __('invoices.payment_failed') }}';
        document.getElementById('paypal-error').classList.remove('hidden');
    },

    style: {
        layout: 'vertical',
        color:  'blue',
        shape:  'rect',
        label:  'pay',
        height: 44,
    },
}).render('#paypal-button-container');
</script>
@endpush
