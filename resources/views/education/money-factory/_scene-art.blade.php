{{-- The Money Factory's picture until the illustrated one is in public/images/money-factory/scene.*
     (2026-11-02): the same places as MoneyFactoryController::SPOTS - the exchange board top left,
     a giant coin, the factory printing notes, the banknote scanner, a market stall, the armoured
     truck, the Central Bank with its president, the vault. No words: the places are named on top,
     in the student's language. --}}
<svg class="zm-scene-art" viewBox="0 0 1600 900" role="img" aria-label="{{ __('money.scene_title') }}">
    <defs>
        <linearGradient id="zm-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#D7F0E6"/><stop offset="1" stop-color="#F7FCFA"/></linearGradient>
        <radialGradient id="zm-gold" cx=".4" cy=".35" r=".7"><stop offset="0" stop-color="#FDE68A"/><stop offset=".6" stop-color="#F6C453"/><stop offset="1" stop-color="#D69E2E"/></radialGradient>
    </defs>
    {{-- sky, sun, clouds, ground --}}
    <rect width="1600" height="900" fill="url(#zm-sky)"/>
    <circle cx="1470" cy="95" r="48" fill="#FDE68A" opacity=".85"/>
    <g fill="#fff" opacity=".9"><ellipse cx="560" cy="95" rx="70" ry="26"/><ellipse cx="610" cy="80" rx="50" ry="24"/><ellipse cx="1110" cy="120" rx="80" ry="24"/><ellipse cx="1160" cy="104" rx="46" ry="22"/></g>
    <rect y="640" width="1600" height="260" fill="#CFEBDD"/>
    <rect y="860" width="1600" height="40" fill="#BBDDCC"/>

    {{-- the currency exchange board on its kiosk --}}
    <rect x="170" y="300" width="14" height="150" fill="#44564F"/><rect x="356" y="300" width="14" height="150" fill="#44564F"/>
    <rect x="100" y="70" width="340" height="230" rx="16" fill="#0B231C" stroke="#16382E" stroke-width="4"/>
    <circle cx="128" cy="96" r="7" fill="#FF5252"/><rect x="144" y="90" width="120" height="12" rx="6" fill="#E6F6F0" opacity=".55"/>
    @foreach ([['#2a78d6', '#34D399'], ['#eda100', '#F87171'], ['#e34948', '#34D399'], ['#6250d6', '#34D399'], ['#1baf7a', '#F87171']] as $i => [$flag, $move])
        <circle cx="134" cy="{{ 128 + $i * 34 }}" r="11" fill="{{ $flag }}"/>
        <rect x="156" y="{{ 123 + $i * 34 }}" width="140" height="10" rx="5" fill="#CFE7DE" opacity=".45"/>
        <rect x="318" y="{{ 121 + $i * 34 }}" width="64" height="14" rx="4" fill="#FFFFFF" opacity=".85"/>
        <rect x="392" y="{{ 121 + $i * 34 }}" width="26" height="14" rx="4" fill="{{ $move }}"/>
    @endforeach
    <rect x="128" y="430" width="284" height="130" rx="12" fill="#00A47D"/><rect x="156" y="452" width="228" height="56" rx="8" fill="#E6F6F0"/>
    <rect x="128" y="420" width="284" height="18" rx="6" fill="#016950"/>
    {{-- a family at the board --}}
    <g><circle cx="470" cy="548" r="19" fill="#C68B59"/><path d="M451 540 q19 -26 38 0" fill="#2E2A26"/><rect x="449" y="568" width="42" height="64" rx="14" fill="#eb6834"/><rect x="455" y="628" width="12" height="16" fill="#33443E"/><rect x="473" y="628" width="12" height="16" fill="#33443E"/></g>
    <g><circle cx="523" cy="590" r="14" fill="#8D5524"/><rect x="507" y="605" width="32" height="38" rx="11" fill="#2a78d6"/><rect x="511" y="640" width="9" height="8" fill="#33443E"/><rect x="526" y="640" width="9" height="8" fill="#33443E"/></g>

    {{-- the giant coin --}}
    <ellipse cx="230" cy="848" rx="112" ry="14" fill="#0F2A22" opacity=".1"/>
    <circle cx="230" cy="732" r="106" fill="url(#zm-gold)" stroke="#B7791F" stroke-width="8"/>
    <circle cx="230" cy="732" r="80" fill="none" stroke="#D69E2E" stroke-width="4" stroke-dasharray="6 7"/>
    <text x="230" y="772" text-anchor="middle" font-size="112" font-weight="800" fill="#B7791F" font-family="Manrope, Arial, sans-serif">Ƶ</text>

    {{-- the Money Factory: a sawtooth roof, a chimney, a big window onto the printing press --}}
    <rect x="938" y="92" width="52" height="150" fill="#9C6B53"/>
    <g fill="#E5E7EB"><circle cx="966" cy="70" r="22"/><circle cx="994" cy="42" r="16"/><circle cx="1020" cy="22" r="11"/></g>
    <rect x="560" y="230" width="470" height="410" fill="#F6E7DC" stroke="#D9B9A3" stroke-width="4"/>
    <path d="M560 230 L640 160 L640 230 L720 160 L720 230 L800 160 L800 230 L880 160 L880 230 L960 160 L960 230 L1030 170 L1030 230 Z" fill="#0E7C5F"/>
    <rect x="598" y="288" width="394" height="212" rx="10" fill="#DCEFF8" stroke="#8AB4C9" stroke-width="5"/>
    <rect x="626" y="382" width="154" height="92" rx="10" fill="#6B7280"/>
    <circle cx="668" cy="382" r="19" fill="#9CA3AF"/><circle cx="738" cy="382" r="19" fill="#9CA3AF"/>
    <rect x="790" y="396" width="176" height="74" rx="4" fill="#D1FAE5" stroke="#0E7C5F" stroke-width="3"/>
    @for ($c = 0; $c < 4; $c++)
        @for ($r = 0; $r < 2; $r++)
            <rect x="{{ 798 + $c * 42 }}" y="{{ 404 + $r * 32 }}" width="36" height="26" rx="3" fill="#A7E3CD" stroke="#0E7C5F" stroke-width="1.5"/>
        @endfor
    @endfor
    <g><circle cx="700" cy="330" r="14" fill="#C68B59"/><rect x="687" y="314" width="26" height="9" rx="4" fill="#eda100"/><rect x="687" y="346" width="26" height="32" rx="8" fill="#2563EB"/></g>
    <g><circle cx="900" cy="330" r="14" fill="#F1C27D"/><rect x="887" y="314" width="26" height="9" rx="4" fill="#eda100"/><rect x="887" y="346" width="26" height="32" rx="8" fill="#2563EB"/></g>
    <rect x="700" y="516" width="210" height="22" rx="6" fill="#00A47D"/>
    <rect x="758" y="548" width="94" height="92" rx="6" fill="#0E7C5F"/><circle cx="838" cy="596" r="5" fill="#FDE68A"/>

    {{-- the banknote scanner and a girl scanning her note --}}
    <rect x="700" y="690" width="172" height="152" rx="16" fill="#1F2937"/>
    <rect x="720" y="706" width="132" height="60" rx="8" fill="#34D399" opacity=".85"/>
    <rect x="720" y="790" width="132" height="18" rx="5" fill="#9BE3C9"/>
    <rect x="746" y="770" width="80" height="30" rx="3" fill="#A7E3CD" stroke="#0E7C5F" stroke-width="2"/>
    <g><circle cx="912" cy="712" r="16" fill="#F1C27D"/><path d="M896 708 q16 -24 32 0 v14 h-6 v-10 h-20 v10 h-6 z" fill="#7C2D12"/><rect x="895" y="730" width="34" height="48" rx="11" fill="#1baf7a"/><rect x="899" y="776" width="10" height="20" fill="#33443E"/><rect x="915" y="776" width="10" height="20" fill="#33443E"/></g>

    {{-- the market stall: prices --}}
    <rect x="972" y="742" width="8" height="110" fill="#9A6400"/><rect x="1150" y="742" width="8" height="110" fill="#9A6400"/>
    @for ($s = 0; $s < 6; $s++)
        <path d="M{{ 960 + $s * 34 }} 712 h34 v34 q-17 14 -34 0 z" fill="{{ $s % 2 ? '#FFFFFF' : '#e34948' }}"/>
    @endfor
    <rect x="968" y="790" width="194" height="62" rx="8" fill="#FBBF24"/>
    @foreach ([998, 1052, 1106] as $i => $tx)
        <line x1="{{ $tx + 14 }}" y1="746" x2="{{ $tx + 14 }}" y2="760" stroke="#9A6400" stroke-width="2"/>
        <rect x="{{ $tx }}" y="760" width="30" height="20" rx="4" fill="#fff" stroke="#9A6400" stroke-width="2"/>
        <path d="M{{ $tx + 40 }} {{ 776 - $i * 2 }} l8 -10 l8 10 z" fill="#e34948"/>
    @endforeach

    {{-- the armoured truck taking the notes to the Central Bank --}}
    <rect x="1040" y="545" width="172" height="86" rx="12" fill="#4B5563"/>
    <rect x="1212" y="570" width="62" height="61" rx="10" fill="#374151"/><rect x="1222" y="578" width="42" height="24" rx="4" fill="#BFDBFE"/>
    <circle cx="1122" cy="588" r="23" fill="#FDE68A" stroke="#B7791F" stroke-width="3"/>
    <text x="1122" y="598" text-anchor="middle" font-size="28" font-weight="800" fill="#B7791F" font-family="Manrope, Arial, sans-serif">Ƶ</text>
    @foreach ([1080, 1180, 1244] as $wx)<circle cx="{{ $wx }}" cy="633" r="20" fill="#111827"/><circle cx="{{ $wx }}" cy="633" r="8" fill="#9CA3AF"/>@endforeach

    {{-- the Central Bank of Zedland --}}
    <rect x="1170" y="524" width="400" height="22" fill="#D1D5DB"/><rect x="1190" y="502" width="360" height="24" fill="#E5E7EB"/>
    <rect x="1210" y="272" width="320" height="230" fill="#F3F4F6" stroke="#D1D5DB" stroke-width="3"/>
    <path d="M1190 272 L1370 176 L1550 272 Z" fill="#E5E7EB" stroke="#D1D5DB" stroke-width="3"/>
    <circle cx="1370" cy="236" r="22" fill="url(#zm-gold)" stroke="#B7791F" stroke-width="3"/>
    @for ($c = 0; $c < 5; $c++)<rect x="{{ 1236 + $c * 62 }}" y="290" width="26" height="212" rx="4" fill="#FFFFFF" stroke="#D1D5DB" stroke-width="2"/>@endfor
    <rect x="1350" y="420" width="40" height="82" rx="4" fill="#2a78d6"/>
    {{-- its president, waving --}}
    <g>
        <ellipse cx="1342" cy="722" rx="40" ry="8" fill="#0F2A22" opacity=".1"/>
        <circle cx="1342" cy="586" r="21" fill="#E8B796"/>
        <path d="M1320 584 q22 -34 44 0 q-6 -12 -22 -12 q-16 0 -22 12 z" fill="#3F2D20"/><circle cx="1342" cy="558" r="10" fill="#3F2D20"/>
        <rect x="1316" y="609" width="52" height="74" rx="15" fill="#1E3A8A"/><path d="M1336 609 l6 14 l6 -14 z" fill="#fff"/>
        <path d="M1364 622 L1394 590" stroke="#1E3A8A" stroke-width="13" stroke-linecap="round"/><circle cx="1397" cy="585" r="8" fill="#E8B796"/>
        <rect x="1324" y="680" width="13" height="38" fill="#1F2937"/><rect x="1347" y="680" width="13" height="38" fill="#1F2937"/>
        <g><ellipse cx="1440" cy="540" rx="44" ry="28" fill="#fff" stroke="#D1D5DB" stroke-width="2"/><path d="M1412 556 l-12 18 l26 -10 z" fill="#fff"/>
            <circle cx="1424" cy="540" r="4" fill="#6B7280"/><circle cx="1440" cy="540" r="4" fill="#6B7280"/><circle cx="1456" cy="540" r="4" fill="#6B7280"/></g>
    </g>

    {{-- the vault --}}
    <circle cx="1482" cy="780" r="88" fill="#9CA3AF" stroke="#4B5563" stroke-width="10"/>
    <circle cx="1482" cy="780" r="52" fill="#D1D5DB" stroke="#6B7280" stroke-width="4"/>
    <g stroke="#4B5563" stroke-width="9" stroke-linecap="round"><line x1="1482" y1="742" x2="1482" y2="818"/><line x1="1449" y1="761" x2="1515" y2="799"/><line x1="1449" y1="799" x2="1515" y2="761"/></g>
    <circle cx="1482" cy="780" r="10" fill="#4B5563"/>
    @for ($b = 0; $b < 8; $b++)<circle cx="{{ 1482 + 72 * cos($b * M_PI / 4) }}" cy="{{ 780 + 72 * sin($b * M_PI / 4) }}" r="5" fill="#4B5563"/>@endfor
</svg>
