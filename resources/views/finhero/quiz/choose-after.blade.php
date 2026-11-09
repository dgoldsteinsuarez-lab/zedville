{{-- Task 15, once the answers are right: the choice of plan (inside the quiz, check({ plan }) sends it with the
     answers). Shown while the student has no plan of their own, or when a year has passed and they may change it;
     their own plan has no button. --}}
@php
    $mine = $stage['current'] && !$stage['current']['auto'] ? $stage['current']['plan'] : null;
    $mayChange = $mine && $stage['can_choose'];
@endphp
<div class="zq-choose" x-show="result && result.done && (result.choose || ({{ $mayChange ? 'true' : 'false' }} && !result.chosen))" x-cloak>
    <h3>{{ __('insurance.choose.your_choice') }}</h3>
    <div class="zq-plans">
        @foreach ($stage['plans'] as $key => $plan)
            @continue($key === $mine)
            <button type="button" class="zq-plan" @click="check({ plan: @js($key) })" x-bind:disabled="busy">
                <strong>{{ __('insurance.choose.pick', ['plan' => __('insurance.plan_' . $key)]) }}</strong>
                <span>{{ __('insurance.choose.per_month', ['premium' => $plan['premium']]) }}</span>
            </button>
        @endforeach
    </div>
    <p class="zq-legend">{{ __('insurance.choose.premium_when') }}</p>
</div>
<p class="zq-msg is-done" x-show="result && result.chosen" x-text="result ? result.chosen : ''" x-cloak></p>

<style>
    .zq-choose { margin: 14px 0 16px; }
    .zq-choose h3 { font: 800 16px/1.3 "Manrope", sans-serif; margin: 0 0 10px; }
    .zq-plans { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
    .zq-plan { display: flex; flex-direction: column; gap: 4px; text-align: left; padding: 12px 14px; border: 2px solid #00A47D;
        border-radius: 12px; background: #F0FBF7; color: #0F2A22; font-size: 15px; cursor: pointer; }
    .zq-plan:hover, .zq-plan:focus-visible { background: #DDF5EC; }
    .zq-plan:disabled { opacity: .6; cursor: default; }
    .zq-plan span { font-size: 14px; color: #3E5A51; }
</style>
