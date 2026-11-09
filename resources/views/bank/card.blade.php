@extends('layouts.profile')

@section('title', __('card.title'))

{{-- The bank's credit card page, from the Intermediate (CreditCardController; App\Services\CreditCardService): the
     conditions and what deferring costs, the request; with a card, what is owed and available, how each statement is
     paid, the next payment, the statements and the movements. Hybrid classes: the facts, without the explanation. --}}
@section('content')
@php
    $pct = fn ($n) => \App\Services\PriceRise::pct($n, false);
    $date = fn ($d) => \Carbon\Carbon::parse($d)->locale(app()->getLocale())->isoFormat('LL');
    $monthName = fn (string $ym) => \Carbon\Carbon::createFromFormat('!Y-m', $ym)->locale(app()->getLocale())->isoFormat('MMMM');
    $S = \App\Services\CreditCardService::class;
    $line = fn (string $text) => \Illuminate\Support\Facades\Lang::has('card.lines.' . $text) ? __('card.lines.' . $text) : txn_text($text);
@endphp
<div class="zb-page">
    @include('bank.partials.nav')

    <div class="zb-grid zb-grid--2">
        <section class="zb-card" aria-labelledby="zc-terms">
            <h2 id="zc-terms">{{ __('card.title') }}</h2>
            <p class="zb-muted">{{ __('card.intro') }}</p>
            <h3 class="zc-h3">{{ __('card.conditions') }}</h3>
            <ul class="zc-facts">
                <li>{{ __('card.c_limit', ['limit' => zeds($S::LIMIT)]) }}</li>
                <li>{{ __('card.c_full') }}</li>
                <li>{{ __('card.c_defer', ['tae' => $pct($S::TAE * 100)]) }}</li>
                <li>{{ __('card.c_minimum', ['pct' => $pct($S::MIN_SHARE * 100), 'floor' => zeds($S::MIN_FLOOR)]) }}</li>
                <li>{{ __('card.c_late') }}</li>
            </ul>
        </section>

        <section class="zb-card" aria-labelledby="zc-cost">
            <h2 id="zc-cost">{{ __('card.cost_title') }}</h2>
            <p>{{ __('card.cost_intro', ['amount' => zeds($example)]) }}</p>
            <div class="zc-table-wrap">
                <table class="zc-table">
                    <tbody>
                        <tr><th scope="row">{{ __('card.cost_full') }}</th><td>{{ trans_choice('card.cost_months', 1, ['count' => 1]) }}</td><td class="zc-num">{{ __('card.cost_free') }}</td></tr>
                        <tr><th scope="row">{{ __('card.cost_year', ['payment' => zeds($payoff['year_payment'])]) }}</th>
                            <td>{{ trans_choice('card.cost_months', $payoff['year']['months'], ['count' => $payoff['year']['months']]) }}</td>
                            <td class="zc-num">{{ __('card.cost_interest', ['amount' => zeds($payoff['year']['interest'])]) }}</td></tr>
                        <tr class="is-worst"><th scope="row">{{ __('card.cost_minimum') }}</th>
                            <td>{{ trans_choice('card.cost_months', $payoff['minimum']['months'], ['count' => $payoff['minimum']['months']]) }}</td>
                            <td class="zc-num">{{ __('card.cost_interest', ['amount' => zeds($payoff['minimum']['interest'])]) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @if (!$card)
        <section class="zb-card" style="margin-top:16px">
            @if ($refusal)
                <p class="zc-warn">{{ __('flash.' . $refusal) }}</p>
            @else
                <form method="POST" action="{{ route('bank.card.store') }}">@csrf
                    <label class="zc-accept"><input type="checkbox" name="accept" value="1" required> {{ __('card.accept') }}</label>
                    <button type="submit" class="themeBtn">{{ __('card.request') }}</button>
                </form>
            @endif
        </section>
    @else
        @php
            $pending = $statements->firstWhere('status', 'pending');
            $spentNow = (float) $movements->where('type', 'purchase')->filter(fn ($m) => $m->moved_at->isSameMonth(now()))->sum('amount');
        @endphp
        <div class="zb-grid zb-grid--2" style="margin-top:16px">
            <section class="zb-card zc-card" aria-labelledby="zc-mine">
                <h2 id="zc-mine" class="zc-number">{{ $card->number }}</h2>
                <dl class="zc-dl">
                    <dt>{{ __('card.owed') }}</dt><dd class="is-big">{{ zeds($owed) }}</dd>
                    <dt>{{ __('card.available') }}</dt><dd>{{ zeds($available) }}</dd>
                    <dt>{{ __('card.limit') }}</dt><dd>{{ zeds((float) $card->limit) }}</dd>
                </dl>
                <p class="zc-next">
                    @if ($pending)
                        {{ __('card.next_due', ['date' => $date(now()->day > $S::DAY ? now() : now()->day($S::DAY)), 'amount' => zeds($S::dueFor($card, (float) $pending->balance)),
                            'month' => $monthName($pending->month), 'owed' => zeds((float) $pending->balance)]) }}
                    @else
                        {{ __('card.next_none') }}
                    @endif
                </p>
                @if ($spentNow > 0)
                    <p class="zb-small">{{ __('card.running', ['amount' => zeds($spentNow)]) }}</p>
                @endif
            </section>

            <section class="zb-card" aria-labelledby="zc-mode">
                <h2 id="zc-mode">{{ __('card.mode_title') }}</h2>
                <form method="POST" action="{{ route('bank.card.mode') }}" x-data="{ mode: @js(old('mode', $card->mode)) }">@csrf
                    @foreach ($S::MODES as $mode)
                        <label class="zc-mode"><input type="radio" name="mode" value="{{ $mode }}" x-model="mode">
                            <span>{{ __('card.mode_' . $mode, ['pct' => $pct($S::MIN_SHARE * 100), 'floor' => zeds($S::MIN_FLOOR)]) }}</span></label>
                    @endforeach
                    <input type="number" name="fixed" min="{{ $S::MIN_FLOOR }}" step="1" class="zc-fixed" x-show="mode === 'fixed'" x-cloak
                        value="{{ old('fixed', $card->fixed_amount ? (int) $card->fixed_amount : '') }}" aria-label="{{ __('card.mode_fixed') }}">
                    <button type="submit" class="themeBtn">{{ __('card.mode_save') }}</button>
                </form>
            </section>
        </div>

        @if ($statements->isNotEmpty())
            <section class="zb-card" style="margin-top:16px" aria-labelledby="zc-statements">
                <h2 id="zc-statements">{{ __('card.statements') }}</h2>
                <div class="zc-table-wrap">
                    <table class="zc-table">
                        <thead><tr><th>{{ __('card.col_month') }}</th><th class="zc-num">{{ __('card.col_owed') }}</th><th class="zc-num">{{ __('card.col_paid') }}</th><th class="zc-num">{{ __('card.col_interest') }}</th></tr></thead>
                        <tbody>
                            @foreach ($statements as $st)
                                <tr>
                                    {{-- the status under the month: the table fits a phone --}}
                                    <td>{{ ucfirst($monthName($st->month)) }}<span class="zc-status is-{{ $st->status }}">{{ __('card.status.' . $st->status) }}</span></td>
                                    <td class="zc-num">{{ zeds((float) $st->balance) }}</td>
                                    <td class="zc-num">{{ $st->status === 'pending' ? '—' : zeds((float) $st->paid) }}</td>
                                    <td class="zc-num">{{ $st->status === 'pending' ? '—' : zeds((float) $st->interest) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="zb-card" style="margin-top:16px" aria-labelledby="zc-moves">
            <h2 id="zc-moves">{{ __('card.movements') }}</h2>
            @if ($movements->isEmpty())
                <p class="zb-muted">{{ __('card.none') }}</p>
            @else
                <div class="zb-list">
                    @foreach ($movements as $m)
                        <div class="zb-row">
                            <div class="zb-row-main">
                                <div class="zb-row-title">{{ $line($m->description) }}</div>
                                <div class="zb-row-sub">{{ $date($m->moved_at) }}</div>
                            </div>
                            <span class="zb-row-amount">{{ $m->type === 'payment' ? '−' : '+' }}{{ zeds((float) $m->amount) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endif
</div>

<style>
    .zc-h3 { font-weight: 800; font-size: 16px; margin: 12px 0 6px; }
    .zc-facts { margin: 0; padding-left: 18px; }
    .zc-facts li { margin-bottom: 4px; }
    .zc-table-wrap { overflow-x: auto; width: 0; min-width: 100%; }
    .zc-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .zc-table th, .zc-table td { padding: 7px 6px; border-bottom: 1px solid #D2DDDB; text-align: left; vertical-align: top; }
    .zc-table thead th { color: #5E7069; font-weight: 700; }
    .zc-table tbody th { font-weight: 600; }
    .zc-table .zc-num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .zc-table tr.is-worst td, .zc-table tr.is-worst th { color: #8E1F25; }
    .zc-status { display: block; font-size: 13px; color: #5E7069; }
    .zc-status.is-late { color: #8E1F25; font-weight: 700; }
    .zc-warn { color: #8E1F25; font-weight: 700; margin: 0; }
    .zc-accept { display: flex; gap: 8px; align-items: center; margin: 0 0 12px; }
    .zc-card { background: linear-gradient(135deg, #1F2A44, #3A4B7A); color: #fff; border: 0; }
    .zc-card .zc-number { color: #fff; font: 700 18px/1.3 monospace; letter-spacing: 1px; margin: 0 0 12px; }
    .zc-dl { display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; margin: 0 0 10px; }
    .zc-dl dt { color: #C9D3EA; }
    .zc-dl dd { margin: 0; text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .zc-dl dd.is-big { font-size: 20px; font-weight: 800; }
    .zc-next { margin: 0; color: #fff; }
    .zc-card .zb-small { color: #C9D3EA; }
    .zc-mode { display: flex; gap: 8px; align-items: center; padding: 8px 10px; border: 2px solid #D2DDDB; border-radius: 10px; margin-bottom: 8px; cursor: pointer; }
    .zc-mode:has(input:checked) { border-color: #00A47D; background: #F0FBF7; }
    .zc-fixed { width: 140px; margin: 0 0 10px; }
</style>
@endsection
