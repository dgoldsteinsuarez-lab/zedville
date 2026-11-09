@extends('layouts.profile')

@section('title', __('bank_ui.tab_details'))

{{-- Card & details (bank review, Daniel 2026-10-29): what a friend needs to send
     the student money - "they should do it like in real life, adding the bank
     account number and so on, so, for that, they will need that information" -
     with a copy button each, the card, and the PIN. Freeze card, Add to wallet
     and paper statements are gone: they only said the service wasn't there. --}}
@section('content')
@php
    $iban = trim(chunk_split(strtoupper((string) $bankAccount->card_iban), 4, ' '));
    $holder = $bankAccount->student_name;
    $card = (string) $bankAccount->card_number;
    $details = [
        ['label' => __('bank_ui.d_holder'), 'value' => $holder, 'tip' => null],
        ['label' => __('bank_ui.account_number'), 'value' => $bankAccount->primary_savings_account_number, 'tip' => __('bank_ui.d_number_tip')],
        ['label' => 'IBAN', 'value' => $iban, 'tip' => __('bank_ui.d_iban_tip')],
        ['label' => 'BIC', 'value' => $bankAccount->card_swift, 'tip' => __('bank_ui.d_bic_tip')],
        ['label' => __('bank_ui.d_bank'), 'value' => $bankAccount->bank_name ?: 'Universal Bank', 'tip' => null],
    ];
    $all = collect($details)->map(fn ($d) => $d['label'] . ': ' . $d['value'])->implode("\n");
@endphp
<div class="zb-page" x-data="{ copied: null, showCard: false, showPin: false, changePin: {{ $errors->has('new_pin') || $errors->has('confirm_pin') || $errors->has('security_answer') ? 'true' : 'false' }},
    copy(text, id) { const done = () => { this.copied = id; setTimeout(() => this.copied = null, 1600); };
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(text).then(done); }
        else { const t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove(); done(); } } }">
    @include('bank.partials.nav')

    <div class="zb-grid zb-grid--2" style="align-items:start">
        <section class="zb-card" aria-labelledby="zb-receive">
            <h2 id="zb-receive">{{ __('bank_ui.d_receive_title') }}</h2>
            <p class="zb-muted" style="margin:-6px 0 12px">{{ __('bank_ui.d_receive_hint') }}</p>
            @foreach ($details as $i => $detail)
                <div class="zb-detail">
                    <dl style="margin:0;min-width:0">
                        <dt>{{ $detail['label'] }}</dt>
                        <dd>{{ $detail['value'] }}</dd>
                        @if ($detail['tip'])
                            <dt style="margin-top:2px">{{ $detail['tip'] }}</dt>
                        @endif
                    </dl>
                    <button type="button" class="zb-copy" :class="copied === {{ $i }} && 'is-done'" @click="copy(@js($detail['value']), {{ $i }})"
                            x-text="copied === {{ $i }} ? @js(__('bank_ui.copied')) : @js(__('bank_ui.copy'))">{{ __('bank_ui.copy') }}</button>
                </div>
            @endforeach
            <button type="button" class="themeBtn" style="margin-top:6px" @click="copy(@js($all), 'all')"
                    x-text="copied === 'all' ? @js(__('bank_ui.copied')) : @js(__('bank_ui.copy_all'))">{{ __('bank_ui.copy_all') }}</button>

            @if ((int) $bankAccount->is_open_emergency_account === 1 || (int) $bankAccount->is_open_money_market_account === 1)
                <h2 style="margin-top:20px">{{ __('bank_ui.d_savings_numbers') }}</h2>
                @foreach (['emergency' => ['is_open_emergency_account', 'emergency_fund_account_number'], 'money_market' => ['is_open_money_market_account', 'money_market_account_number']] as $key => [$open, $number])
                    @if ((int) $bankAccount->{$open} === 1)
                        <div class="zb-detail">
                            <dl style="margin:0"><dt>{{ __('bank_ui.' . $key) }}</dt><dd>{{ $bankAccount->{$number} }}</dd></dl>
                        </div>
                    @endif
                @endforeach
            @endif
        </section>

        <section class="zb-card" aria-labelledby="zb-card-title">
            <h2 id="zb-card-title">{{ __('bank_ui.d_card_title') }}</h2>
            <div class="zb-debit">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span class="zb-debit-bank">Universal Bank</span>
                    <span style="font:700 13px/1 'Manrope',sans-serif;padding:4px 8px;border-radius:6px;background:rgba(255,255,255,.18)">DEBIT</span>
                </div>
                <div class="zb-debit-number" x-text="showCard ? @js(trim(chunk_split($card, 4, ' '))) : @js('•••• •••• •••• ' . substr($card, -4))">•••• •••• •••• {{ substr($card, -4) }}</div>
                <div class="zb-debit-meta">
                    <div>{{ __('bank_ui.d_valid') }}<b>{{ \Carbon\Carbon::parse($bankAccount->card_valid)->format('m/y') }}</b></div>
                    <div>CVV<b x-text="showCard ? @js((string) $bankAccount->card_cvv) : '•••'">•••</b></div>
                    <div style="min-width:0">{{ __('bank_ui.d_holder') }}<b style="overflow-wrap:anywhere">{{ $holder }}</b></div>
                </div>
            </div>
            <button type="button" class="whiteBtn" style="margin-top:12px" @click="showCard = !showCard"
                    x-text="showCard ? @js(__('bank_ui.d_hide_pin')) : @js(__('bank_ui.show'))">{{ __('bank_ui.show') }}</button>

            <h2 style="margin-top:20px">{{ __('bank_ui.d_pin_title') }}</h2>
            <p class="zb-warn" style="margin:0 0 10px">{{ __('bank_ui.d_pin_secret') }}</p>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                <button type="button" class="whiteBtn" @click="showPin = !showPin" x-text="showPin ? @js(__('bank_ui.d_hide_pin')) : @js(__('bank_ui.d_see_pin'))">{{ __('bank_ui.d_see_pin') }}</button>
                <span class="zb-num" style="font-size:20px;letter-spacing:.3em" x-show="showPin" x-cloak>{{ $bankAccount->card_pin }}</span>
                <button type="button" class="whiteBtn" @click="changePin = !changePin">{{ __('bank_ui.d_change_pin') }}</button>
            </div>
            <form method="POST" action="{{ route('bank.updatePin') }}" x-show="changePin" x-cloak style="margin-top:14px">
                @csrf
                <input type="hidden" name="bank_account_id" value="{{ $bankAccount->id }}">
                <div class="zb-field">
                    <label for="zb-question">{{ __('bank.security_question') }}: {{ __('bank.what_is_10_11') }}</label>
                    <input id="zb-question" name="security_answer" type="number" class="form-control" required style="max-width:160px">
                </div>
                <div class="zb-row2">
                    <div class="zb-field">
                        <label for="zb-new-pin">{{ __('bank_ui.d_new_pin') }}</label>
                        <input id="zb-new-pin" name="new_pin" type="password" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" class="form-control zb-pin" required autocomplete="new-password">
                    </div>
                    <div class="zb-field">
                        <label for="zb-repeat-pin">{{ __('bank_ui.d_repeat_pin') }}</label>
                        <input id="zb-repeat-pin" name="confirm_pin" type="password" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" class="form-control zb-pin" required autocomplete="new-password">
                    </div>
                </div>
                <button type="submit" class="themeBtn">{{ __('bank_ui.d_save') }}</button>
            </form>
        </section>
    </div>
</div>
@endsection
