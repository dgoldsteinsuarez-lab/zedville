{{-- "Finish" at the end of an activity (Daniel, 2026-10-28: "put 'Finish' button on
     the bottom of all activities. Is it 'Finish' or 'Finished'" - Finish: a button
     says what it does). It leaves the activity for the place that lists it: the
     Educational Finance Department, whose board holds the assigned activities,
     unless the page passes $to. $inline: inside the page's own row of buttons
     instead of on a line of its own. --}}
@php
    $finishTo = $to ?? route('education.educational_finance_department');
@endphp
@if (!empty($inline))
    <a href="{{ $finishTo }}" class="themeBtn zv-finish-btn">{{ __('layout.finish') }}</a>
@else
    <div class="zv-finish">
        <a href="{{ $finishTo }}" class="themeBtn zv-finish-btn">{{ __('layout.finish') }}</a>
    </div>
@endif
@once
<style>
    .zv-finish { display: flex; justify-content: center; margin: 28px 0 8px; }
    .zv-finish-btn { display: inline-block; min-width: 180px; text-align: center; font-weight: 700; }
</style>
@endonce
