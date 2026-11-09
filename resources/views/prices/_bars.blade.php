{{-- How much each group rose (App\Services\PriceRise::GROUPS): diverging bars from zero - up to the right,
     down to the left - and the average's dashed line, each value written at its bar. $groups: [['name', 'pct'],
     ...]. The Statistics Office's page and FinHero Task 9 show it; the page's table has the same numbers. --}}
@php
    $zpIndex = \App\Services\PriceRise::INDEX;
    $zpIndexText = \App\Services\PriceRise::pct($zpIndex, false);
    // the scale: from -5% to +20%
    $zpAt = fn ($n) => round(($n + 5) / 25 * 100, 2);
@endphp
<div class="zp-bars" aria-hidden="true">
    <div class="zp-bar-row">
        <span></span>
        <span class="zp-bar-track"><span class="zp-avg-label" style="left:{{ $zpAt($zpIndex) }}%">{{ __('prices.average', ['index' => $zpIndexText]) }}</span></span>
        <span></span>
    </div>
    @foreach ($groups as $group)
        <div class="zp-bar-row" title="{{ $group['name'] }}: {{ \App\Services\PriceRise::pct($group['pct']) }}">
            <span class="zp-bar-name">{{ $group['name'] }}</span>
            <span class="zp-bar-track">
                @if ($group['pct'] > 0)
                    <span class="zp-bar zp-up" style="left:{{ $zpAt(0) }}%;width:{{ $zpAt($group['pct']) - $zpAt(0) }}%"></span>
                @elseif ($group['pct'] < 0)
                    <span class="zp-bar zp-down" style="left:{{ $zpAt($group['pct']) }}%;width:{{ $zpAt(0) - $zpAt($group['pct']) }}%"></span>
                @endif
                <span class="zp-zero" style="left:{{ $zpAt(0) }}%"></span>
                <span class="zp-avg" style="left:{{ $zpAt($zpIndex) }}%"></span>
            </span>
            <b class="zp-bar-value">{{ \App\Services\PriceRise::pct($group['pct']) }}</b>
        </div>
    @endforeach
</div>
<p class="zp-legend">
    <span class="zp-key zp-up" aria-hidden="true"></span> {{ __('prices.col_rise') }}
    · <span class="zp-key zp-down" aria-hidden="true"></span> {{ __('prices.groups.electronics') }}
    · <span class="zp-key zp-dash" aria-hidden="true"></span> {{ __('prices.average', ['index' => $zpIndexText]) }}
</p>
<style>
    .zp-bars { --zp-ink: #0F2A22; --zp-muted: #4F625B; --zp-up: #C8541E; --zp-down: #1F6FAE; display: flex; flex-direction: column; gap: 6px; color: var(--zp-ink); }
    .zp-bar-row { display: grid; grid-template-columns: minmax(110px, 36%) minmax(0, 1fr) 56px; align-items: center; gap: 10px; font-size: 14px; }
    .zp-bar-track { position: relative; height: 18px; }
    .zp-bar { position: absolute; top: 3px; height: 12px; }
    .zp-bar.zp-up { background: var(--zp-up); border-radius: 0 4px 4px 0; }
    .zp-bar.zp-down { background: var(--zp-down); border-radius: 4px 0 0 4px; }
    .zp-zero { position: absolute; top: -3px; bottom: -3px; width: 1px; background: var(--zp-muted); }
    .zp-avg { position: absolute; top: -3px; bottom: -3px; border-left: 2px dashed var(--zp-ink); }
    .zp-avg-label { position: absolute; bottom: 0; transform: translateX(-50%); font-size: 13px; font-weight: 700; white-space: nowrap; }
    .zp-bar-value { text-align: right; font-variant-numeric: tabular-nums; }
    .zp-legend { margin: 12px 0 0; font-size: 14px; color: #4F625B; }
    .zp-key { display: inline-block; width: 12px; height: 12px; border-radius: 3px; vertical-align: -1px; }
    .zp-key.zp-up { background: #C8541E; }
    .zp-key.zp-down { background: #1F6FAE; }
    .zp-key.zp-dash { width: 14px; height: 0; border-top: 2px dashed #0F2A22; border-radius: 0; vertical-align: 3px; }
    @media (max-width: 560px) {
        .zp-bar-row { grid-template-columns: minmax(92px, 40%) minmax(0, 1fr) 50px; gap: 6px; font-size: 13px; }
    }
</style>
