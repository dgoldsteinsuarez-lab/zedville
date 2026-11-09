@extends('layouts.profile')

@section('title', __('emergency.title'))

{{-- «Resuelve tu imprevisto» (EmergencyController; App\Services\EmergencyService): what happened and what it costs the
     student (a health one, after their insurance), the ways to pay it with what each costs in all, then the two
     questions. Hybrid classes: the facts, without the why. --}}
@section('content')
@php
    $date = fn ($d) => \Carbon\Carbon::parse($d)->locale(app()->getLocale())->isoFormat('LL');
    $hybrid = \App\Support\Hybrid::on();
@endphp
<div class="ze">
    <h1 class="ze-title">{{ __('emergency.title') }}</h1>

    @if (!$emergency)
        <section class="ze-card"><p style="margin:0">{{ __('emergency.none') }}</p></section>
    @else
        @php
            $k = \App\Services\EmergencyService::KINDS[$emergency->kind];
            $cover = $emergency->cover();
            $plan = $emergency->plan ? __('insurance.plan_' . $emergency->plan) : null;
        @endphp
        <section class="ze-card ze-story" aria-labelledby="ze-what">
            <p class="ze-icon" aria-hidden="true">{{ $k['icon'] }}</p>
            <h2 id="ze-what">{{ __('emergency.kinds.' . $emergency->kind . '.title') }}</h2>
            <p>{{ __('emergency.kinds.' . $emergency->kind . '.text') }}</p>
            <dl class="ze-facts">
                <dt>{{ __('emergency.costs') }}</dt><dd class="ze-amt">{{ zeds((float) $emergency->cost) }}</dd>
                @if ($cover && $cover !== 'none')
                    <dt>{{ __('emergency.insurance_pays', ['plan' => $plan]) }}</dt><dd class="ze-amt">{{ zeds((float) $emergency->cost - (float) $emergency->amount) }}</dd>
                @endif
                <dt class="is-total">{{ __('emergency.you_pay') }}</dt><dd class="is-total ze-amt">{{ zeds((float) $emergency->amount) }}</dd>
                @if ($emergency->status === 'pending')
                    {{-- the last day, and under it the days left --}}
                    <dt>{{ __('emergency.due') }}</dt>
                    <dd>{{ $date($emergency->due_on) }}<span class="ze-left">{{ \App\Support\Deadline::label($emergency->due_on) }}</span></dd>
                @endif
            </dl>
            @if ($cover === 'none')
                <p class="ze-note">{{ __('emergency.no_insurance') }}</p>
            @elseif ($cover === 'waiting')
                <p class="ze-note">{{ __('emergency.waiting') }}</p>
            @elseif ($cover === 'deductible')
                <p class="ze-note">{{ __('emergency.deductible', ['deductible' => zeds(\App\Services\InsuranceService::PLANS[$emergency->plan]['deductible'])]) }}</p>
            @endif
        </section>

        @if ($emergency->status === 'pending')
            <section class="ze-card" aria-labelledby="ze-how">
                <h2 id="ze-how">{{ __('emergency.how_title') }}</h2>
                <p>{{ __('emergency.how_text') }}</p>
                @if (empty($options))
                    <p class="ze-note">{{ __('emergency.no_way') }}</p>
                @else
                    <form method="POST" action="{{ route('emergency.pay') }}">
                        @csrf
                        <input type="hidden" name="emergency" value="{{ $emergency->id }}">
                        @foreach ($options as $key => $o)
                            @php
                                [$label, $detail] = match (true) {
                                    $key === 'fund' => [__('emergency.opt_fund'), __('emergency.opt_fund_detail', ['from' => zeds($balances['fund']), 'to' => zeds($balances['fund'] - $o['fund'])])],
                                    $key === 'fund_current' => [__('emergency.opt_fund_current'), __('emergency.opt_fund_current_detail', ['fund' => zeds($o['fund']), 'current' => zeds($o['current'])])],
                                    $key === 'current' => [__('emergency.opt_current'), __('emergency.opt_current_detail', ['left' => zeds($balances['current'] - $o['current'])])],
                                    default => [__($o['loan'] > (float) $emergency->amount ? 'emergency.opt_loan_min' : 'emergency.opt_loan', ['amount' => zeds($o['loan']), 'months' => $o['months']]),
                                        __('emergency.opt_loan_detail', ['months' => $o['months'], 'instalment' => zeds($o['instalment']), 'interest' => zeds($o['interest'])])],
                                };
                            @endphp
                            <label class="ze-opt">
                                <input type="radio" name="option" value="{{ $key }}" required>
                                <span class="ze-opt-main"><strong>{{ $label }}</strong><span>{{ $detail }}</span></span>
                                <span class="ze-opt-total"><small>{{ __('emergency.total') }}</small>{{ zeds($o['total']) }}</span>
                            </label>
                        @endforeach
                        <button type="submit" class="themeBtn">{{ __('emergency.pay') }}</button>
                    </form>
                @endif
                <p class="ze-small">{{ __('emergency.if_not', ['date' => $date($emergency->due_on), 'tae' => \App\Services\PriceRise::pct(\App\Services\Overdraft::TAE * 100, false), 'fee' => zeds(\App\Services\Overdraft::FEE)]) }}</p>
            </section>
        @else
            <section class="ze-card" aria-labelledby="ze-paid">
                <h2 id="ze-paid">{{ __('emergency.paid_title') }}</h2>
                @php $loan = $emergency->loan_id ? \App\Models\Loan::find($emergency->loan_id) : null; @endphp
                <p>
                    @if ($emergency->status === 'auto')
                        {{ __('emergency.auto_' . $emergency->method, ['date' => $date($emergency->paid_at)]) }}
                    @elseif ($emergency->method === 'loan')
                        {{ __('emergency.paid_loan', ['date' => $date($emergency->paid_at), 'instalment' => zeds((float) optional($loan)->instalment), 'months' => (int) optional($loan)->months]) }}
                    @else
                        {{ __('emergency.paid_' . $emergency->method, ['date' => $date($emergency->paid_at)]) }}
                    @endif
                </p>

                @if ($emergency->completed_at)
                    <p class="ze-done">✓ {{ __('emergency.done') }}</p>
                    @if ($why && !$hybrid)<p class="ze-why">{{ $why }}</p>@endif
                    @if ($emergency->change)<p>{{ __('emergency.your_change', ['change' => __('emergency.changes.' . $emergency->change)]) }}</p>@endif
                @elseif ($question)
                    <form method="POST" action="{{ route('emergency.answers') }}" style="margin-top:16px">
                        @csrf
                        <input type="hidden" name="emergency" value="{{ $emergency->id }}">
                        <fieldset class="ze-q">
                            <legend>1. {{ $question['text'] }}</legend>
                            @foreach ($question['options'] as $i => $option)
                                <label class="ze-choice"><input type="radio" name="cost" value="{{ $i }}" required @checked((string) old('cost') === (string) $i)> <span>{{ $option }}</span></label>
                            @endforeach
                        </fieldset>
                        <fieldset class="ze-q">
                            <legend>2. {{ __('emergency.q_change') }}</legend>
                            @foreach (\App\Services\EmergencyService::changes($emergency) as $change)
                                <label class="ze-choice"><input type="radio" name="change" value="{{ $change }}" required @checked(old('change') === $change)> <span>{{ __('emergency.changes.' . $change) }}</span></label>
                            @endforeach
                        </fieldset>
                        <button type="submit" class="themeBtn">{{ __('emergency.send') }}</button>
                    </form>
                @endif
            </section>
        @endif
    @endif

    @if ($history->isNotEmpty())
        <section class="ze-card" aria-labelledby="ze-history">
            <h2 id="ze-history">{{ __('emergency.history') }}</h2>
            <ul class="ze-history">
                @foreach ($history as $old)
                    <li>
                        <span>{{ \App\Services\EmergencyService::KINDS[$old->kind]['icon'] }} {{ __('emergency.kinds.' . $old->kind . '.title') }}</span>
                        <span class="ze-small">{{ $date($old->arrived_at) }}@if ($old->method) · {{ __('emergency.methods.' . $old->method) }}@endif · {{ zeds($old->realCost()) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>

<style>
    .ze { max-width: 760px; margin: 0 auto; padding: 8px 4px 24px; color: #0F2A22; }
    .ze-title { font: 800 26px/1.25 "Manrope", sans-serif; margin: 0 0 16px; }
    .ze-card { background: #fff; border: 1px solid #D2DDDB; border-radius: 16px; padding: 18px 20px; margin-bottom: 16px; font-size: 16px; line-height: 1.5; }
    .ze-card h2 { font: 800 18px/1.3 "Manrope", sans-serif; margin: 0 0 10px; }
    .ze-story { border-color: #F2B8A0; background: #FFF8F4; }
    .ze-icon { font-size: 36px; line-height: 1; margin: 0 0 8px; }
    .ze-facts { display: grid; grid-template-columns: 1fr auto; gap: 6px 16px; margin: 12px 0 0; }
    .ze-facts dt { color: #3E5A51; }
    .ze-facts dd { margin: 0; text-align: right; font-variant-numeric: tabular-nums; }
    .ze-facts .ze-amt { white-space: nowrap; }
    .ze-left { display: block; font-size: 14px; color: #5E7069; }
    .ze-facts .is-total { font-weight: 800; color: #0F2A22; }
    .ze-note { margin: 10px 0 0; font-size: 15px; color: #8E3B12; }
    .ze-opt { display: grid; grid-template-columns: auto 1fr auto; gap: 10px; align-items: center; padding: 10px 12px; border: 2px solid #D2DDDB;
        border-radius: 12px; margin-bottom: 10px; cursor: pointer; }
    .ze-opt:has(input:checked) { border-color: #00A47D; background: #F0FBF7; }
    .ze-opt input, .ze-choice input { accent-color: #00A47D; width: 18px; height: 18px; }
    .ze-opt-main { display: flex; flex-direction: column; gap: 2px; font-size: 15px; }
    .ze-opt-main span { font-size: 14px; color: #3E5A51; }
    .ze-opt-total { text-align: right; font-weight: 800; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .ze-opt-total small { display: block; font-weight: 400; font-size: 12px; color: #5E7069; }
    .ze-small { font-size: 14px; color: #5E7069; margin: 12px 0 0; }
    .ze-q { border: 0; padding: 0; margin: 0 0 16px; }
    .ze-q legend { font-weight: 700; margin-bottom: 8px; }
    .ze-choice { display: flex; gap: 10px; align-items: center; padding: 9px 12px; border: 2px solid #D2DDDB; border-radius: 12px; margin-bottom: 8px; cursor: pointer; font-size: 15px; }
    .ze-choice:has(input:checked) { border-color: #00A47D; background: #F0FBF7; }
    .ze-done { font-weight: 800; color: #0B7A55; }
    .ze-why { color: #0B7A55; font-size: 15px; }
    .ze-history { list-style: none; margin: 0; padding: 0; }
    .ze-history li { display: flex; flex-direction: column; padding: 8px 0; border-bottom: 1px solid #E4ECEA; }
    .ze-history li:last-child { border-bottom: 0; }
    @media (max-width: 560px) {
        .ze-card { padding: 14px 12px; }
        .ze-opt { grid-template-columns: auto 1fr; }
        .ze-opt-total { grid-column: 2; text-align: left; }
        .ze-opt-total small { display: inline; margin-right: 6px; }
    }
</style>
@endsection
