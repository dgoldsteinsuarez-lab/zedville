@extends('layouts.profile')

@section('title', __('money.supply_title'))

{{-- The money supply, for the advanced levels (Daniel, 2026-11-02: "con una
     opción de click, se puede explicar más al respecto, para los niveles
     avanzados, los tipos de dinero que hay ... M1, M2, M3, pero esto en una
     sección aparte"). The ECB's definitions, and the euro area's real money
     in September 2026 (ECB press release of 27 October 2026, EUR billions). --}}
@section('content')
@include('education.money-factory._styles')
@php
    // [key, EUR billions, colour] - each ring adds one part
    $parts = [
        ['cash', 1618, '#2a78d6'],
        ['overnight', 9715, '#eb6834'],
        ['short', 5158, '#1baf7a'],
        ['market', 1225, '#eda100'],
    ];
    // the ECB's own totals (its parts are rounded, so they add up to one less)
    [$m1, $m2, $m3] = [11334, 16492, 17717];
    $n = fn ($value) => \App\Support\Money::number($value, 0);
@endphp
<div class="zm">
    <div class="zm-page-head">
        <a class="zm-crumb" href="{{ route('education.money-factory') }}">← {{ __('money.back_to_factory') }}</a>
    </div>
    <div class="zm-page-head">
        <h1>{{ __('money.supply_title') }}</h1>
        <span class="zm-badge">{{ __('money.advanced') }}</span>
    </div>

    <section class="zm-card">
        <p style="margin:0">{{ __('money.supply_intro') }}</p>
    </section>

    <div class="zm-grid zm-grid--2">
        {{-- the rings, inside out: each adds one part --}}
        <section class="zm-card" aria-label="{{ __('money.supply_title') }}">
            <div class="zm-ring" style="--ring:#eda100">
                <p class="zm-ring-name">{{ __('money.ring_m3') }}</p>
                <p class="zm-ring-text">{{ __('money.ring_m3_text') }}</p>
                <div class="zm-ring" style="--ring:#1baf7a">
                    <p class="zm-ring-name">{{ __('money.ring_m2') }}</p>
                    <p class="zm-ring-text">{{ __('money.ring_m2_text') }}</p>
                    <div class="zm-ring" style="--ring:#eb6834">
                        <p class="zm-ring-name">{{ __('money.ring_m1') }}</p>
                        <p class="zm-ring-text">{{ __('money.ring_m1_text') }}</p>
                        <div class="zm-ring" style="--ring:#2a78d6">
                            <p class="zm-ring-name">{{ __('money.ring_cash') }}</p>
                            <p class="zm-ring-text">{{ __('money.ring_cash_text') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="zm" style="gap:16px">
            <section class="zm-card" x-data="{ tip: null }">
                <h2>{{ __('money.supply_chart_title') }}</h2>
                <p class="zm-small" style="margin:-6px 0 12px">{{ __('money.supply_chart_sub') }}</p>
                <p style="font-weight:700;color:#0F2A22">{{ __('money.supply_share', ['cash' => $n(1618), 'm3' => $n($m3)]) }}</p>

                {{-- M1, M2, M3: brackets over the bar --}}
                <div class="zm-brackets" aria-hidden="true">
                    @foreach ([['M1', $m1], ['M2', $m2], ['M3', $m3]] as [$name, $value])
                        <div class="zm-bracket" style="width:{{ round($value / $m3 * 100, 2) }}%"><span>{{ $name }} · {{ $n($value) }}</span></div>
                    @endforeach
                </div>
                <div class="zm-stack" role="img" aria-label="{{ __('money.supply_chart_title') }}">
                    @foreach ($parts as [$key, $value, $colour])
                        <span style="flex:{{ $value }};background:{{ $colour }}"
                              @mouseenter="tip = @js(__('money.seg_' . $key) . ': ' . $n($value) . ' ' . __('money.billions') . ' (' . \App\Support\Money::number($value / $m3 * 100, 1) . ' %)')"
                              @mouseleave="tip = null"></span>
                    @endforeach
                </div>
                <p class="zm-tip" x-show="tip" x-text="tip" x-cloak></p>
                <ul class="zm-legend">
                    @foreach ($parts as [$key, $value, $colour])
                        <li><span class="zm-sw" style="background:{{ $colour }}"></span>{{ __('money.seg_' . $key) }}<b>{{ $n($value) }}</b></li>
                    @endforeach
                </ul>
                <details class="zm-details">
                    <summary>{{ __('money.table_view') }}</summary>
                    <table class="zm-table">
                        <tbody>
                            @foreach ($parts as [$key, $value])
                                <tr><td>{{ __('money.seg_' . $key) }}</td><td class="num">{{ $n($value) }}</td></tr>
                            @endforeach
                            <tr><th>M1</th><th class="num">{{ $n($m1) }}</th></tr>
                            <tr><th>M2</th><th class="num">{{ $n($m2) }}</th></tr>
                            <tr><th>M3</th><th class="num">{{ $n($m3) }}</th></tr>
                        </tbody>
                    </table>
                </details>
            </section>

            <section class="zm-card">
                <h2><span class="zm-ico" aria-hidden="true">🏦</span>{{ __('money.banks_title') }}</h2>
                <p>{{ __('money.banks_text') }}</p>
            </section>
            <section class="zm-card">
                <h2><span class="zm-ico" aria-hidden="true">🔭</span>{{ __('money.watch_title') }}</h2>
                <p>{{ __('money.watch_text') }}</p>
            </section>
        </div>
    </div>

    <section class="zm-card">
        <h2 style="font-size:15px">{{ __('money.sources') }}</h2>
        <ul class="zm-sources">
            <li><a href="https://www.bde.es/f/webbe/GAP/Secciones/SalaPrensa/ComunicadosBCE/NotasInformativasBCE/26/presbce2026-144en.pdf" target="_blank" rel="noopener">ECB – Monetary developments in the euro area: September 2026 (27/10/2026)</a></li>
            <li><a href="https://www.ecb.europa.eu/stats/money_credit_banking/monetary_aggregates/html/index.en.html" target="_blank" rel="noopener">ECB – Monetary aggregates</a></li>
            <li><a href="https://www.bankofengland.co.uk/quarterly-bulletin/2014/q1/money-creation-in-the-modern-economy" target="_blank" rel="noopener">Bank of England – Money creation in the modern economy (2014)</a></li>
        </ul>
    </section>
</div>
<style>
    .zm-ring { border: 2px solid var(--ring); border-radius: 14px; padding: 10px 12px 12px; margin-top: 8px; background: color-mix(in srgb, var(--ring) 7%, #fff); }
    .zm-card > .zm-ring { margin-top: 0; }
    .zm-ring-name { margin: 0 0 2px !important; font: 800 15px/1.3 "Manrope", sans-serif; color: #0F2A22 !important; }
    .zm-ring-name::before { content: ""; display: inline-block; width: 10px; height: 10px; border-radius: 3px; background: var(--ring); margin-right: 8px; }
    .zm-ring-text { margin: 0 !important; font-size: 13px !important; line-height: 1.45 !important; color: #33443E !important; }
    .zm-brackets { display: flex; flex-direction: column; gap: 3px; margin-bottom: 6px; }
    .zm-bracket { border: 1.5px solid #8A9A95; border-bottom: 0; border-radius: 6px 6px 0 0; height: 19px; position: relative; }
    .zm-bracket span { position: absolute; left: 6px; top: -1px; font-size: 12px; font-weight: 800; color: #44564F; background: #fff; padding: 0 4px; line-height: 15px; white-space: nowrap; }
    .zm-stack { display: flex; gap: 2px; height: 34px; }
    .zm-stack span { display: block; min-width: 4px; cursor: default; }
    .zm-stack span:first-child { border-radius: 4px 0 0 4px; }
    .zm-stack span:last-child { border-radius: 0 4px 4px 0; }
    .zm-tip { margin: 8px 0 0 !important; font-size: 13px !important; font-weight: 700; color: #0F2A22 !important; }
    .zm-legend { list-style: none; margin: 12px 0 0; padding: 0; display: grid; grid-template-columns: 1fr; gap: 6px; font-size: 14px; color: #33443E; }
    .zm-legend li { display: flex; align-items: center; gap: 8px; }
    .zm-legend b { margin-left: auto; font-variant-numeric: tabular-nums; color: #0F2A22; }
    .zm-sw { width: 12px; height: 12px; border-radius: 3px; flex: none; }
</style>
@endsection
