{{-- Authorize.net Accept.js inline card form --}}
{{-- Variables: $authorizeApiLoginId, $authorizePublicKey, $authorizeSandbox --}}

<input type="hidden" name="authorize_data_descriptor" id="authorize_data_descriptor">
<input type="hidden" name="authorize_data_value" id="authorize_data_value">

<div class="space-y-4">
    <div>
        <label class="form-label">{{ __('invoices.card_number') }}</label>
        <input type="text" id="authorize_card_number"
               class="form-input"
               placeholder="•••• •••• •••• ••••"
               maxlength="19"
               autocomplete="cc-number">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="form-label">{{ __('invoices.expiry') }}</label>
            <input type="text" id="authorize_expiry"
                   class="form-input"
                   placeholder="MM / YY"
                   maxlength="7"
                   autocomplete="cc-exp">
        </div>
        <div>
            <label class="form-label">{{ __('invoices.cvc') }}</label>
            <input type="text" id="authorize_cvv"
                   class="form-input"
                   placeholder="CVC"
                   maxlength="4"
                   autocomplete="cc-csc">
        </div>
    </div>

    <div id="authorize-error" class="text-sm text-red-600 hidden"></div>
</div>

@push('scripts')
<script
    src="{{ $authorizeSandbox
        ? 'https://jstest.authorize.net/v1/Accept.js'
        : 'https://js.authorize.net/v1/Accept.js' }}"
    charset="utf-8">
</script>
<script>
(function () {
    var form = document.getElementById('gateway-form');

    // Format card number with spaces
    document.getElementById('authorize_card_number').addEventListener('input', function (e) {
        var v = e.target.value.replace(/\D/g, '').substring(0, 16);
        e.target.value = v.replace(/(.{4})/g, '$1 ').trim();
    });

    // Format expiry MM / YY
    document.getElementById('authorize_expiry').addEventListener('input', function (e) {
        var v = e.target.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 3) v = v.substring(0, 2) + ' / ' + v.substring(2);
        e.target.value = v;
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var errorEl = document.getElementById('authorize-error');
        errorEl.classList.add('hidden');
        errorEl.textContent = '';

        var expiry   = document.getElementById('authorize_expiry').value.replace(/\s/g, '');
        var expParts = expiry.split('/');

        var secureData = {
            authData: {
                clientKey: '{{ $authorizePublicKey }}',
                apiLoginID: '{{ $authorizeApiLoginId }}',
            },
            cardData: {
                cardNumber:    document.getElementById('authorize_card_number').value.replace(/\s/g, ''),
                month:         expParts[0] ? expParts[0].trim() : '',
                year:          expParts[1] ? '20' + expParts[1].trim() : '',
                cardCode:      document.getElementById('authorize_cvv').value,
            },
        };

        Accept.dispatchData(secureData, function (response) {
            if (response.messages.resultCode === 'Error') {
                var msg = response.messages.message.map(function (m) { return m.text; }).join(' ');
                errorEl.textContent = msg;
                errorEl.classList.remove('hidden');
                return;
            }

            document.getElementById('authorize_data_descriptor').value =
                response.opaqueData.dataDescriptor;
            document.getElementById('authorize_data_value').value =
                response.opaqueData.dataValue;

            // Remove raw card inputs so they are never submitted
            document.getElementById('authorize_card_number').remove();
            document.getElementById('authorize_expiry').remove();
            document.getElementById('authorize_cvv').remove();

            form.submit();
        });
    });
}());
</script>
@endpush
