@extends('layouts.profile')

@section('title', 'Citizen Activation Complete')

@section('content')

@php
    $zeds = fn ($amount) => 'Ƶ' . number_format((float) $amount, 2);
    // The onboarding survey can be skipped - that leaves a zero-cost record.
    $surveyDone = $survey && (float) $survey->total_cost > 0;
@endphp

<div class="grid grid-cols-1 gap-5">

    <div class="page-shell">

        {{-- Progress Bar --}}
        @include('citizen-activation.layouts.progress',['step'=>6])

        <div class="step-header">

            <div class="step-eyebrow">
                {{ __('citizen.eyebrow', ['step' => 6, 'total' => 6, 'name' => __('citizen.complete.step')]) }}
            </div>

            <div class="ca-step-title">
                {!! __('citizen.complete.title', ['name' => e($user->name)]) !!}
            </div>

            <div class="step-subtitle">
                {!! __('citizen.complete.subtitle') !!}
            </div>

        </div>

        <div class="step-body">

            {{-- Completion Banner --}}
            <div class="completion-banner">

                <div class="big">
                    {{ __('citizen.complete.banner') }}
                </div>

                <div class="sub">
                    {{ __('citizen.complete.banner_text') }}
                </div>

            </div>

            {{-- Spending Snapshot (the student's own survey answers) --}}
            @if ($surveyDone)
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1.5rem">

                <p style="font-size:.82rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem">
                    {{ __('surveys.snapshot') }}
                </p>

                <div class="snapshot-cat">🍖 {{ __('surveys.food_groceries') }}</div>

                <div class="snapshot-row">
                    <span>{{ __('surveys.monthly_groceries') }}</span>
                    <span>{{ $zeds($survey->basket_cost) }}</span>
                </div>

                <div class="snapshot-row">
                    <span>{{ __('surveys.school_lunch') }}</span>
                    <span>{{ $zeds($survey->lunch) }}</span>
                </div>

                <div class="snapshot-cat">
                    🚌 {{ __('surveys.getting_around') }}
                </div>

                <div class="snapshot-row">
                    <span>{{ __('surveys.travel_to_school') }}</span>
                    <span>{{ $zeds($survey->transport) }}</span>
                </div>

                <div class="snapshot-cat">
                    🎮 {{ __('surveys.fun_free_time') }}
                </div>

                <div class="snapshot-row">
                    <span>{{ __('surveys.activities_hobbies') }}</span>
                    <span>{{ $zeds($survey->activities) }}</span>
                </div>

                <div class="snapshot-row">
                    <span>{{ __('surveys.eating_out') }}</span>
                    <span>{{ $zeds($survey->restaurants) }}</span>
                </div>

                <div class="snapshot-total">

                    <span class="label">
                        {{ __('citizen.complete.total') }}
                    </span>

                    <span class="amount">
                        {{ $zeds($survey->total_cost) }}
                    </span>

                </div>

            </div>
            @endif

            {{-- Citizen Diary --}}
            <div class="diary-heading">
                {{ __('citizen.complete.diary') }}
            </div>

            <div class="diary-entry">

                <div class="diary-date">
                    {{ __('citizen.complete.diary_bank_date') }}
                </div>

                <div class="diary-text">
                    {!! __('citizen.complete.diary_bank', ['account' => e($bankAccount->primary_savings_account_number ?? '—')]) !!}
                </div>

            </div>

            <div class="diary-entry">

                <div class="diary-date">
                    {{ __('citizen.complete.diary_salary_date') }}
                </div>

                <div class="diary-text">
                    {!! __('citizen.complete.diary_salary') !!}
                </div>

            </div>

            <div class="diary-entry">

                <div class="diary-date">
                    {{ __('citizen.complete.diary_bills_date') }}
                </div>

                <div class="diary-text">
                    {!! __('citizen.complete.diary_bills', ['amount' => 'Ƶ' . number_format(config('zedville.school_fees', 1000))]) !!}
                </div>

            </div>

            <div class="diary-entry">

                <div class="diary-date">
                    {{ __('citizen.complete.diary_profile_date') }}
                </div>

                <div class="diary-text">
                    @if ($surveyDone)
                        {!! __('citizen.complete.diary_profile', ['amount' => $zeds($survey->total_cost)]) !!}
                    @else
                        {!! __('citizen.complete.diary_profile_skipped') !!}
                    @endif
                </div>

            </div>

            <div class="btn-row" style="margin-top:1.5rem">

                <a href="{{ route('bank.index') }}"
                   class="ca-btn-primary">
                    {{ __('citizen.complete.go_bank') }}
                </a>

                <a href="{{ route('CitizenActivation.consumerProfile') }}"
                   class="ca-btn-secondary">
                    {{ __('citizen.back') }}
                </a>

            </div>

        </div>

    </div>

</div>

@endsection
