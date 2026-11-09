{{-- The student's saved Consumer Profile (Settings): their consumer type,
     their month by Need/Want, and when they can update it - the survey is
     once per calendar month (Daniel, 2026-10-23). Same look as the survey
     itself (surveys/wizard). --}}
@include('surveys._styles')
@php
    $w = __('surveys.wizard');
    $surveyProfile = !empty($Consumersurvey) ? $Consumersurvey->profile() : null;
    $money = fn ($amount) => 'Ƶ' . number_format((float) $amount, 2);
    $rows = [
        [$w['groceries'], $basketTotal ?? 0, false, $itemNames ?? ''],
        [$w['school_lunch'], $lunch ?? 0, false, ''],
        [$w['transport'], $transport ?? 0, (float) ($transport ?? 0) == \App\Models\ConsumerSurvey::TAXI, ''],
        [$w['activities'], $activities ?? 0, true, ''],
        [$w['eating_out'], $restaurants ?? 0, true, ''],
    ];
    $needsPct = $surveyProfile && $surveyProfile['total'] > 0 ? (int) round($surveyProfile['needs'] / $surveyProfile['total'] * 100) : 0;
@endphp

<div class="zvq">
    <div class="zvq-head">
        <div class="zvq-title">{{ __('surveys.results_title') }}</div>
    </div>

    @if ($surveyProfile)
        <div class="zvq-type">
            <div class="zvq-type-title">{{ $surveyProfile['title'] }}</div>
            <p class="zvq-type-text">{{ $surveyProfile['text'] }}</p>
            <p class="zvq-type-tip">{{ $surveyProfile['tip'] }}</p>
        </div>
    @endif

    @if (!empty($upcomingSurvey))
        <div class="zvq-note mb-3">{{ __('surveys.upcoming', ['month' => $upcomingSurvey->monthStart()->translatedFormat('F Y')]) }}</div>
    @endif

    <div class="zvq-summary">
        <div class="zvq-summary-title">{{ $w['your_month'] }}</div>
        @foreach ($rows as [$label, $amount, $isWant, $detail])
            <div class="zvq-summary-row">
                <span>
                    {{ $label }}
                    @if ($detail)
                        <br><small class="text-gray-500">{{ $detail }}</small>
                    @endif
                </span>
                <span class="zvq-summary-right">
                    <span class="zvq-tag {{ $isWant ? 'is-want' : 'is-need' }}">{{ $isWant ? $w['want'] : $w['need'] }}</span>
                    <strong>{{ $money($amount) }}</strong>
                </span>
            </div>
        @endforeach
        <div class="zvq-summary-row zvq-summary-total">
            <span>{{ $w['total'] }}</span><strong>{{ $money($total ?? 0) }}</strong>
        </div>
        @if ($surveyProfile)
            <div class="zvq-split">
                <div class="zvq-split-needs" style="width: {{ $needsPct }}%"></div>
                <div class="zvq-split-wants" style="width: {{ 100 - $needsPct }}%"></div>
            </div>
            <div class="zvq-split-legend">
                <span><i class="is-need"></i> {{ $w['needs'] }} <b>{{ $needsPct }}%</b></span>
                <span><i class="is-want"></i> {{ $w['wants'] }} <b>{{ 100 - $needsPct }}%</b></span>
            </div>
        @endif
    </div>

    <p class="zvq-muted mt-3 mb-0">{!! __('surveys.good_to_know') !!}</p>

    <div class="zvq-nav">
        <span class="zvq-muted">{{ __('surveys.changed_habits') }}</span>
        @if ($surveyDoneThisMonth ?? true)
            <span class="zvq-count">{{ __('surveys.once_per_month', ['date' => now()->startOfMonth()->addMonth()->translatedFormat('j F')]) }}</span>
        @else
            <a href="{{ route('consumer-profile-survey') }}" class="themeBtn">{{ __('surveys.update_profile') }}</a>
        @endif
    </div>
</div>
