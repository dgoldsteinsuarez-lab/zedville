@extends('layouts.profile')

@section('title', __('money.enter_title'))

{{-- How money gets into the economy, for the advanced levels (Daniel, 2026-11-02:
     "en Avanzado, poner la opción de políticos irresponsables ... que en vez de bajar
     el gasto público, imprimen dinero creando inflación. Explica también cómo entra
     el dinero al mercado"). The doors - banknotes, loans, the government, bonds ("¿el
     dinero no entra al mercado a través de los bonos?"), printing to pay the bills - a
     picture of them, and what printing to pay the bills did. --}}
@section('content')
@include('education.money-factory._styles')
<div class="zm">
    <div class="zm-page-head">
        <a class="zm-crumb" href="{{ route('education.money-factory') }}">← {{ __('money.back_to_factory') }}</a>
    </div>
    <div class="zm-page-head">
        <h1>{{ __('money.enter_title') }}</h1>
        <span class="zm-badge">{{ __('money.advanced') }}</span>
    </div>
    <section class="zm-card"><p style="margin:0">{{ __('money.enter_intro') }}</p></section>

    <div class="zm-grid zm-grid--2">
        <section class="zm-card" aria-label="{{ __('money.enter_title') }}">
            {{-- the doors: notes, bond purchases and loans go down to people, the bonds' money across to the
                 government, taxes up and spending down, printing to pay the bills sideways --}}
            <svg viewBox="0 0 400 300" width="100%" role="img" aria-label="{{ __('money.enter_title') }}" style="display:block">
                <defs>
                    <marker id="zm-arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0,0 L10,5 L0,10 z" fill="#44564F"/></marker>
                    <marker id="zm-arrow-red" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0,0 L10,5 L0,10 z" fill="#e34948"/></marker>
                    <marker id="zm-arrow-blue" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0,0 L10,5 L0,10 z" fill="#2a78d6"/></marker>
                </defs>
                @foreach ([[20, 18, 'diagram_central', '#2a78d6'], [20, 128, 'diagram_bank', '#00A47D'], [20, 238, 'diagram_people', '#eda100'], [244, 128, 'diagram_state', '#6250d6']] as [$x, $y, $key, $colour])
                    <rect x="{{ $x }}" y="{{ $y }}" width="136" height="44" rx="10" fill="#fff" stroke="{{ $colour }}" stroke-width="2.5"/>
                    <text x="{{ $x + 68 }}" y="{{ $y + 27 }}" text-anchor="middle" font-size="13" font-weight="800" fill="#0F2A22">{{ __('money.' . $key) }}</text>
                @endforeach
                <line x1="50" y1="64" x2="50" y2="124" stroke="#44564F" stroke-width="2" marker-end="url(#zm-arrow)"/>
                <text x="58" y="104" font-size="12" fill="#33443E">{{ __('money.diagram_notes') }}</text>
                {{-- the central bank buys bonds from the banks with new money; the banks lend the government for its bonds --}}
                <line x1="132" y1="64" x2="132" y2="124" stroke="#2a78d6" stroke-width="2.5" marker-end="url(#zm-arrow-blue)"/>
                <text x="140" y="84" font-size="12" font-weight="700" fill="#1F5FAD">{{ __('money.diagram_bond_buying') }}</text>
                <line x1="158" y1="150" x2="240" y2="150" stroke="#2a78d6" stroke-width="2.5" marker-end="url(#zm-arrow-blue)"/>
                <text x="199" y="143" text-anchor="middle" font-size="12" font-weight="700" fill="#1F5FAD">{{ __('money.diagram_bonds') }}</text>
                <line x1="88" y1="174" x2="88" y2="234" stroke="#44564F" stroke-width="2" marker-end="url(#zm-arrow)"/>
                <text x="96" y="208" font-size="12" fill="#33443E">{{ __('money.diagram_loans') }}</text>
                {{-- taxes up to the government, spending back down --}}
                <path d="M156,250 C230,250 300,230 300,176" fill="none" stroke="#44564F" stroke-width="2" marker-end="url(#zm-arrow)"/>
                <text x="166" y="234" font-size="12" fill="#33443E">{{ __('money.diagram_taxes') }}</text>
                <path d="M330,176 C330,240 250,282 160,272" fill="none" stroke="#44564F" stroke-width="2" marker-end="url(#zm-arrow)"/>
                <text x="236" y="296" font-size="12" fill="#33443E">{{ __('money.diagram_spending') }}</text>
                {{-- printing to pay the bills: from the central bank to the government --}}
                <path d="M156,40 C250,40 312,60 312,124" fill="none" stroke="#e34948" stroke-width="2.5" stroke-dasharray="6 5" marker-end="url(#zm-arrow-red)"/>
                <text x="190" y="30" font-size="12" font-weight="700" fill="#B42318">{{ __('money.diagram_print') }}</text>
                <text x="298" y="104" text-anchor="end" font-size="12" font-weight="800" fill="#B42318">↑ {{ __('money.diagram_prices') }}</text>
            </svg>
        </section>

        <div class="zm" style="gap:12px">
            @foreach (['cash' => '🏦', 'loans' => '🤝', 'state' => '🏛️', 'bonds' => '📄', 'print' => '🖨️'] as $door => $icon)
                <section class="zm-card" @if ($door === 'print') style="border-color:#F3B9BC;background:#FFF8F7" @elseif ($door === 'bonds') style="border-color:#B8D3F3;background:#F6FAFF" @endif>
                    <h2><span class="zm-ico" aria-hidden="true">{{ $icon }}</span>{{ __('money.door_' . $door) }}</h2>
                    <p style="margin:0">{{ __('money.door_' . $door . '_text') }}</p>
                    {{-- bonds: who buys them decides whether new money is made (2026-11-02) --}}
                    @if ($door === 'bonds')
                        <ul class="zm-points" style="margin-top:10px">
                            <li><span aria-hidden="true">👪</span>{{ __('money.bonds_people') }}</li>
                            <li><span aria-hidden="true">🏦</span>{{ __('money.bonds_banks') }}</li>
                            <li><span aria-hidden="true">🏛️</span>{{ __('money.bonds_central') }}</li>
                        </ul>
                    @endif
                </section>
            @endforeach
        </div>
    </div>

    <section class="zm-card" style="border-top:5px solid #e34948">
        <h2><span class="zm-ico" aria-hidden="true">⚠️</span>{{ __('money.politicians_title') }}</h2>
        <p>{{ __('money.politicians_text') }}</p>
        <ul class="zm-points" style="margin-bottom:12px">
            <li><span aria-hidden="true">🕰️</span>{{ __('money.politicians_germany') }}</li>
            <li><span aria-hidden="true">💸</span>{{ __('money.politicians_zimbabwe') }}</li>
            <li><span aria-hidden="true">⚖️</span>{{ __('money.politicians_rule') }}</li>
        </ul>
        <p style="margin:0" class="zm-muted">{{ __('money.politicians_debate') }}</p>
    </section>

    <section class="zm-card">
        <h2 style="font-size:15px">{{ __('money.sources') }}</h2>
        <ul class="zm-sources">
            <li><a href="https://eur-lex.europa.eu/eli/treaty/tfeu_2016/art_123/oj" target="_blank" rel="noopener">Treaty on the Functioning of the European Union – Article 123</a></li>
            <li><a href="https://www.bankofengland.co.uk/quarterly-bulletin/2014/q1/money-creation-in-the-modern-economy" target="_blank" rel="noopener">Bank of England – Money creation in the modern economy (2014)</a></li>
            <li><a href="https://www.ecb.europa.eu/ecb-and-you/explainers/show-me/html/app_infographic.{{ in_array(app()->getLocale(), ['es', 'fr', 'de'], true) ? app()->getLocale() : 'en' }}.html" target="_blank" rel="noopener">{{ __('money.source_qe') }}</a></li>
            <li><a href="https://www.bankofengland.co.uk/monetary-policy/quantitative-easing" target="_blank" rel="noopener">Bank of England – Quantitative easing</a></li>
            <li><a href="https://www.ecb.europa.eu/mopo/implement/pepp/html/index.en.html" target="_blank" rel="noopener">European Central Bank – Pandemic emergency purchase programme (PEPP)</a></li>
            <li><a href="https://www.dhm.de/lemo/kapitel/weimarer-republik/innenpolitik/inflation-1923.html" target="_blank" rel="noopener">Deutsches Historisches Museum (LeMO) – Inflation 1923</a></li>
            <li><a href="https://www.cato.org/commentary/hyperinflation-much-talked-about-little-understood" target="_blank" rel="noopener">Steve H. Hanke (Cato Institute) – Hyperinflation: much talked about, little understood</a></li>
        </ul>
    </section>
</div>
@endsection
