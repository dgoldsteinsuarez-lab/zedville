{{-- The Mayor of Zedville: a friendly illustrated character (navy suit,
     green sash with the city's gold medal) in the soft 3D style of the
     city renders. $class sizes it. --}}
<svg class="{{ $class ?? '' }}" viewBox="0 0 220 300" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ __('welcome.mayor.who') }}">
    <defs>
        <linearGradient id="zwSuit" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2B3E63"/><stop offset="1" stop-color="#1A2742"/></linearGradient>
        <linearGradient id="zwSkin" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#F6C9A4"/><stop offset="1" stop-color="#E9AE86"/></linearGradient>
        <linearGradient id="zwSash" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#00C493"/><stop offset="1" stop-color="#00805F"/></linearGradient>
        <radialGradient id="zwMedal" cx=".35" cy=".35" r=".7"><stop offset="0" stop-color="#FFE9A3"/><stop offset=".6" stop-color="#E7B53C"/><stop offset="1" stop-color="#B9861C"/></radialGradient>
    </defs>
    <ellipse cx="110" cy="290" rx="70" ry="8" fill="#000" opacity=".18"/>
    <!-- body -->
    <path d="M42 290 C40 220 58 182 110 178 C162 182 180 220 178 290 Z" fill="url(#zwSuit)"/>
    <path d="M96 180 L110 214 L124 180 Z" fill="#fff"/>
    <path d="M106 186 L110 214 L114 186 L110 182 Z" fill="#C8102E"/>
    <!-- sash -->
    <path d="M62 196 L74 188 L170 270 L160 284 Z" fill="url(#zwSash)"/>
    <circle cx="128" cy="244" r="15" fill="url(#zwMedal)" stroke="#9C6F12" stroke-width="2"/>
    <text x="128" y="250" text-anchor="middle" font-family="Manrope, sans-serif" font-weight="800" font-size="16" fill="#7A5408">Z</text>
    <!-- arms -->
    <path d="M44 236 C40 214 50 196 64 192 L70 250 C60 256 48 252 44 236 Z" fill="#22345A"/>
    <path d="M176 236 C180 214 170 196 156 192 L150 250 C160 256 172 252 176 236 Z" fill="#22345A"/>
    <circle cx="58" cy="254" r="11" fill="url(#zwSkin)"/>
    <circle cx="162" cy="254" r="11" fill="url(#zwSkin)"/>
    <!-- neck + head -->
    <rect x="98" y="150" width="24" height="32" rx="10" fill="#E9AE86"/>
    <ellipse cx="110" cy="112" rx="50" ry="54" fill="url(#zwSkin)"/>
    <ellipse cx="60" cy="116" rx="9" ry="13" fill="#E9AE86"/>
    <ellipse cx="160" cy="116" rx="9" ry="13" fill="#E9AE86"/>
    <!-- hair -->
    <path d="M60 104 C56 64 84 50 112 52 C142 52 166 70 160 106 C152 86 136 78 112 78 C88 78 70 84 60 104 Z" fill="#8A8F98"/>
    <path d="M62 100 C60 88 64 80 70 76 C68 88 68 96 66 106 Z" fill="#6F757F"/>
    <!-- glasses -->
    <circle cx="90" cy="114" r="15" fill="#fff" fill-opacity=".25" stroke="#2B3E63" stroke-width="3"/>
    <circle cx="130" cy="114" r="15" fill="#fff" fill-opacity=".25" stroke="#2B3E63" stroke-width="3"/>
    <path d="M105 114 L115 114" stroke="#2B3E63" stroke-width="3"/>
    <!-- eyes -->
    <circle cx="90" cy="116" r="4" fill="#2A2320"/>
    <circle cx="130" cy="116" r="4" fill="#2A2320"/>
    <circle cx="91.5" cy="114.5" r="1.3" fill="#fff"/>
    <circle cx="131.5" cy="114.5" r="1.3" fill="#fff"/>
    <!-- brows, moustache, smile -->
    <path d="M78 96 Q90 90 102 96" stroke="#6F757F" stroke-width="4" fill="none" stroke-linecap="round"/>
    <path d="M118 96 Q130 90 142 96" stroke="#6F757F" stroke-width="4" fill="none" stroke-linecap="round"/>
    <path d="M92 140 Q110 132 128 140 Q120 146 110 142 Q100 146 92 140 Z" fill="#8A8F98"/>
    <path d="M96 148 Q110 160 124 148" stroke="#A2492F" stroke-width="3.5" fill="none" stroke-linecap="round"/>
    <circle cx="78" cy="134" r="7" fill="#F29C8A" opacity=".45"/>
    <circle cx="142" cy="134" r="7" fill="#F29C8A" opacity=".45"/>
</svg>
