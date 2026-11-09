{{-- Real or fake banknote? The three checks of the euro (European Central Bank): feel the
     raised print, look against the light (watermark, security thread), tilt (hologram, the
     number that changes colour). A fake note lacks one of them. --}}
@php $colours = [10 => '#C2410C', 20 => '#1F5FAD', 50 => '#0E7C5F']; @endphp
<section class="zm-card" x-data="{ check: null }" aria-labelledby="zm-fake-sets">
    <h2 id="zm-fake-sets"><span class="zm-ico" aria-hidden="true">🔍</span>{{ __('money_tasks.fake.how') }}</h2>
    <div class="zm-checks" role="group" aria-label="{{ __('money_tasks.fake.how') }}">
        @foreach (['feel' => '✋', 'look' => '💡', 'tilt' => '🌈'] as $check => $icon)
            <button type="button" class="zm-check" :class="check === '{{ $check }}' && 'is-on'" :aria-pressed="check === '{{ $check }}' ? 'true' : 'false'"
                    @click="check = check === '{{ $check }}' ? null : '{{ $check }}'">
                <span aria-hidden="true">{{ $icon }}</span> {{ __('money_tasks.fake.checks.' . $check) }}
            </button>
        @endforeach
    </div>
    @foreach (['feel', 'look', 'tilt'] as $check)
        <p class="zm-check-text" x-show="check === '{{ $check }}'" x-cloak>{{ __('money_tasks.fake.check_texts.' . $check) }}</p>
    @endforeach

    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="zm-holo" x1="0" y1="0" x2="0.3" y2="1">
                <stop offset="0" stop-color="#C4B5FD"/><stop offset=".3" stop-color="#93C5FD"/><stop offset=".55" stop-color="#6EE7B7"/>
                <stop offset=".8" stop-color="#FDE68A"/><stop offset="1" stop-color="#F9A8D4"/>
            </linearGradient>
        </defs>
    </svg>

    @foreach ($stage['sets'] as $s => $set)
        @php $colour = $colours[$set['value']]; @endphp
        <h3 class="zm-set-title">{{ __('money_tasks.fake.set', ['n' => $s + 1]) }}</h3>
        <div class="zm-notes">
            @foreach ($set['notes'] as $n => $note)
                @php $letter = ['A', 'B', 'C'][$n]; @endphp
                <figure class="zm-fnote" :class="check === 'tilt' && 'is-tilted'">
                    <svg viewBox="0 0 240 124" role="img" aria-label="{{ __('money_tasks.fake.note', ['letter' => $letter]) }} · {{ $set['value'] }} Ƶ">
                        <rect x="2" y="2" width="236" height="120" rx="10" fill="#F7FBF9" stroke="{{ $colour }}" stroke-width="3"/>
                        <path d="M8,96 C58,62 104,118 150,84" fill="none" stroke="{{ $colour }}" stroke-opacity=".16" stroke-width="7"/>
                        <text x="68" y="28" font-size="12" font-weight="800" letter-spacing="1" fill="#0F2A22">ZEDLAND</text>
                        <circle cx="42" cy="56" r="23" fill="#fff" stroke="{{ $colour }}" stroke-width="2.5"/>
                        <text x="42" y="65" text-anchor="middle" font-size="25" font-weight="800" fill="{{ $colour }}">Ƶ</text>
                        <text x="230" y="32" text-anchor="end" font-size="25" font-weight="800" fill="#0F2A22">{{ $set['value'] }}</text>
                        {{-- the hologram: a silver stripe that shows the value when tilted --}}
                        <rect x="154" y="3" width="24" height="118" @if ($note['tilt']) :fill="check === 'tilt' ? 'url(#zm-holo)' : '#D5DBE0'" @else fill="#D5DBE0" @endif/>
                        @if ($note['tilt'])
                            <text x="166" y="70" text-anchor="middle" font-size="13" font-weight="800" fill="#0F2A22" x-show="check === 'tilt'">{{ $set['value'] }}</text>
                        @endif
                        {{-- the number that turns from emerald green to deep blue --}}
                        <text x="230" y="112" text-anchor="end" font-size="22" font-weight="800" @if ($note['tilt']) :fill="check === 'tilt' ? '#1E3A8A' : '#0E7C5F'" @else fill="#0E7C5F" @endif>{{ $set['value'] }}</text>
                        <text x="12" y="112" font-size="12" font-family="ui-monospace, Menlo, monospace" fill="#33443E">{{ $note['serial'] }}</text>
                        {{-- against the light: the watermark and the dark security thread --}}
                        <g x-show="check === 'look'">
                            <rect x="2" y="2" width="236" height="120" rx="10" fill="#FFF1B8" opacity=".5"/>
                            @if ($note['look'])
                                <text x="106" y="82" text-anchor="middle" font-size="46" font-weight="800" fill="#0F2A22" opacity=".2">Ƶ</text>
                                <line x1="143" y1="3" x2="143" y2="121" stroke="#1F2B27" stroke-width="3" opacity=".75"/>
                            @endif
                        </g>
                        {{-- touch: short raised lines on both edges --}}
                        @if ($note['feel'])
                            <g x-show="check === 'feel'" stroke-linecap="round">
                                @for ($i = 0; $i < 5; $i++)
                                    <line x1="7" y1="{{ 40 + $i * 12 }}" x2="17" y2="{{ 33 + $i * 12 }}" stroke="#0F2A22" stroke-width="3.5"/>
                                    <line x1="223" y1="{{ 50 + $i * 9 }}" x2="233" y2="{{ 43 + $i * 9 }}" stroke="#0F2A22" stroke-width="3.5"/>
                                @endfor
                            </g>
                        @endif
                    </svg>
                    <figcaption>{{ __('money_tasks.fake.note', ['letter' => $letter]) }}</figcaption>
                </figure>
            @endforeach
        </div>
    @endforeach
</section>
