@extends('layouts.profile')

@section('title', __('messages.nav_bank_account'))

{{-- The bank's Overview (Daniel, 2026-10-29: "the userfriendlyness is more
     important than the design ... for kids but also for grade 11 and 12"): the
     three accounts with one name each and what each is for, where this month's
     money went, the bills, the latest movements. --}}
@section('content')
@php
    $keys = ['current', 'emergency', 'money_market'];
    $open = [
        'current' => true,
        'emergency' => (int) $bankAccount->is_open_emergency_account === 1,
        'money_market' => (int) $bankAccount->is_open_money_market_account === 1,
    ];
    $balances = [
        'current' => $current,
        'emergency' => (float) $bankAccount->emergency_fund_account_amount,
        'money_market' => (float) $bankAccount->money_market_account_amount,
    ];
    $numbers = [
        'current' => $bankAccount->primary_savings_account_number,
        'emergency' => $bankAccount->emergency_fund_account_number,
        'money_market' => $bankAccount->money_market_account_number,
    ];
    // the bar is the month's salary (or what went out, if more): each colour its share, the grey rest not spent
    $spent = max(0.01, $summary['salary'], $summary['needs'] + $summary['wants'] + $summary['savings'] + $summary['other']);
    $date = fn ($d) => $d->copy()->locale(app()->getLocale())->isoFormat('D MMM');
@endphp
<div class="zb-page" x-data="{ openEmergencyModal: false, openAccountModal: false }">
    @include('bank.partials.nav')

    <p class="zb-muted" style="margin:-4px 0 14px">{{ __('bank_ui.hello', ['name' => $user->display_name]) }}</p>
    @if ($current < 0)
        {{-- the overdraft, from the Intermediate (App\Services\Overdraft) --}}
        <p class="zb-errors" role="alert" style="margin:0 0 14px">{{ __('bank_ui.overdrawn', ['amount' => zeds($current), 'tae' => \App\Services\PriceRise::pct(\App\Services\Overdraft::TAE * 100, false), 'fee' => zeds(\App\Services\Overdraft::FEE)]) }}</p>
    @endif
    @include('bank.partials.emergency')
    @include('bank.partials.easy-activities', ['link' => true])

    <div class="zb-grid zb-grid--3">
        @foreach ($keys as $key)
            <section class="zb-card zb-acc zb-acc--{{ $key }}" aria-labelledby="zb-acc-{{ $key }}">
                <div class="zb-acc-name" id="zb-acc-{{ $key }}"><span class="zb-dot"></span>{{ __('bank_ui.' . $key) }}</div>
                @if ($open[$key])
                    <div class="zb-amount">{{ zeds($balances[$key]) }}</div>
                    <div class="zb-num">{{ __('bank_ui.account_number') }}: {{ $numbers[$key] }}</div>
                @else
                    <div class="zb-amount zb-amount--off">{{ __('bank_ui.not_open') }}</div>
                @endif
                {{-- the Money Market's rate is the student's: 5 % from the Intermediate's third month (ZedDirecto) --}}
                <p class="zb-muted" style="margin:0">{{ __('bank_ui.' . $key . '_hint', ['rate' => \App\Services\PriceRise::pct(\App\Services\SavingsLedger::rate('money_market', $user) * 100, false)]) }}</p>
                @if ($key !== 'current' && $open[$key])
                    <p class="zb-small" style="margin:0">
                        {{-- the Money Market's contract: money taken out this month, no interest this month --}}
                        @if ($interest[$key]['withdrawn'] ?? false)
                            {{ __('bank_ui.interest_lost') }}
                        @else
                            {{ __('bank_ui.interest_next', ['date' => $interest[$key]['next_payment_date'], 'amount' => zeds($interest[$key]['estimated_interest'])]) }}
                        @endif
                        @if ($interest[$key]['earned'] > 0)
                            · {{ __('bank_ui.interest_earned', ['amount' => zeds($interest[$key]['earned'])]) }}
                        @endif
                    </p>
                @endif
                <div class="zb-actions">
                    @if ($key === 'current')
                        <a href="{{ route('bank.transfer', ['to' => 'friend']) }}" class="themeBtn">{{ __('bank_ui.choice_friend') }}</a>
                        <a href="{{ route('bank.transfer', ['to' => 'bill']) }}" class="whiteBtn">{{ __('bank_ui.choice_bill') }}</a>
                    @elseif ($open[$key])
                        <a href="{{ route('bank.transfer', ['to' => 'own']) }}" class="whiteBtn">{{ __('bank_ui.choice_own') }}</a>
                        <a href="{{ route('bank.bank_statement_show', ['account' => $key]) }}" class="whiteBtn">{{ __('bank_ui.see_statement') }}</a>
                    @else
                        <button type="button" class="themeBtn" @click="{{ $key === 'emergency' ? 'openEmergencyModal' : 'openAccountModal' }} = true">{{ __('bank_ui.open_it') }}</button>
                    @endif
                </div>
            </section>
        @endforeach
    </div>

    @if ($saver)
        @include('bank.partials.saver')
    @endif
    @if (\App\Services\InsuranceService::offered($user))
        @include('bank.partials.insurance')
    @endif
    @if (\App\Services\ZedDirecto::open($user))
        @include('bank.partials.zed-directo')
    @endif

    <div class="zb-grid zb-grid--2" style="margin-top:16px">
        <section class="zb-card" aria-labelledby="zb-month">
            <h2 id="zb-month">{{ __('bank_ui.this_month') }}</h2>
            <dl class="zb-legend">
                <dt>{{ __('bank_ui.m_salary') }}</dt><dd class="zb-in">{{ zeds($summary['salary'], true) }}</dd>
            </dl>
            <div class="zb-bar" aria-hidden="true">
                @foreach (['needs', 'wants', 'savings', 'other'] as $group)
                    @if ($summary[$group] > 0)
                        <span class="zb-c-{{ $group }}" style="width: {{ round($summary[$group] / $spent * 100, 2) }}%"></span>
                    @endif
                @endforeach
            </div>
            <dl class="zb-legend">
                @foreach (['needs' => 'm_needs', 'wants' => 'm_wants', 'savings' => 'm_savings', 'other' => 'm_other'] as $group => $label)
                    <dt><span class="zb-sw zb-c-{{ $group }}"></span>{{ __('bank_ui.' . $label) }}</dt><dd>{{ zeds($summary[$group]) }}</dd>
                @endforeach
                <dt class="is-total">{{ __('bank_ui.m_left') }}</dt><dd class="is-total">{{ zeds($summary['left']) }}</dd>
            </dl>
        </section>

        <section class="zb-card" aria-labelledby="zb-bills">
            <h2 id="zb-bills">{{ __('bank_ui.bills_title') }}</h2>
            <p class="zb-small" style="margin:-6px 0 6px">{{ __('bank_ui.bills_auto') }}</p>
            <div class="zb-list">
                @foreach ($bills['fixed'] as $bill)
                    <div class="zb-row">
                        <span class="zb-tick {{ $bill['paid'] ? 'is-paid' : ($bill['late'] ? 'is-late' : 'is-due') }}" aria-hidden="true">{{ $bill['paid'] ? '✓' : ($bill['late'] ? '!' : '…') }}</span>
                        <div class="zb-row-main">
                            <div class="zb-row-title">{{ $bill['text'] }}</div>
                            <div class="zb-row-sub">{{ $bill['paid'] ? __('bank_ui.bill_paid_on', ['date' => $date($bill['date'])]) : ($bill['late'] ? __('bank_ui.bill_not_paid') : __('bank_ui.bill_due_on', ['date' => $date($bill['date'])])) }}</div>
                        </div>
                        @if ($bill['paid'])
                            <span class="zb-row-amount">{{ zeds(-$bill['amount']) }}</span>
                        @endif
                    </div>
                @endforeach
                @foreach ($bills['plans'] as $plan)
                    <div class="zb-row">
                        <span class="zb-tick {{ ['charged' => 'is-paid', 'waiting' => 'is-late'][$plan['status']] ?? 'is-due' }}" aria-hidden="true">{{ ['charged' => '✓', 'waiting' => '!'][$plan['status']] ?? '…' }}</span>
                        <div class="zb-row-main">
                            <div class="zb-row-title">{{ $plan['text'] }}</div>
                            <div class="zb-row-sub">{{ $plan['status'] === 'waiting' ? __('bank_ui.plan_waiting') : __('bank_ui.plan_' . ($plan['status'] === 'charged' ? 'paid_on' : 'due_on'), ['date' => $date($plan['date'])]) }}</div>
                        </div>
                        <span class="zb-row-amount">{{ zeds(-$plan['amount']) }}</span>
                    </div>
                @endforeach
            </div>
            @if ($bills['scheduled']->isNotEmpty())
                <h2 style="margin-top:14px">{{ __('bank_ui.coming_up') }}</h2>
                <div class="zb-list">
                    @foreach ($bills['scheduled'] as $order)
                        <div class="zb-row">
                            <span class="zb-tick is-due" aria-hidden="true">↻</span>
                            <div class="zb-row-main">
                                <div class="zb-row-title">{{ $order->targetLabel($bankAccount) }}</div>
                                <div class="zb-row-sub">{{ __('bank_ui.next_run', ['date' => $date($order->next_run_at)]) }}</div>
                            </div>
                            <span class="zb-row-amount">{{ zeds(-$order->amount) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="zb-card" style="margin-top:16px" aria-labelledby="zb-recent">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px">
            <h2 id="zb-recent" style="margin:0">{{ __('bank_ui.recent') }}</h2>
            <a href="{{ route('bank.bank_statement_show') }}" class="whiteBtn" style="margin-left:auto;padding:6px 14px;font-size:14px">{{ __('bank_ui.see_statement') }}</a>
        </div>
        <div class="zb-list">
            @forelse ($recent as $line)
                <div class="zb-row">
                    <div class="zb-row-main">
                        <div class="zb-row-title">{{ $line['text'] }}</div>
                        <div class="zb-row-sub">{{ $line['date']->copy()->locale(app()->getLocale())->isoFormat('LL') }}</div>
                    </div>
                    <span class="zb-row-amount {{ $line['amount'] > 0 ? 'zb-in' : '' }}">{{ zeds($line['amount'], true) }}</span>
                </div>
            @empty
                <p class="zb-muted">{{ __('bank_ui.nothing_yet') }}</p>
            @endforelse
        </div>
    </section>

    @unless ($open['emergency'])
        @include('bank.partials.open-emergency')
    @endunless
    @unless ($open['money_market'])
        @include('bank.partials.open-money-market')
    @endunless
</div>
@endsection
