@extends('layouts.profile')

@section('title', __('zeddirecto.name'))

{{-- Zed Directo, the Intermediate's second bank from its third month (ZedDirectoController; App\Services\ZedDirecto):
     another bank, with its own colours - its rates next to the Money Market's with the real interest, the savings
     account, the 6-month deposit and the movements. Hybrid classes: the facts, without the explanations. --}}
@section('content')
@php
    $pct = fn ($n, $sign = false) => \App\Services\PriceRise::pct($n, $sign);
    $index = \App\Services\PriceRise::INDEX;
    $line = fn (string $text) => \Illuminate\Support\Facades\Lang::has('transactions.lines.' . $text) ? __('transactions.lines.' . $text) : $text;
    $date = fn ($d) => \Carbon\Carbon::parse($d)->locale(app()->getLocale())->isoFormat('LL');
    $savings = (float) $account->zd_savings_amount;
    $deposit = (float) $account->zd_deposit_amount;
@endphp
<div class="zd">
    <header class="zd-head">
        <p class="zd-logo"><span aria-hidden="true">◆</span> {{ __('zeddirecto.name') }}</p>
        <p class="zd-tagline">{{ __('zeddirecto.tagline') }}</p>
    </header>
    <p class="zd-note">{{ __('zeddirecto.other_bank') }}</p>

    @unless ($open)
        <section class="zd-card"><p style="margin:0">{{ __('zeddirecto.not_yet', ['month' => $opensOn->copy()->locale(app()->getLocale())->isoFormat('MMMM')]) }}</p></section>
    @else
        <section class="zd-card" aria-labelledby="zd-compare">
            <h2 id="zd-compare">{{ __('zeddirecto.compare_title') }}</h2>
            <p>{{ __('zeddirecto.compare_intro', ['index' => $pct($index)]) }}</p>
            <div class="zd-table-wrap">
                <table class="zd-table">
                    <thead><tr><th scope="col">{{ __('zeddirecto.col_where') }}</th><th scope="col" class="zd-num">{{ __('zeddirecto.col_rate') }}</th><th scope="col" class="zd-num">{{ __('zeddirecto.col_real') }}</th><th scope="col">{{ __('zeddirecto.col_when') }}</th></tr></thead>
                    <tbody>
                        {{-- the cuenta remunerada: a month with money taken out earns nothing (its contract) --}}
                        @foreach ([['row_mm', $rates['mm'], 'when_mm'], ['row_savings', $rates['savings'], 'when_any'], ['row_deposit', $rates['deposit'], 'when_end']] as [$row, $rate, $when])
                            <tr>
                                <td>{{ __('zeddirecto.' . $row) }}</td>
                                <td class="zd-num">{{ $pct($rate) }}</td>
                                <td class="zd-num"><strong>{{ $pct(round($rate - $index, 2), true) }}</strong></td>
                                <td>{{ __('zeddirecto.' . $when) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="zd-grid">
            <section class="zd-card" aria-labelledby="zd-savings">
                <h2 id="zd-savings">{{ __('zeddirecto.savings_title') }}</h2>
                <p>{{ __('zeddirecto.savings_text', ['rate' => $pct($rates['savings'])]) }}</p>
                @unless ($savingsOpen)
                    <form method="POST" action="{{ route('zeddirecto.open') }}">@csrf
                        <button type="submit" class="zd-btn">{{ __('zeddirecto.open') }}</button>
                    </form>
                @else
                    <p class="zd-amount">{{ zeds($savings) }}</p>
                    <p class="zd-small">{{ __('zeddirecto.number') }}: {{ $account->zd_savings_number }} · {{ __('zeddirecto.interest_next', ['amount' => zeds(\App\Services\SavingsLedger::monthlyInterest($savings, \App\Services\ZedDirecto::SAVINGS))]) }}</p>
                    <form method="POST" action="{{ route('zeddirecto.move') }}" class="zd-form">@csrf
                        <input type="hidden" name="way" value="in">
                        <label for="zd-in">{{ __('zeddirecto.in_title') }}</label>
                        <div class="zd-row"><input id="zd-in" type="number" name="amount" min="0.01" step="0.01" required placeholder="{{ __('zeddirecto.amount') }}"><button type="submit" class="zd-btn">{{ __('zeddirecto.send') }}</button></div>
                        <p class="zd-small">{{ __('zeddirecto.in_hint') }}</p>
                    </form>
                    <form method="POST" action="{{ route('zeddirecto.move') }}" class="zd-form">@csrf
                        <input type="hidden" name="way" value="out">
                        <label for="zd-out">{{ __('zeddirecto.out_title') }}</label>
                        <div class="zd-row"><input id="zd-out" type="number" name="amount" min="0.01" step="0.01" max="{{ $savings }}" required placeholder="{{ __('zeddirecto.amount') }}"><button type="submit" class="zd-btn zd-btn--light">{{ __('zeddirecto.back') }}</button></div>
                    </form>
                @endunless
            </section>

            <section class="zd-card" aria-labelledby="zd-deposit">
                <h2 id="zd-deposit">{{ __('zeddirecto.deposit_title') }}</h2>
                <p>{{ __('zeddirecto.deposit_text', ['rate' => $pct($rates['deposit'])]) }}</p>
                @if (!$savingsOpen)
                    <p class="zd-small">{{ __('zeddirecto.deposit_first') }}</p>
                @elseif ($depositOpen)
                    <p class="zd-amount">{{ zeds($deposit) }}</p>
                    <p>{{ __('zeddirecto.deposit_active', ['amount' => zeds($deposit), 'date' => $date($account->zd_deposit_matures_on), 'interest' => zeds(\App\Services\ZedDirecto::depositInterest($deposit))]) }}</p>
                    <form method="POST" action="{{ route('zeddirecto.cancel') }}" onsubmit="return confirm(@js(__('zeddirecto.deposit_cancel_confirm', ['interest' => zeds(\App\Services\ZedDirecto::depositInterest($deposit))])))">@csrf
                        <button type="submit" class="zd-btn zd-btn--light">{{ __('zeddirecto.deposit_cancel') }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('zeddirecto.deposit') }}" class="zd-form">@csrf
                        <label for="zd-dep">{{ __('zeddirecto.deposit_new', ['min' => zeds(\App\Services\ZedDirecto::MIN_DEPOSIT)]) }}</label>
                        <div class="zd-row"><input id="zd-dep" type="number" name="amount" min="{{ \App\Services\ZedDirecto::MIN_DEPOSIT }}" step="0.01" max="{{ $savings }}" required placeholder="{{ __('zeddirecto.amount') }}"><button type="submit" class="zd-btn">{{ __('zeddirecto.deposit_make') }}</button></div>
                        <p class="zd-small">{{ __('zeddirecto.deposit_preview', ['example' => zeds(1000), 'interest' => zeds(\App\Services\ZedDirecto::depositInterest(1000))]) }}</p>
                    </form>
                @endif
            </section>
        </div>

        <section class="zd-card" aria-labelledby="zd-movements">
            <h2 id="zd-movements">{{ __('zeddirecto.movements') }}</h2>
            @if ($movements->isEmpty())
                <p class="zd-small" style="margin:0">{{ __('zeddirecto.no_movements') }}</p>
            @else
                <div class="zd-table-wrap">
                    <table class="zd-table">
                        <tbody>
                            @foreach ($movements as $movement)
                                <tr>
                                    <td class="zd-nowrap">{{ \Carbon\Carbon::parse($movement->transaction_date)->locale(app()->getLocale())->isoFormat('D MMM') }}</td>
                                    <td>{{ __($movement->account === \App\Services\ZedDirecto::DEPOSIT ? 'zeddirecto.deposit_title' : 'zeddirecto.savings_title') }} · {{ $line($movement->description) }}</td>
                                    <td class="zd-num {{ $movement->type === 'credit' ? 'zd-in' : '' }}">{{ zeds($movement->type === 'credit' ? (float) $movement->amount : -(float) $movement->amount, true) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endunless
</div>

<style>
    .zd { --zd-ink: #1B1840; --zd-brand: #4338CA; --zd-soft: #EEF0FF; --zd-line: #D9DBF2; --zd-muted: #55597A;
        max-width: 900px; margin: 0 auto; padding: 8px 4px 24px; color: var(--zd-ink); }
    .zd-head { display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 14px; margin-bottom: 6px; }
    .zd-logo { margin: 0; font: 800 28px/1.2 "Manrope", sans-serif; color: var(--zd-brand); letter-spacing: -0.5px; }
    .zd-tagline { margin: 0; font-size: 16px; color: var(--zd-muted); }
    .zd-note { margin: 0 0 16px; padding: 10px 14px; border-radius: 12px; background: var(--zd-soft); font-size: 15px; }
    .zd-card { background: #fff; border: 1px solid var(--zd-line); border-radius: 16px; padding: 18px 20px; margin-bottom: 16px; font-size: 16px; line-height: 1.5; }
    .zd-card h2 { font: 800 18px/1.3 "Manrope", sans-serif; margin: 0 0 10px; color: var(--zd-brand); }
    .zd-card p { margin: 0 0 10px; }
    .zd-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
    .zd-grid .zd-card { margin-bottom: 0; }
    .zd-grid { margin-bottom: 16px; }
    .zd-amount { font: 800 28px/1.2 "Manrope", sans-serif; }
    .zd-small { font-size: 14px; color: var(--zd-muted); }
    .zd-form { margin-top: 12px; }
    .zd-form label { display: block; font-weight: 700; font-size: 15px; margin-bottom: 6px; }
    .zd-row { display: flex; gap: 8px; }
    .zd-row input { flex: 1; min-width: 0; padding: 9px 12px; border: 2px solid var(--zd-line); border-radius: 10px; font-size: 16px; }
    .zd-btn { padding: 9px 16px; border: 0; border-radius: 10px; background: var(--zd-brand); color: #fff; font-weight: 700; font-size: 15px; cursor: pointer; }
    .zd-btn--light { background: var(--zd-soft); color: var(--zd-brand); }
    .zd-table-wrap { overflow-x: auto; width: 0; min-width: 100%; }
    .zd-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .zd-table th, .zd-table td { padding: 7px 8px; border-bottom: 1px solid var(--zd-line); text-align: left; vertical-align: top; }
    .zd-table thead th { color: var(--zd-muted); font-weight: 700; }
    .zd-num { text-align: right !important; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .zd-nowrap { white-space: nowrap; }
    .zd-in { color: #0B7A55; }
    @media (max-width: 560px) {
        .zd-card { padding: 14px 12px; }
        .zd-table { font-size: 13px; }
        .zd-table th, .zd-table td { padding: 6px 4px; }
    }
</style>
@endsection
