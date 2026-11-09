@extends('layouts.profile')

@section('title', __('loans.title'))

{{-- The bank's Loans page, from the Intermediate (LoanController; App\Services\LoanService): the terms, a simulator
     that works out the same table the bank will use, the request with the bank's 20 % check, and each loan with
     its table - interest and capital of each instalment - the 14 days to change one's mind and paying it off. --}}
@section('content')
@php
    $pct = fn ($n) => \App\Services\PriceRise::pct($n, false);
    $date = fn ($d) => \Carbon\Carbon::parse($d)->locale(app()->getLocale())->isoFormat('LL');
    $short = fn ($d) => \Carbon\Carbon::parse($d)->locale(app()->getLocale())->isoFormat('D MMM YY');
@endphp
<div class="zb-page">
    @include('bank.partials.nav')

    <div class="zb-grid zb-grid--2">
        <section class="zb-card" aria-labelledby="zl-terms">
            <h2 id="zl-terms">{{ __('loans.facts_title') }}</h2>
            <p class="zb-muted">{{ __('loans.intro') }}</p>
            <ul class="zl-facts">
                <li>{{ __('loans.fact_amount', ['min' => zeds(\App\Services\LoanService::MIN), 'max' => zeds(\App\Services\LoanService::MAX)]) }}</li>
                <li>{{ __('loans.fact_tae', ['tae' => $pct(\App\Services\LoanService::TAE * 100)]) }}</li>
                <li>{{ __('loans.fact_day') }}</li>
                <li>{{ __('loans.fact_limit', ['max' => zeds($maxLoad)]) }}</li>
                <li>{{ __('loans.fact_cancel') }}</li>
                <li>{{ __('loans.fact_late') }}</li>
            </ul>
        </section>

        <section class="zb-card" aria-labelledby="zl-sim" x-data="zlSimulator(@js(['rate' => $monthlyRate, 'load' => $load, 'max' => $maxLoad, 'min' => \App\Services\LoanService::MIN, 'top' => \App\Services\LoanService::MAX, 'lang' => app()->getLocale()]))">
            <h2 id="zl-sim">{{ __('loans.sim_title') }}</h2>
            @if ($blocked)
                <p class="zl-warn">{{ __('loans.blocked') }}</p>
            @endif
            <form method="POST" action="{{ route('bank.loans.store') }}">@csrf
                <div class="zl-row">
                    <label>{{ __('loans.amount') }}
                        <input type="number" name="amount" x-model.number="amount" min="{{ \App\Services\LoanService::MIN }}" max="{{ \App\Services\LoanService::MAX }}" step="50" required>
                    </label>
                    <label>{{ __('loans.months') }}
                        <select name="months" x-model.number="months">
                            @foreach (\App\Services\LoanService::TERMS as $term)
                                <option value="{{ $term }}">{{ __('loans.months_n', ['n' => $term]) }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <dl class="zl-quote">
                    <dt>{{ __('loans.instalment') }}</dt><dd x-text="money(quote().instalment)"></dd>
                    <dt>{{ __('loans.total') }}</dt><dd x-text="money(quote().total)"></dd>
                    <dt>{{ __('loans.interest') }}</dt><dd x-text="money(quote().interest)"></dd>
                </dl>
                <p class="zb-small">{{ __('loans.your_load', ['load' => zeds($load), 'max' => zeds($maxLoad)]) }}</p>
                <p class="zl-warn" x-show="!fits()" x-cloak>{{ __('loans.too_much') }}</p>
                <label class="zl-accept"><input type="checkbox" name="accept" value="1" required> {{ __('loans.accept') }}</label>
                <button type="submit" class="themeBtn" :disabled="!fits() || amount < cfg.min || amount > cfg.top" @if ($blocked) disabled @endif>{{ __('loans.ask') }}</button>
            </form>
        </section>
    </div>

    <section class="zb-card" aria-labelledby="zl-yours" style="margin-top:16px">
        <h2 id="zl-yours">{{ __('loans.yours') }}</h2>
        @forelse ($loans as $loan)
            <article class="zl-loan">
                <p class="zl-loan-head">
                    <strong>{{ __('loans.loan_line', ['amount' => zeds((float) $loan->amount), 'months' => $loan->months, 'instalment' => zeds((float) $loan->instalment), 'interest' => zeds((float) $loan->interest_total)]) }}</strong>
                    <span class="zl-badge zl-badge--{{ $loan->status }}">{{ __('loans.status_' . $loan->status) }}</span>
                </p>
                @if ($loan->status === 'active')
                    <div class="zl-actions">
                        @if (!today()->gt($loan->cancel_until) && !$loan->instalments->whereIn('status', ['paid', 'paid_late'])->count())
                            <form method="POST" action="{{ route('bank.loans.cancel', $loan) }}">@csrf
                                <p class="zb-small">{{ __('loans.cancel_until', ['date' => $date($loan->cancel_until), 'amount' => zeds((float) $loan->amount)]) }}</p>
                                <button type="submit" class="whiteBtn">{{ __('loans.cancel') }}</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('bank.loans.payoff', $loan) }}">@csrf
                            <p class="zb-small">{{ __('loans.payoff_hint') }}</p>
                            <button type="submit" class="whiteBtn">{{ __('loans.payoff', ['amount' => zeds(\App\Services\LoanService::owed($loan))]) }}</button>
                        </form>
                    </div>
                @endif
                <div class="zl-table-wrap">
                    <table class="zb-table">
                        <thead><tr><th>{{ __('loans.col_n') }}</th><th>{{ __('loans.col_date') }}</th><th class="zl-num">{{ __('loans.col_amount') }}</th><th class="zl-num">{{ __('loans.col_interest') }}</th><th class="zl-num">{{ __('loans.col_capital') }}</th><th class="zl-num">{{ __('loans.col_left') }}</th><th>{{ __('loans.col_state') }}</th></tr></thead>
                        <tbody>
                            @foreach ($loan->instalments as $row)
                                <tr class="zl-state--{{ $row->status }}">
                                    <td>{{ $row->number }}</td>
                                    <td class="zl-nowrap">{{ $short($row->due_on) }}</td>
                                    <td class="zl-num">{{ zeds((float) $row->amount) }}</td>
                                    <td class="zl-num">{{ zeds((float) $row->interest) }}</td>
                                    <td class="zl-num">{{ zeds((float) $row->capital) }}</td>
                                    <td class="zl-num">{{ zeds((float) $row->remaining) }}</td>
                                    <td>{{ __('loans.state_' . $row->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="zb-small">{{ __('loans.table_hint') }}</p>
            </article>
        @empty
            <p class="zb-muted" style="margin:0">{{ __('loans.none') }}</p>
        @endforelse
    </section>
</div>

<style>
    .zl-facts { margin: 0; padding-left: 18px; font-size: 14px; line-height: 1.6; color: #0F2A22; }
    .zl-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
    .zl-row label { display: flex; flex-direction: column; gap: 4px; font-weight: 700; font-size: 14px; flex: 1; min-width: 140px; }
    .zl-row input, .zl-row select { padding: 8px 10px; border: 2px solid #D2DDDB; border-radius: 10px; font-size: 16px; }
    .zl-quote { display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; margin: 0 0 10px; font-size: 15px; }
    .zl-quote dt { color: #5E7069; }
    .zl-quote dd { margin: 0; font-weight: 800; text-align: right; font-variant-numeric: tabular-nums; }
    .zl-warn { color: #8E1F25; font-weight: 700; font-size: 14px; }
    .zl-accept { display: flex; gap: 8px; align-items: center; font-size: 14px; margin: 8px 0 12px; }
    .zl-loan { border-top: 1px solid #E6ECEA; padding-top: 12px; margin-top: 12px; }
    .zl-loan:first-of-type { border-top: 0; margin-top: 0; padding-top: 0; }
    .zl-loan-head { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; margin: 0 0 8px; }
    .zl-badge { font-size: 13px; font-weight: 700; padding: 2px 10px; border-radius: 999px; background: #EEF3F1; color: #0F2A22; }
    .zl-badge--active { background: #E5F4E4; color: #0B7A55; }
    .zl-actions { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 10px; }
    .zl-actions form { max-width: 360px; }
    .zl-table-wrap { overflow-x: auto; width: 0; min-width: 100%; }
    .zl-num { text-align: right !important; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .zl-nowrap { white-space: nowrap; }
    .zl-state--late td { color: #8E1F25; }
    .zl-state--paid td, .zl-state--paid_early td { color: #5E7069; }
</style>
<script>
    // the same table as App\Services\LoanService::table: equal instalments, the last one closes the rest
    function zlSimulator(cfg) {
        return {
            cfg, amount: 1000, months: 12,
            quote() {
                const i = cfg.rate, a = Math.max(0, Number(this.amount) || 0), n = Number(this.months);
                if (!a) return { instalment: 0, total: 0, interest: 0 };
                const round = v => Math.round(v * 100) / 100;
                const instalment = round(a * i / (1 - Math.pow(1 + i, -n)));
                let left = round(a), total = 0;
                for (let k = 1; k <= n; k++) {
                    const interest = round(left * i);
                    const capital = k === n ? left : round(instalment - interest);
                    left = round(left - capital);
                    total = round(total + capital + interest);
                }
                return { instalment, total, interest: round(total - a) };
            },
            fits() { return Math.round((cfg.load + this.quote().instalment) * 100) <= Math.round(cfg.max * 100); },
            // as App\Support\Money::format: "Ƶ 1,000.00" in English, "1.000,00 Ƶ" in the others
            money(v) {
                const n = Number(v).toLocaleString(cfg.lang === 'en' ? 'en-GB' : cfg.lang, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                return cfg.lang === 'en' ? 'Ƶ ' + n : n + ' Ƶ';
            },
        };
    }
</script>
@endsection
