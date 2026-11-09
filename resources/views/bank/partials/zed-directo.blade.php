{{-- Zed Directo, the Intermediate's second bank from its third month (App\Services\ZedDirecto): another bank
     in town, to compare with - what it pays, what the student has there and the way in. --}}
@php
    $zdPct = fn (string $key) => \App\Services\PriceRise::pct(\App\Services\SavingsLedger::rate($key) * 100, false);
    $zdHave = round((float) $bankAccount->zd_savings_amount + (float) $bankAccount->zd_deposit_amount, 2);
@endphp
<section class="zb-card zb-zd" aria-labelledby="zb-zd" style="margin-top:16px">
    <h2 id="zb-zd">{{ __('zeddirecto.card_title') }}</h2>
    <p class="zb-muted" style="margin:0 0 8px">{{ __('zeddirecto.card_text', ['savings' => $zdPct('zd_savings'), 'deposit' => $zdPct('zd_deposit')]) }}</p>
    @if ($zdHave > 0)
        <p style="margin:0 0 8px"><strong>{{ __('zeddirecto.card_have', ['amount' => zeds($zdHave)]) }}</strong></p>
    @endif
    <div class="zb-actions"><a href="{{ route('zeddirecto.index') }}" class="themeBtn">{{ __('zeddirecto.card_link') }}</a></div>
</section>
