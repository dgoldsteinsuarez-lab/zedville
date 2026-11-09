@extends('layouts.profile')

@section('title', __('money.circ_title'))

{{-- Too little or too much money (Daniel, 2026-11-02: "consecuencias de tener poco
     dinero o mucho dinero circulando, con opción de ver gráficos, pero todo esto en
     una sección aparte"). Where too much money often comes from - a government's
     debts paid with new money. The charts open on request: the euro area's inflation
     (Eurostat, annual average, 2015-2025) against the ECB's 2 % aim, and a simple
     model of Zedland - more money, the same things, higher prices. --}}
@section('content')
@include('education.money-factory._styles')
@php
    $inflation = [2015 => 0.2, 2016 => 0.2, 2017 => 1.5, 2018 => 1.8, 2019 => 1.2, 2020 => 0.3, 2021 => 2.6, 2022 => 8.4, 2023 => 5.4, 2024 => 2.4, 2025 => 2.1];
    $pct = fn ($value) => \App\Support\Money::number($value, 1) . (app()->getLocale() === 'en' ? '%' : "\u{00A0}%");
    // the chart's frame (SVG units, about the card's width so the text stays readable)
    [$left, $right, $top, $base, $max] = [26, 396, 16, 206, 9];
    $band = ($right - $left) / count($inflation);
    $y = fn ($value) => $base - $value / $max * ($base - $top);
@endphp
<div class="zm" x-data="{ charts: false, tip: null }">
    <div class="zm-page-head">
        <a class="zm-crumb" href="{{ route('education.money-factory') }}">← {{ __('money.back_to_factory') }}</a>
    </div>
    <div class="zm-page-head"><h1>{{ __('money.circ_title') }}</h1></div>

    <section class="zm-card"><p style="margin:0">{{ __('money.circ_intro') }}</p></section>

    <div class="zm-grid zm-grid--3">
        @foreach ([['little', '❄️', '#2a78d6'], ['right', '✅', '#00A47D'], ['much', '🔥', '#e34948']] as [$key, $icon, $colour])
            <section class="zm-card" style="border-top:5px solid {{ $colour }}">
                <h2><span class="zm-ico" aria-hidden="true">{{ $icon }}</span>{{ __('money.' . $key . '_title') }}</h2>
                <p style="margin:0">{{ __('money.' . $key . '_text') }}</p>
            </section>
        @endforeach
    </div>

    {{-- where too much money often comes from: a government's debts paid with new money, directly,
         indirectly or through inflation (Daniel, 2026-11-02: "¿no tienes que añadir también que se
         imprime dinero para pagar indirectamente las deudas del país?"). An explanation: not for
         hybrid classes. --}}
    @unless (\App\Support\Hybrid::on())
        <section class="zm-card" style="border-top:5px solid #B42318" aria-labelledby="zm-debts">
            <h2 id="zm-debts"><span class="zm-ico" aria-hidden="true">🖨️</span>{{ __('money.debts_title') }}</h2>
            <p>{{ __('money.debts_text') }}</p>
            <ul class="zm-points">
                <li><span aria-hidden="true">➡️</span>{{ __('money.debts_direct') }}</li>
                <li><span aria-hidden="true">🔄</span>{{ __('money.debts_indirect') }}</li>
                <li><span aria-hidden="true">📉</span>{{ __('money.debts_inflation') }}</li>
            </ul>
            {{-- the same debt, in bread, before and after prices double --}}
            <div class="zm-debt">
                <p class="zm-small" style="margin:0 0 6px;font-weight:700">{{ __('money.debts_example', ['debt' => zeds(100)]) }}</p>
                @foreach ([['debts_today', 20, '#00A47D'], ['debts_double', 10, '#e34948']] as [$label, $loaves, $colour])
                    <div class="zm-debt-row">
                        <span>{{ __('money.' . $label) }}</span>
                        <span class="zm-debt-bar"><span style="width:{{ $loaves * 5 }}%;background:{{ $colour }}"></span></span>
                        <b>🍞 {{ __('money.debts_loaves', ['count' => $loaves]) }}</b>
                    </div>
                @endforeach
            </div>
            <p class="zm-muted" style="margin:12px 0 0">🕰️ {{ __('money.debts_germany') }}</p>
        </section>
    @endunless

    <p class="zm-card" style="margin:0;background:#FFFBF2;border-color:#F3D9A6">💡 {{ __('money.did_you_know') }}</p>

    <div>
        <button type="button" class="zm-btn" style="width:auto;padding:10px 22px" @click="charts = !charts" :aria-expanded="charts">
            📊 <span x-text="charts ? @js(__('money.hide_charts')) : @js(__('money.show_charts'))"></span>
        </button>
    </div>

    <div class="zm-grid zm-grid--2" x-show="charts" x-cloak x-transition.opacity>
        <section class="zm-card">
            <h2>{{ __('money.chart_inflation_title') }}</h2>
            <p class="zm-small" style="margin:-6px 0 4px">{{ __('money.chart_inflation_sub') }}</p>
            <p class="zm-tip" style="min-height:20px" x-text="tip"></p>
            <svg viewBox="0 0 400 232" width="100%" role="img" aria-label="{{ __('money.chart_inflation_title') }}" style="display:block;overflow:visible">
                @foreach ([0, 2, 4, 6, 8] as $tick)
                    <line x1="{{ $left }}" x2="{{ $right }}" y1="{{ $y($tick) }}" y2="{{ $y($tick) }}" stroke="#E6ECEA" stroke-width="1"/>
                    <text x="{{ $left - 8 }}" y="{{ $y($tick) + 4 }}" text-anchor="end" font-size="12" fill="#5E7069">{{ $tick }}</text>
                @endforeach
                @foreach (array_values($inflation) as $i => $value)
                    @php
                        $year = array_keys($inflation)[$i];
                        $w = 20; $x = $left + $i * $band + ($band - $w) / 2; $h = $base - $y($value); $r = min(4, $h);
                    @endphp
                    <g @mouseenter="tip = @js($year . ': ' . $pct($value))" @mouseleave="tip = null">
                        <rect x="{{ $left + $i * $band }}" y="{{ $top }}" width="{{ $band }}" height="{{ $base - $top + 24 }}" fill="transparent"/>
                        <path d="M{{ $x }},{{ $base }} V{{ $base - $h + $r }} Q{{ $x }},{{ $base - $h }} {{ $x + $r }},{{ $base - $h }} H{{ $x + $w - $r }} Q{{ $x + $w }},{{ $base - $h }} {{ $x + $w }},{{ $base - $h + $r }} V{{ $base }} Z" fill="#2a78d6"/>
                        <text x="{{ $x + $w / 2 }}" y="{{ $base + 18 }}" text-anchor="middle" font-size="12" fill="#5E7069">{{ $year }}</text>
                        @if (in_array($year, [2022, 2025], true))
                            <text x="{{ $x + $w / 2 }}" y="{{ $base - $h - 6 }}" text-anchor="middle" font-size="12" font-weight="700" fill="#0F2A22">{{ $pct($value) }}</text>
                        @endif
                    </g>
                @endforeach
                <line x1="{{ $left }}" x2="{{ $right }}" y1="{{ $y(2) }}" y2="{{ $y(2) }}" stroke="#52514e" stroke-width="2" stroke-dasharray="6 5"/>
                {{-- on the left, over the low years, so it doesn't sit on a bar --}}
                <text x="{{ $left + 4 }}" y="{{ $y(2) - 6 }}" font-size="12" font-weight="700" fill="#33443E">{{ __('money.chart_target') }}</text>
                <line x1="{{ $left }}" x2="{{ $right }}" y1="{{ $base }}" y2="{{ $base }}" stroke="#8A9A95" stroke-width="1"/>
            </svg>
            <details class="zm-details">
                <summary>{{ __('money.table_view') }}</summary>
                <table class="zm-table">
                    <thead><tr><th>{{ __('money.year') }}</th><th class="num">{{ __('money.inflation') }}</th></tr></thead>
                    <tbody>
                        @foreach ($inflation as $year => $value)
                            <tr><td>{{ $year }}</td><td class="num">{{ $pct($value) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        </section>

        {{-- the model: the same things to buy, more or less money --}}
        <section class="zm-card" x-data="zmSim(@js([
            'today' => __('money.sim_today'),
            'after' => __('money.sim_after'),
            'pct' => app()->getLocale() === 'en' ? '%' : "\u{00A0}%",
            'money' => \App\Support\Money::style(),
            'states' => [
                'less' => ['#2a78d6', __('money.sim_less')],
                'same' => ['#00A47D', __('money.sim_same')],
                'more' => ['#eb6834', __('money.sim_more')],
                'hyper' => ['#e34948', __('money.sim_hyper')],
            ],
        ]))">
            <h2>{{ __('money.sim_title') }}</h2>
            <p>{{ __('money.sim_text') }}</p>
            <label for="zm-sim" class="zm-small" style="display:flex;justify-content:space-between;font-weight:700;color:#33443E">
                <span>{{ __('money.sim_money') }}</span><span x-text="label()"></span>
            </label>
            <input id="zm-sim" type="range" min="0" :max="steps.length - 1" step="1" x-model.number="step" style="width:100%;accent-color:#00A47D">
            <div style="margin-top:12px">
                <template x-for="bar in bars()" :key="bar.key">
                    <div class="zm-sim-row">
                        <span x-text="bar.name"></span>
                        <span class="zm-sim-track"><span :style="{ width: bar.width + '%', background: bar.colour }"></span></span>
                        <b x-text="zeds(bar.value)"></b>
                    </div>
                </template>
            </div>
            <p style="margin:8px 0 6px;font-weight:700;color:#0F2A22" x-text="cfg.states[state()][1]"></p>
            <p class="zm-small" style="margin:0">{{ __('money.sim_note') }}</p>
        </section>
    </div>

    <section class="zm-card">
        <h2 style="font-size:15px">{{ __('money.sources') }}</h2>
        <ul class="zm-sources">
            <li><a href="https://ec.europa.eu/eurostat/databrowser/view/prc_hicp_aind/default/table" target="_blank" rel="noopener">Eurostat – HICP, annual average rate of change</a></li>
            <li><a href="https://www.ecb.europa.eu/mopo/strategy/html/index.en.html" target="_blank" rel="noopener">ECB – Monetary policy strategy</a></li>
            <li><a href="https://www.dhm.de/lemo/kapitel/weimarer-republik/innenpolitik/inflation-1923.html" target="_blank" rel="noopener">Deutsches Historisches Museum (LeMO) – Inflation 1923</a></li>
            <li><a href="https://eur-lex.europa.eu/eli/treaty/tfeu_2016/art_123/oj" target="_blank" rel="noopener">Treaty on the Functioning of the European Union – Article 123</a></li>
            <li><a href="https://curia.europa.eu/jcms/upload/docs/application/pdf/2018-12/cp180192en.pdf" target="_blank" rel="noopener">Court of Justice of the EU – Press release 192/18, Weiss and Others (2018)</a></li>
            <li><a href="https://www.imf.org/en/publications/fm/issues/2023/04/03/fiscal-monitor-april-2023" target="_blank" rel="noopener">IMF – Fiscal Monitor, April 2023 (inflation and public debt)</a></li>
        </ul>
    </section>
</div>
<style>
    .zm-sim-row { display: grid; grid-template-columns: 70px 1fr 100px; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: #33443E; }
    .zm-sim-row b { text-align: right; font-weight: 800; color: #0F2A22; font-variant-numeric: tabular-nums; }
    .zm-sim-track { height: 22px; background: #EEF3F1; border-radius: 4px; overflow: hidden; }
    .zm-sim-track span { display: block; height: 100%; border-radius: 0 4px 4px 0; transition: width .3s, background .3s; }
    .zm-debt { margin-top: 12px; padding: 12px 14px; border-radius: 12px; background: #F7FAF9; }
    .zm-debt-row { display: grid; grid-template-columns: 90px 1fr auto; align-items: center; gap: 10px; margin-top: 6px; font-size: 14px; color: #33443E; }
    .zm-debt-row b { color: #0F2A22; white-space: nowrap; }
    .zm-debt-bar { height: 18px; border-radius: 6px; background: #E6ECEA; overflow: hidden; }
    .zm-debt-bar span { display: block; height: 100%; border-radius: 6px 4px 4px 6px; }
</style>
<script>
    // Zedland makes the same things; the Money Factory prints more or less money (a simple model: prices follow the money).
    window.zmSim = function (cfg) {
        return {
            cfg,
            basket: 50,
            step: 2,
            steps: [-50, -25, 0, 10, 25, 50, 100, 200, 500],
            change() { return this.steps[this.step]; },
            state() { const m = this.change(); return m < 0 ? 'less' : (m === 0 ? 'same' : (m <= 100 ? 'more' : 'hyper')); },
            label() { const m = this.change(); return (m > 0 ? '+' : '') + m + cfg.pct; },
            bars() {
                const after = this.basket * (1 + this.change() / 100), top = Math.max(this.basket, after);
                return [
                    { key: 'today', name: cfg.today, value: this.basket, width: this.basket / top * 100, colour: '#8A9A95' },
                    { key: 'after', name: cfg.after, value: after, width: after / top * 100, colour: cfg.states[this.state()][0] },
                ];
            },
            zeds(value) {
                const [whole, cents] = value.toFixed(2).split('.');
                const number = whole.replace(/\B(?=(\d{3})+(?!\d))/g, cfg.money.thou) + cfg.money.dec + cents;
                return cfg.money.before ? 'Ƶ ' + number : number + ' Ƶ';
            },
        };
    };
</script>
@endsection
