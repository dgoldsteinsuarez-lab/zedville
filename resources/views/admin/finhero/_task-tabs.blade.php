{{-- Needs, Wants & Savings Match is one of the FinHero tasks (Daniel,
     2026-10-31: "should be inside the FinHero Task Activity") - a tab of that
     screen, not a menu entry of its own. Its assignments still live in
     mba_position, so the tab opens its own form. From the Intermediate on there
     is no 50/30/20: no Match, and Task 5 "How much have I saved?" takes its
     place (Daniel, 2026-11-04). --}}
@php
    $workingOrder = (int) optional(\App\Support\AdminLevel::current())->sort_order;
    $saverLevel = $workingOrder >= \App\Support\Level::INTERMEDIATE;
@endphp
<ul class="nav nav-tabs mab-15">
    <li class="nav-item">
        <a class="nav-link {{ request()->is('admin/finhero/activities') ? 'active' : '' }}" href="{{ url('admin/finhero/activities') }}">{{ $saverLevel ? 'Tasks' : 'Tasks 1–4' }}</a>
    </li>
    @unless ($saverLevel)
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/education/monthly-budget-activity*') ? 'active' : '' }}" href="{{ url('admin/education/monthly-budget-activity') }}">Needs, Wants &amp; Savings Match</a>
        </li>
    @endunless
</ul>
@if ($saverLevel)
    <p class="text-muted small mab-15">From the Intermediate on there are no 50/30/20 tasks (Tasks 3 and 4, the Match): students save as much as they can, and Task 5 "How much have I saved?" is their monthly look at it. Tasks 6–8 are about compound interest; Tasks 9–14 about the price rise of the level's second month (assign them from that month). Task 15 "Choose your health insurance" is for the level's first month, as Required: it is done once the student has chosen a plan, and whoever has none when their first month ends gets the Basic plan from the City Hall. Task 16 "Compare two loans" (month 4) and Task 17 "Your money grows" (month 3, with the student's own Money Market) are Required in the proposal.</p>
@endif
