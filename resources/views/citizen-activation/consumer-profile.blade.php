@extends('layouts.profile')

@section('title', 'Consumer Profile')

@section('content')

<div class="grid grid-cols-1 gap-5">

    {{-- wide, with little above the survey, so each of its steps fits the screen
         without scrolling - as in Settings (Daniel, 2026-10-28) --}}
    <div class="page-shell page-shell--survey">

        {{-- Progress Bar --}}
        @include('citizen-activation.layouts.progress',['step'=>5])

        <div class="step-header">

            <div class="step-eyebrow">
                {{ __('citizen.eyebrow', ['step' => 5, 'total' => 6, 'name' => __('citizen.profile.step')]) }}
            </div>

            <div class="ca-step-title">
                {{ __('citizen.profile.title') }}
            </div>

            <div class="step-subtitle">
                {!! __('citizen.profile.subtitle') !!}
            </div>

        </div>

        <div class="step-body">

            <div class="survey-options">
             <!-- Consumer profile code -->
              @include('citizen-activation.layouts.surveys')
             <!-- Consumer profile code -->
            </div>
        </div>

    </div>

</div>

@endsection

@push('styles')
<style>
    main:has(.page-shell--survey) { padding-top: 20px; padding-bottom: 4px; position: relative; }
    main:has(.page-shell--survey) > .zv-page-back { position: absolute; top: 20px; left: 32px; }
    main:has(.page-shell--survey) > .zv-page-back > a { margin-bottom: 0; }
    .page-shell--survey { width: 100%; max-width: 1140px; padding: 0; }
    .page-shell--survey .progress-header { margin-bottom: 8px; padding-left: 120px; min-height: 38px; }
    .page-shell--survey .step-header { padding: 0 0 6px; margin-bottom: 8px; }
    .page-shell--survey .step-eyebrow { display: none; } /* the progress bar says which step this is */
    .page-shell--survey .ca-step-title { font-size: 1.2rem; margin-bottom: 2px; }
    .page-shell--survey .step-subtitle { font-size: .88rem; line-height: 1.45; }
    /* answers one under the other still fit the screen (Daniel, 2026-10-29: "not two
       columns of the choices"): the subtitle only with the survey's first step - it says
       the same on every step - and the page's title stands for the survey's own */
    .page-shell--survey:has(.zvq > section:first-of-type[style*="display: none"]) .step-subtitle { display: none; }
    .page-shell--survey .zvq-title { display: none; }
    .page-shell--survey .zvq { padding-bottom: 12px; }
    .page-shell--survey .step-body { padding: 0; }
    .page-shell--survey .survey-options { margin-bottom: 0; }
    /* "Skip for now" in the middle of the Back / Next row, not on a row of its own */
    .page-shell--survey .zvq > form { position: absolute; left: 50%; bottom: 24px; transform: translateX(-50%); margin: 0 !important; }
</style>
@endpush