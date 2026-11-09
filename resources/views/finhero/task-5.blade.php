@extends('layouts.profile')

@section('title', __('savings_check.title'))

{{-- FinHero Task 5, "How much have I saved?" (Daniel, 2026-11-04: in the Intermediate "una tarea de
     reflexión sobre cuánto ha podido ahorrar, simple, que supliría a la de Match"): what the student
     saved this month and last month - from their own Emergency Fund and Money Market - and two
     questions to choose an answer for, no writing and no wrong answers. Finishing records the task
     (finhero.task-complete) and its points. --}}
@section('content')
@php
    $user = auth()->user();
    $now = \App\Services\SaverService::savedThisMonth($user->id);
    $before = \App\Services\SaverService::savedThisMonth($user->id, now()->subMonthNoOverflow());
    $account = \App\Models\BankAccount::where('student_id', $user->id)->first();
    $share = $account ? \App\Services\SaverService::fundPercent($user, $account) : null;
    $pct = function ($n) {
        $s = rtrim(rtrim(number_format((float) $n, 1, '.', ''), '0'), '.');

        return app()->getLocale() === 'en' ? $s . '%' : str_replace('.', ',', $s) . ' %';
    };
    $questions = ['q1' => trans('savings_check.q1_options'), 'q2' => trans('savings_check.q2_options')];
@endphp
<div class="zs-page" x-data="zsCheck()">
    <h1 class="zs-title">{{ __('savings_check.title') }}</h1>

    <section class="zs-card">
        @if ($now['amount'] > 0)
            <p class="zs-big">{{ __('savings_check.saved', ['amount' => zeds($now['amount']), 'percent' => $pct($now['percent'])]) }}</p>
        @else
            <p class="zs-big">{{ __('savings_check.saved_none') }}</p>
        @endif
        <p>{{ __('savings_check.last', ['amount' => zeds($before['amount']), 'percent' => $pct($before['percent'])]) }}</p>
        @if ($share !== null)
            <p>{{ __('savings_check.share', ['percent' => $pct($share)]) }} <a href="{{ route('bank.index') }}">{{ __('savings_check.change_share') }}</a></p>
        @endif
    </section>

    <section class="zs-card" x-show="!done">
        @foreach ($questions as $key => $options)
            <fieldset class="zs-q">
                <legend>{{ __('savings_check.' . $key) }}</legend>
                @foreach ($options as $i => $option)
                    <label class="zs-option"><input type="radio" name="{{ $key }}" value="{{ $i }}" x-model="answers.{{ $key }}"> {{ $option }}</label>
                @endforeach
            </fieldset>
        @endforeach
        <p class="zs-warn" x-show="missing" x-cloak>{{ __('savings_check.choose_both') }}</p>
        <button type="button" class="themeBtn" @click="finish()" x-bind:disabled="busy">{{ __('savings_check.finish') }}</button>
    </section>

    <section class="zs-card" x-show="done" x-cloak>
        <p class="zs-big">{{ __('savings_check.done') }}</p>
        @include('partials.finish')
    </section>
</div>

<style>
    .zs-page { max-width: 760px; margin: 0 auto; padding: 8px 4px 24px; }
    .zs-title { font: 800 26px/1.25 "Manrope", sans-serif; color: #0F2A22; margin: 0 0 16px; }
    .zs-card { background: #fff; border: 1px solid #D2DDDB; border-radius: 16px; padding: 18px 20px; margin-bottom: 16px; font-size: 16px; line-height: 1.5; color: #0F2A22; }
    .zs-card p { margin: 0 0 8px; }
    .zs-big { font: 700 19px/1.4 "Manrope", sans-serif; }
    .zs-q { border: 0; padding: 0; margin: 0 0 18px; }
    .zs-q legend { font-weight: 700; font-size: 17px; margin-bottom: 8px; }
    .zs-option { display: flex; gap: 10px; align-items: center; padding: 10px 12px; border: 2px solid #D2DDDB; border-radius: 12px; margin-bottom: 8px; cursor: pointer; font-size: 16px; }
    .zs-option:has(input:checked) { border-color: #00A47D; background: #F0FBF7; }
    .zs-option input { accent-color: #00A47D; width: 18px; height: 18px; }
    .zs-warn { color: #8E1F25; font-weight: 600; }
</style>

<script>
    function zsCheck() {
        return {
            answers: { q1: null, q2: null }, missing: false, busy: false, done: false,
            async finish() {
                this.missing = this.answers.q1 === null || this.answers.q2 === null;
                if (this.missing) return;
                this.busy = true;
                const post = (url, body) => fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                    body: JSON.stringify(body),
                }).catch(() => {});
                // the task's points, then the task done (the board and the calendar)
                await post(@json(route('finhero.save-points')), { source_key: @json($activityKey) + '_question_1', approach: 0 });
                await post(@json(route('finhero.task-complete')), { activity_key: @json($activityKey) });
                this.done = true;
                this.busy = false;
            },
        };
    }
</script>
@endsection
