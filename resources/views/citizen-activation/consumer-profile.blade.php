@extends('layouts.profile')

@section('title', 'Consumer Profile')

@section('content')

<div class="grid grid-cols-1 gap-5">

    <div class="page-shell">

        {{-- Progress Bar --}}
        @include('citizen-activation.layouts.progress',['step'=>5])

        <div class="step-header">

            <div class="step-eyebrow">
                Step 5 of 6 — Consumer Profile
            </div>

            <div class="ca-step-title">
                What kind of spender are you?
            </div>

            <div class="step-subtitle">
                Answer a short <span class="term">consumer profile</span> survey — your answers
                will set your personal monthly expenses inside Zedville.
            </div>

        </div>

        <div class="step-body">

            <div class="narrative">
                In real life, your <strong>consumer profile</strong> is shaped by your lifestyle
                choices—what you eat, how you travel and what you do for fun.

                Your answers here will generate your personal monthly expenses inside Zedville.

                Choose honestly—this becomes your financial reality in the city.
            </div>
            <div class="survey-options">
             <!-- Consumer profile code -->
              @include('citizen-activation.layouts.surveys')
             <!-- Consumer profile code -->
            </div>
            {{-- The survey above is a client-side-only quiz that never
                 submits to the server (bugs.md: "Consumer profile survey
                 never actually submits") - this is a temporary way to get
                 past this step until that's fixed for real. --}}
            <div class="btn-row">
                <a href="{{ route('CitizenActivation.autoDebit') }}"
                    class="ca-btn-secondary">
                    ← Back
                </a>

                <form action="{{ route('CitizenActivation.consumerProfile.skip') }}" method="POST">
                    @csrf
                    <button type="submit" class="ca-btn-primary">
                        Skip for now →
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

@endsection