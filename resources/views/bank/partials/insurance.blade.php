{{-- The student's health insurance, from the Intermediate (App\Services\InsuranceService): their plan and what it
     costs, or that they have none yet, with the way to FinHero Task 15, where they compare and choose. --}}
@php
    $insurance = \App\Services\InsuranceService::current($user->id);
    $insurancePlan = $insurance ? \App\Services\InsuranceService::PLANS[$insurance->plan] : null;
@endphp
<section class="zb-card" aria-labelledby="zb-insurance" style="margin-top:16px">
    <h2 id="zb-insurance">{{ __('insurance.card_title') }}</h2>
    @if ($insurance)
        <p style="margin:0 0 4px"><strong>{{ __('insurance.card_plan', ['plan' => __('insurance.plan_' . $insurance->plan), 'premium' => zeds($insurancePlan['premium'])]) }}</strong></p>
        <p class="zb-muted" style="margin:0 0 8px">{{ __('insurance.card_terms', ['deductible' => zeds($insurancePlan['deductible']), 'copay' => \App\Services\PriceRise::pct($insurancePlan['copay'] * 100, false)]) }}</p>
        @if ($insurance->auto)
            <p style="margin:0 0 8px">{{ __('insurance.card_auto') }}</p>
        @endif
    @else
        <p style="margin:0 0 8px">{{ __('insurance.card_none') }}</p>
    @endif
    <div class="zb-actions"><a href="{{ route('task.page', ['task' => 'task-15']) }}" class="themeBtn">{{ __($insurance && !$insurance->auto ? 'insurance.card_see' : 'insurance.card_choose') }}</a></div>
</section>
