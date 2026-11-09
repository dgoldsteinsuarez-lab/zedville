{{-- Task 9, what went up? The Statistics Office's chart of each group's rise (prices/_bars;
     App\Services\Finhero\Inflation\WhatRose). --}}
<section class="zq-card" aria-labelledby="zq-rise">
    <h2 id="zq-rise">{{ __('inflation.rise.chart') }}</h2>
    @include('prices._bars', ['groups' => $stage['groups']])
</section>
