{{-- The shops' "pay with" (App\Services\CreditCardService, proposal step 16: "paga con ella en el City Mall y en las
     actividades de gasto"): with a credit card, the student chooses the debit card (the current account) or the
     credit card, with what is left on it. Without one, nothing shows and the shops pay from the account as always.
     zvPayWith() says what was chosen; zvPaidWith(res) is the message after paying with the card. --}}
@php $payCard = auth()->check() ? \App\Services\CreditCardService::card(auth()->id()) : null; @endphp
@if ($payCard)
    <fieldset class="form-group zpw">
        <legend>{{ __('card.pay_with') }}</legend>
        <label class="zpw-opt"><input type="radio" name="payWith" value="account" checked> <span>{{ __('card.pay_account') }}</span></label>
        <label class="zpw-opt"><input type="radio" name="payWith" value="card"> <span>{{ __('card.pay_card', ['available' => zeds(\App\Services\CreditCardService::available($payCard))]) }}</span></label>
    </fieldset>
    <style>
        .zpw { border: 0; padding: 0; margin: 0 0 12px; }
        .zpw legend { font-weight: 700; font-size: 15px; margin-bottom: 6px; }
        .zpw-opt { display: flex; gap: 8px; align-items: center; padding: 8px 10px; border: 2px solid #D2DDDB; border-radius: 10px; margin-bottom: 6px; cursor: pointer; font-size: 15px; }
        .zpw-opt:has(input:checked) { border-color: #00A47D; background: #F0FBF7; }
    </style>
@endif
<script>
    window.zvPayWith = () => (document.querySelector('input[name="payWith"]:checked') || {}).value || 'account';
    window.zvPaidWith = (res) => res && res.paid_with === 'card'
        ? @json(__('card.paid')).replace(':amount', res.total_text).replace(':available', res.card_available_text)
        : null;
</script>
