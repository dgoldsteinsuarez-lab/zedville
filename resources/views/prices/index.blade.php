@extends('layouts.profile')

@section('title', __('prices.title'))

{{-- The Zedville Statistics Office (Student\PriceIndexController; App\Services\PriceRise): the Intermediate's
     price rise of its second month - the index, what rose and why with two charts, and the student's own
     month at the old prices and the new ones. Hybrid classes: the facts and the student's numbers, without
     the explanations. --}}
@section('content')
@php
    $hybrid = \App\Support\Hybrid::on();
    $indexText = \App\Services\PriceRise::pct(\App\Services\PriceRise::INDEX, false);
    // the index chart: from 98 to 106, one step a month; narrow, so its text stays at least 12px on a phone
    [$w, $h, $padL, $padR, $padT, $padB] = [400, 230, 40, 26, 32, 42];
    $count = count($series);
    $x = fn ($i) => round($padL + ($count > 1 ? $i / ($count - 1) : 0) * ($w - $padL - $padR), 1);
    $y = fn ($v) => round($padT + (106 - $v) / 8 * ($h - $padT - $padB), 1);
@endphp
<div class="zp">
    <p class="zp-eyebrow"><span aria-hidden="true">🏛️</span> {{ __('prices.office') }}</p>
    <h1 class="zp-title">{{ __('prices.title') }}</h1>

    <section class="zp-card zp-hero">
        <p class="zp-big" aria-hidden="true">+{{ $indexText }}</p>
        <div>
            <h2>{{ __('prices.headline', ['month' => $month, 'index' => $indexText]) }}</h2>
            @unless ($on)
                <p class="zp-note">{{ __('prices.not_yet', ['month' => $month]) }}</p>
            @endunless
            <p>{{ __('prices.intro', ['month' => $month, 'index' => $indexText]) }}</p>
            <p><strong>{{ __('prices.salary') }}</strong></p>
        </div>
    </section>

    <section class="zp-card" aria-labelledby="zp-groups">
        <h2 id="zp-groups">{{ __('prices.chart_groups') }}</h2>
        {{-- the table below has the same numbers for screen readers --}}
        @include('prices._bars', ['groups' => $groups])
    </section>

    <section class="zp-card" aria-labelledby="zp-why">
        <h2 id="zp-why">{{ __('prices.why_title') }}</h2>
        @unless ($hybrid)
            <p>{{ __('prices.why', ['index' => $indexText]) }}</p>
        @endunless
        <div class="zp-table-wrap">
            <table class="zp-table">
                <thead><tr><th scope="col">{{ __('prices.col_what') }}</th><th scope="col" class="zp-num">{{ __('prices.col_rise') }}</th>@unless ($hybrid)<th scope="col">{{ __('prices.col_why') }}</th>@endunless</tr></thead>
                <tbody>
                    @foreach ($groups as $group)
                        <tr><td>{{ $group['name'] }}</td><td class="zp-num">{{ \App\Services\PriceRise::pct($group['pct']) }}</td>@unless ($hybrid)<td>{{ $group['reason'] }}</td>@endunless</tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="zp-card" aria-labelledby="zp-index">
        <h2 id="zp-index">{{ __('prices.chart_index') }}</h2>
        <svg class="zp-line" viewBox="0 0 {{ $w }} {{ $h }}" role="img" aria-label="{{ __('prices.chart_index') }}: {{ collect($series)->map(fn ($p) => __('prices.month_n', ['n' => $p['month']]) . ' ' . $p['value'])->implode(', ') }}">
            @foreach ([98, 100, 102, 104, 106] as $tick)
                <line x1="{{ $padL }}" x2="{{ $w - $padR }}" y1="{{ $y($tick) }}" y2="{{ $y($tick) }}" class="zp-grid"/>
                <text x="{{ $padL - 8 }}" y="{{ $y($tick) + 5 }}" text-anchor="end" class="zp-axis">{{ $tick }}</text>
            @endforeach
            <polyline fill="none" class="zp-path" points="{{ collect($series)->map(fn ($p, $i) => $x($i) . ',' . $y($p['value']))->implode(' ') }}"/>
            @foreach ($series as $i => $p)
                <circle cx="{{ $x($i) }}" cy="{{ $y($p['value']) }}" r="5" class="zp-dot"><title>{{ __('prices.month_n', ['n' => $p['month']]) }}: {{ $p['value'] }}</title></circle>
                @if ($count <= 4 || $i === 0 || $i === $count - 1 || ($i % 3 === 0 && $count - 1 - $i >= 2))
                    <text x="{{ $x($i) }}" y="{{ $h - 12 }}" text-anchor="middle" class="zp-axis">{{ __('prices.month_n', ['n' => $p['month']]) }}</text>
                @endif
                @if ($i === 0 || $i === $count - 1)
                    {{-- the first one to the right of its point, clear of the axis's numbers --}}
                    <text x="{{ $i === 0 ? $x($i) + 8 : $x($i) }}" y="{{ $y($p['value']) - 14 }}" text-anchor="{{ $i === 0 ? 'start' : 'middle' }}" class="zp-label">{{ $p['value'] }}</text>
                @endif
            @endforeach
        </svg>
        <p class="zp-legend">{{ __('prices.index_note', ['base' => zeds(100), 'value' => zeds(100 + \App\Services\PriceRise::INDEX)]) }}</p>
    </section>

    <section class="zp-card" aria-labelledby="zp-month">
        <h2 id="zp-month">{{ __('prices.basket_title') }}</h2>
        <p>{{ __('prices.basket_intro') }}</p>
        <div class="zp-table-wrap">
            <table class="zp-table">
                <thead><tr><th scope="col"><span class="sr-only">{{ __('prices.col_what') }}</span></th><th scope="col" class="zp-num">{{ __('prices.before') }}</th><th scope="col" class="zp-num">{{ __('prices.now') }}</th><th scope="col" class="zp-num">{{ __('prices.difference') }}</th></tr></thead>
                <tbody>
                    @foreach ($lines as $line)
                        <tr><td>{{ $line['name'] }}</td><td class="zp-num">{{ zeds($line['before']) }}</td><td class="zp-num">{{ zeds($line['after']) }}</td><td class="zp-num">{{ zeds($line['after'] - $line['before'], true) }}</td></tr>
                    @endforeach
                </tbody>
                <tfoot><tr><th scope="row">{{ __('prices.total') }}</th><td class="zp-num">{{ zeds($before) }}</td><td class="zp-num">{{ zeds($after) }}</td><td class="zp-num">{{ zeds($after - $before, true) }}</td></tr></tfoot>
            </table>
        </div>
        @unless ($hasSurvey)
            <p class="zp-note">{{ __('prices.no_survey') }}</p>
        @endunless
        <p><strong>{{ __('prices.month_extra', ['extra' => zeds($after - $before), 'before' => zeds($before), 'after' => zeds($after)]) }}</strong></p>
        <p>{{ __('prices.also') }}</p>
    </section>
</div>

<style>
    .zp { --zp-ink: #0F2A22; --zp-muted: #4F625B; --zp-line: #D2DDDB; --zp-up: #C8541E; --zp-down: #1F6FAE; --zp-surface: #fff;
        max-width: 860px; margin: 0 auto; padding: 8px 4px 24px; color: var(--zp-ink); }
    .zp-eyebrow { margin: 0 0 4px; font-size: 14px; font-weight: 700; color: var(--zp-muted); }
    .zp-title { font: 800 26px/1.25 "Manrope", sans-serif; margin: 0 0 16px; }
    .zp-card { background: var(--zp-surface); border: 1px solid var(--zp-line); border-radius: 16px; padding: 18px 20px; margin-bottom: 16px; font-size: 16px; line-height: 1.5; }
    .zp-card h2 { font: 800 18px/1.3 "Manrope", sans-serif; margin: 0 0 12px; }
    .zp-card p { margin: 0 0 10px; }
    .zp-hero { display: flex; gap: 18px; align-items: flex-start; }
    .zp-big { font: 800 44px/1 "Manrope", sans-serif; color: var(--zp-up); margin: 0; white-space: nowrap; }
    .zp-note { color: var(--zp-muted); font-size: 14px; }
    .zp-card > .zp-legend { margin: 12px 0 0; font-size: 14px; color: var(--zp-muted); }
    /* a table never widens the page: if it doesn't fit, it scrolls inside its card */
    .zp-table-wrap { overflow-x: auto; width: 0; min-width: 100%; }
    .zp-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .zp-table th, .zp-table td { padding: 7px 8px; border-bottom: 1px solid var(--zp-line); text-align: left; vertical-align: top; }
    .zp-table thead th { color: var(--zp-muted); font-weight: 700; }
    .zp-table tfoot th, .zp-table tfoot td { font-weight: 800; border-bottom: 0; }
    .zp-num { text-align: right !important; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .zp-line { width: 100%; max-width: 520px; height: auto; display: block; }
    .zp-grid { stroke: var(--zp-line); stroke-width: 1; }
    .zp-axis { font-size: 15px; fill: var(--zp-muted); }
    .zp-label { font-size: 17px; font-weight: 800; fill: var(--zp-ink); }
    .zp-path { stroke: var(--zp-up); stroke-width: 2.5; stroke-linejoin: round; }
    .zp-dot { fill: var(--zp-up); stroke: var(--zp-surface); stroke-width: 2; }
    .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
    @media (max-width: 560px) {
        .zp-hero { flex-direction: column; gap: 6px; }
        .zp-card { padding: 14px 12px; }
        .zp-table { font-size: 13px; }
        .zp-table th, .zp-table td { padding: 6px 4px; }
    }
</style>
@endsection
