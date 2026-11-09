{{-- The badge medals (Daniel, 2026-11-02: "crees que puedes mejorar el diseño de las insignias?"), drawn once
     per page and used as <svg viewBox="0 0 120 130"><use href="#m-gold"/></svg>. Engagement: a medal with the
     Zedville Z, one star more each level (Bronze 1 to Platinum 4), beads from Silver, laurels from Gold, a
     diamond and a glow for Platinum. FinHero: a shield that gets grander - Rookie a sprout growing from a coin,
     FinHero the Z coin, Champion a winged trophy, Legend a crowned, winged star. #m-eng-none and #m-fh-none
     are the empty places. Generated, then kept as it is: no text inside, so it looks the same everywhere. --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <defs>
    <path id="zvb-z" d="M11 31.04V30.512L22.36 11.616H11.8V8H29.032V8.544L17.736 27.408H28.76V31.04H11Z"/>
    <path id="zvb-star" d="M0 -5.4 L1.4 -1.9 L5.1 -1.7 L2.2 0.7 L3.2 4.4 L0 2.3 L-3.2 4.4 L-2.2 0.7 L-5.1 -1.7 L-1.4 -1.9 Z"/>
    <path id="zvb-sparkle" d="M0 -7 L1.6 -1.6 L7 0 L1.6 1.6 L0 7 L-1.6 1.6 L-7 0 L-1.6 -1.6 Z" stroke-width=".8" stroke-linejoin="round"/>
    <path id="zvb-leaf" d="M0 0 C3 -3.6 7 -3.8 10.5 0 C7 3.8 3 3.6 0 0 Z"/>
    <path id="zvb-wing" d="M29 45 C19 35 9 31 1.5 33 C5.5 41.5 14 49 28 56.5 Z M29 56.5 C18 51 8.5 50 2.5 53.5 C7.5 61.5 16 66 28.5 68.5 Z M29.5 68.5 C20 66.5 12 67.5 7 72.5 C12 78.5 20 80.5 30 79.5 Z"/>
    <linearGradient id="zvb-ribbon" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#16C394"/><stop offset="1" stop-color="#017A5C"/></linearGradient>
    <linearGradient id="zvb-ribbon-dark" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#0FA57E"/><stop offset="1" stop-color="#015C45"/></linearGradient>
    <linearGradient id="zvb-leaf-fill" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#2FA36F"/><stop offset="1" stop-color="#6FD39F"/></linearGradient>
    <radialGradient id="zvb-coin" cx="35%" cy="30%" r="80%"><stop offset="0" stop-color="#FFF4B8"/><stop offset=".55" stop-color="#F7C948"/><stop offset="1" stop-color="#C08A1E"/></radialGradient>
    <linearGradient id="zvb-gem" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#CFFAFE"/><stop offset=".5" stop-color="#67E8F9"/><stop offset="1" stop-color="#3B82F6"/></linearGradient>
    <linearGradient id="zvb-sheen" x1="0" y1="0" x2="1" y2="1"><stop offset=".2" stop-color="#F9A8D4" stop-opacity="0"/><stop offset=".42" stop-color="#F9A8D4" stop-opacity=".28"/><stop offset=".6" stop-color="#A5F3FC" stop-opacity=".32"/><stop offset=".8" stop-color="#C4B5FD" stop-opacity=".28"/><stop offset="1" stop-color="#C4B5FD" stop-opacity="0"/></linearGradient>
    <radialGradient id="zvb-glow-platinum" cx="50%" cy="50%" r="50%"><stop offset=".45" stop-color="#C7D2FE" stop-opacity=".9"/><stop offset="1" stop-color="#C7D2FE" stop-opacity="0"/></radialGradient>
    <radialGradient id="zvb-glow-gold" cx="50%" cy="50%" r="50%"><stop offset=".35" stop-color="#FDE68A" stop-opacity=".95"/><stop offset="1" stop-color="#FDE68A" stop-opacity="0"/></radialGradient>
    <linearGradient id="zvb-wing-silver" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#F4F8FF"/><stop offset="1" stop-color="#93B4EE"/></linearGradient>
    <linearGradient id="zvb-wing-gold" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#FFF4B8"/><stop offset="1" stop-color="#E9B23A"/></linearGradient>
    <radialGradient id="zvb-bronze-face" cx="38%" cy="30%" r="80%"><stop offset="0" stop-color="#FBD9B8"/><stop offset=".55" stop-color="#D08C58"/><stop offset="1" stop-color="#8E522B"/></radialGradient>
    <linearGradient id="zvb-bronze-rim" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#F0B583"/><stop offset="1" stop-color="#6B3A18"/></linearGradient>
    <radialGradient id="zvb-silver-face" cx="38%" cy="30%" r="80%"><stop offset="0" stop-color="#FFFFFF"/><stop offset=".55" stop-color="#D3D9E0"/><stop offset="1" stop-color="#8C96A2"/></radialGradient>
    <linearGradient id="zvb-silver-rim" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#F4F6F8"/><stop offset="1" stop-color="#66707C"/></linearGradient>
    <radialGradient id="zvb-gold-face" cx="38%" cy="30%" r="80%"><stop offset="0" stop-color="#FFF4B8"/><stop offset=".55" stop-color="#F7C948"/><stop offset="1" stop-color="#B7811A"/></radialGradient>
    <linearGradient id="zvb-gold-rim" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FFE485"/><stop offset="1" stop-color="#94630C"/></linearGradient>
    <radialGradient id="zvb-platinum-face" cx="38%" cy="30%" r="80%"><stop offset="0" stop-color="#FFFFFF"/><stop offset=".55" stop-color="#DCE5FA"/><stop offset="1" stop-color="#8E9CD8"/></radialGradient>
    <linearGradient id="zvb-platinum-rim" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#F3F6FF"/><stop offset="1" stop-color="#5E6BB0"/></linearGradient>
    <linearGradient id="zvb-rookie-rim" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#C9D2D9"/><stop offset="1" stop-color="#5F6D78"/></linearGradient>
    <linearGradient id="zvb-rookie-field" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#F4F7F9"/><stop offset="1" stop-color="#B9C5CE"/></linearGradient>
    <linearGradient id="zvb-finhero-rim" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#34D399"/><stop offset="1" stop-color="#04694F"/></linearGradient>
    <linearGradient id="zvb-finhero-field" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#E6FBF3"/><stop offset="1" stop-color="#7FDDB8"/></linearGradient>
    <linearGradient id="zvb-champion-rim" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#93C5FD"/><stop offset="1" stop-color="#1D4ED8"/></linearGradient>
    <linearGradient id="zvb-champion-field" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#E0EDFF"/><stop offset="1" stop-color="#6AA8F7"/></linearGradient>
    <linearGradient id="zvb-legend-rim" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FFE485"/><stop offset="1" stop-color="#A06A0E"/></linearGradient>
    <linearGradient id="zvb-legend-field" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7C3AED"/><stop offset="1" stop-color="#3B0764"/></linearGradient>
    <!-- Engagement: a Zedville medal; one star more each level, beads from Silver, laurels from Gold, a diamond for Platinum -->
    <symbol id="m-bronze" viewBox="0 0 120 130">
      <path d="M24 0 H48 L66 44 L46 49 Z" fill="url(#zvb-ribbon)"/>
      <path d="M33 0 H39 L58.5 45 L53 46.5 Z" fill="#D08C58"/>
      <path d="M96 0 H72 L54 44 L74 49 Z" fill="url(#zvb-ribbon-dark)"/>
      <path d="M87 0 H81 L61.5 45 L67 46.5 Z" fill="#D08C58" opacity=".85"/>
      <rect x="50" y="37" width="20" height="8" rx="3" fill="url(#zvb-bronze-rim)" stroke="#5E3216" stroke-opacity=".5"/>
      <circle cx="60" cy="82" r="40" fill="url(#zvb-bronze-rim)" stroke="#5E3216" stroke-opacity=".45" stroke-width="1"/>
      <circle cx="60" cy="82" r="36.8" fill="none" stroke="#5E3216" stroke-opacity=".25" stroke-width=".8"/>
      <circle cx="60" cy="82" r="33.5" fill="url(#zvb-bronze-face)" stroke="#5E3216" stroke-opacity=".5" stroke-width="1.2"/>
      <path d="M34.6 70.2 A28 28 0 0 1 50.4 55.7" fill="none" stroke="#FFFFFF" stroke-opacity=".7" stroke-width="3" stroke-linecap="round"/>
      <use href="#zvb-star" x="60.8" y="59.3" fill="#FFE7CF"/><use href="#zvb-star" x="60" y="58.5" fill="#5E3216"/>
      <use href="#zvb-z" transform="translate(60.9 89.9) scale(1.3) translate(-20.02 -19.52)" fill="#FFE7CF"/>
      <use href="#zvb-z" transform="translate(60 89) scale(1.3) translate(-20.02 -19.52)" fill="#5E3216"/>
    </symbol>
    <symbol id="m-silver" viewBox="0 0 120 130">
      <path d="M24 0 H48 L66 44 L46 49 Z" fill="url(#zvb-ribbon)"/>
      <path d="M33 0 H39 L58.5 45 L53 46.5 Z" fill="#D3D9E0"/>
      <path d="M96 0 H72 L54 44 L74 49 Z" fill="url(#zvb-ribbon-dark)"/>
      <path d="M87 0 H81 L61.5 45 L67 46.5 Z" fill="#D3D9E0" opacity=".85"/>
      <rect x="50" y="37" width="20" height="8" rx="3" fill="url(#zvb-silver-rim)" stroke="#47515D" stroke-opacity=".5"/>
      <circle cx="60" cy="82" r="40" fill="url(#zvb-silver-rim)" stroke="#47515D" stroke-opacity=".45" stroke-width="1"/>
      <circle cx="60" cy="82" r="36.8" fill="none" stroke="#FFFFFF" stroke-width="2.6" stroke-linecap="round" stroke-dasharray="0 7.707"/>
      <circle cx="60" cy="82" r="33.5" fill="url(#zvb-silver-face)" stroke="#47515D" stroke-opacity=".5" stroke-width="1.2"/>
      <path d="M34.6 70.2 A28 28 0 0 1 50.4 55.7" fill="none" stroke="#FFFFFF" stroke-opacity=".7" stroke-width="3" stroke-linecap="round"/>
      <use href="#zvb-star" x="53.9" y="60.3" fill="#FFFFFF"/><use href="#zvb-star" x="53.1" y="59.5" fill="#47515D"/><use href="#zvb-star" x="67.7" y="60.3" fill="#FFFFFF"/><use href="#zvb-star" x="66.9" y="59.5" fill="#47515D"/>
      <use href="#zvb-z" transform="translate(60.9 89.9) scale(1.3) translate(-20.02 -19.52)" fill="#FFFFFF"/>
      <use href="#zvb-z" transform="translate(60 89) scale(1.3) translate(-20.02 -19.52)" fill="#47515D"/>
    </symbol>
    <symbol id="m-gold" viewBox="0 0 120 130">
      <path d="M24 0 H48 L66 44 L46 49 Z" fill="url(#zvb-ribbon)"/>
      <path d="M33 0 H39 L58.5 45 L53 46.5 Z" fill="#F7C948"/>
      <path d="M96 0 H72 L54 44 L74 49 Z" fill="url(#zvb-ribbon-dark)"/>
      <path d="M87 0 H81 L61.5 45 L67 46.5 Z" fill="#F7C948" opacity=".85"/>
      <rect x="50" y="37" width="20" height="8" rx="3" fill="url(#zvb-gold-rim)" stroke="#6E4505" stroke-opacity=".5"/><g class="laurel"><path d="M41.9 122.7 A44.5 44.5 0 0 1 20.7 61.1" fill="none" stroke="#1C6E4C" stroke-width="1.3" stroke-linecap="round"/><path d="M78.1 122.7 A44.5 44.5 0 0 0 99.3 61.1" fill="none" stroke="#1C6E4C" stroke-width="1.3" stroke-linecap="round"/><g fill="url(#zvb-leaf-fill)" stroke="#1C6E4C" stroke-width=".7"><use href="#zvb-leaf" transform="translate(41.9 122.7) rotate(169)"/><use href="#zvb-leaf" transform="translate(41.9 122.7) rotate(-121)"/><use href="#zvb-leaf" transform="translate(31.6 116.3) rotate(-175.3)"/><use href="#zvb-leaf" transform="translate(31.6 116.3) rotate(-105.3)"/><use href="#zvb-leaf" transform="translate(23.4 107.3) rotate(-159.7)"/><use href="#zvb-leaf" transform="translate(23.4 107.3) rotate(-89.7)"/><use href="#zvb-leaf" transform="translate(17.9 96.5) rotate(-144)"/><use href="#zvb-leaf" transform="translate(17.9 96.5) rotate(-74)"/><use href="#zvb-leaf" transform="translate(15.6 84.6) rotate(-128.3)"/><use href="#zvb-leaf" transform="translate(15.6 84.6) rotate(-58.3)"/><use href="#zvb-leaf" transform="translate(16.5 72.5) rotate(-112.7)"/><use href="#zvb-leaf" transform="translate(16.5 72.5) rotate(-42.7)"/><use href="#zvb-leaf" transform="translate(20.7 61.1) rotate(-62)"/><use href="#zvb-leaf" transform="translate(78.1 122.7) rotate(11)"/><use href="#zvb-leaf" transform="translate(78.1 122.7) rotate(301)"/><use href="#zvb-leaf" transform="translate(88.4 116.3) rotate(355.3)"/><use href="#zvb-leaf" transform="translate(88.4 116.3) rotate(285.3)"/><use href="#zvb-leaf" transform="translate(96.6 107.3) rotate(339.7)"/><use href="#zvb-leaf" transform="translate(96.6 107.3) rotate(269.7)"/><use href="#zvb-leaf" transform="translate(102.1 96.5) rotate(324)"/><use href="#zvb-leaf" transform="translate(102.1 96.5) rotate(254)"/><use href="#zvb-leaf" transform="translate(104.4 84.6) rotate(308.3)"/><use href="#zvb-leaf" transform="translate(104.4 84.6) rotate(238.3)"/><use href="#zvb-leaf" transform="translate(103.5 72.5) rotate(292.7)"/><use href="#zvb-leaf" transform="translate(103.5 72.5) rotate(222.7)"/><use href="#zvb-leaf" transform="translate(99.3 61.1) rotate(242)"/></g></g>
      <circle cx="60" cy="82" r="40" fill="url(#zvb-gold-rim)" stroke="#6E4505" stroke-opacity=".45" stroke-width="1"/>
      <circle cx="60" cy="82" r="36.8" fill="none" stroke="#FFF8D6" stroke-width="2.6" stroke-linecap="round" stroke-dasharray="0 7.707"/>
      <circle cx="60" cy="82" r="33.5" fill="url(#zvb-gold-face)" stroke="#6E4505" stroke-opacity=".5" stroke-width="1.2"/>
      <path d="M34.6 70.2 A28 28 0 0 1 50.4 55.7" fill="none" stroke="#FFFFFF" stroke-opacity=".7" stroke-width="3" stroke-linecap="round"/>
      <use href="#zvb-star" x="49.1" y="62.4" fill="#FFF8D6"/><use href="#zvb-star" x="48.3" y="61.6" fill="#6E4505"/><use href="#zvb-star" x="60.8" y="59.3" fill="#FFF8D6"/><use href="#zvb-star" x="60" y="58.5" fill="#6E4505"/><use href="#zvb-star" x="72.6" y="62.4" fill="#FFF8D6"/><use href="#zvb-star" x="71.8" y="61.6" fill="#6E4505"/>
      <use href="#zvb-z" transform="translate(60.9 89.9) scale(1.3) translate(-20.02 -19.52)" fill="#FFF8D6"/>
      <use href="#zvb-z" transform="translate(60 89) scale(1.3) translate(-20.02 -19.52)" fill="#6E4505"/>
    </symbol>
    <symbol id="m-platinum" viewBox="0 0 120 130"><circle cx="60" cy="82" r="58" fill="url(#zvb-glow-platinum)"/>
      <path d="M24 0 H48 L66 44 L46 49 Z" fill="url(#zvb-ribbon)"/>
      <path d="M33 0 H39 L58.5 45 L53 46.5 Z" fill="#DCE5FA"/>
      <path d="M96 0 H72 L54 44 L74 49 Z" fill="url(#zvb-ribbon-dark)"/>
      <path d="M87 0 H81 L61.5 45 L67 46.5 Z" fill="#DCE5FA" opacity=".85"/>
      <rect x="50" y="37" width="20" height="8" rx="3" fill="url(#zvb-platinum-rim)" stroke="#36408A" stroke-opacity=".5"/><g class="laurel"><path d="M41.9 122.7 A44.5 44.5 0 0 1 20.7 61.1" fill="none" stroke="#1C6E4C" stroke-width="1.3" stroke-linecap="round"/><path d="M78.1 122.7 A44.5 44.5 0 0 0 99.3 61.1" fill="none" stroke="#1C6E4C" stroke-width="1.3" stroke-linecap="round"/><g fill="url(#zvb-leaf-fill)" stroke="#1C6E4C" stroke-width=".7"><use href="#zvb-leaf" transform="translate(41.9 122.7) rotate(169)"/><use href="#zvb-leaf" transform="translate(41.9 122.7) rotate(-121)"/><use href="#zvb-leaf" transform="translate(31.6 116.3) rotate(-175.3)"/><use href="#zvb-leaf" transform="translate(31.6 116.3) rotate(-105.3)"/><use href="#zvb-leaf" transform="translate(23.4 107.3) rotate(-159.7)"/><use href="#zvb-leaf" transform="translate(23.4 107.3) rotate(-89.7)"/><use href="#zvb-leaf" transform="translate(17.9 96.5) rotate(-144)"/><use href="#zvb-leaf" transform="translate(17.9 96.5) rotate(-74)"/><use href="#zvb-leaf" transform="translate(15.6 84.6) rotate(-128.3)"/><use href="#zvb-leaf" transform="translate(15.6 84.6) rotate(-58.3)"/><use href="#zvb-leaf" transform="translate(16.5 72.5) rotate(-112.7)"/><use href="#zvb-leaf" transform="translate(16.5 72.5) rotate(-42.7)"/><use href="#zvb-leaf" transform="translate(20.7 61.1) rotate(-62)"/><use href="#zvb-leaf" transform="translate(78.1 122.7) rotate(11)"/><use href="#zvb-leaf" transform="translate(78.1 122.7) rotate(301)"/><use href="#zvb-leaf" transform="translate(88.4 116.3) rotate(355.3)"/><use href="#zvb-leaf" transform="translate(88.4 116.3) rotate(285.3)"/><use href="#zvb-leaf" transform="translate(96.6 107.3) rotate(339.7)"/><use href="#zvb-leaf" transform="translate(96.6 107.3) rotate(269.7)"/><use href="#zvb-leaf" transform="translate(102.1 96.5) rotate(324)"/><use href="#zvb-leaf" transform="translate(102.1 96.5) rotate(254)"/><use href="#zvb-leaf" transform="translate(104.4 84.6) rotate(308.3)"/><use href="#zvb-leaf" transform="translate(104.4 84.6) rotate(238.3)"/><use href="#zvb-leaf" transform="translate(103.5 72.5) rotate(292.7)"/><use href="#zvb-leaf" transform="translate(103.5 72.5) rotate(222.7)"/><use href="#zvb-leaf" transform="translate(99.3 61.1) rotate(242)"/></g></g>
      <circle cx="60" cy="82" r="40" fill="url(#zvb-platinum-rim)" stroke="#36408A" stroke-opacity=".45" stroke-width="1"/>
      <circle cx="60" cy="82" r="36.8" fill="none" stroke="#FFFFFF" stroke-width="2.6" stroke-linecap="round" stroke-dasharray="0 7.707"/>
      <circle cx="60" cy="82" r="33.5" fill="url(#zvb-platinum-face)" stroke="#36408A" stroke-opacity=".5" stroke-width="1.2"/><circle cx="60" cy="82" r="33.5" fill="url(#zvb-sheen)"/>
      <path d="M34.6 70.2 A28 28 0 0 1 50.4 55.7" fill="none" stroke="#FFFFFF" stroke-opacity=".7" stroke-width="3" stroke-linecap="round"/>
      <use href="#zvb-star" x="44.2" y="66.2" fill="#FFFFFF"/><use href="#zvb-star" x="43.4" y="65.4" fill="#36408A"/><use href="#zvb-star" x="54.7" y="60.1" fill="#FFFFFF"/><use href="#zvb-star" x="53.9" y="59.3" fill="#36408A"/><use href="#zvb-star" x="66.9" y="60.1" fill="#FFFFFF"/><use href="#zvb-star" x="66.1" y="59.3" fill="#36408A"/><use href="#zvb-star" x="77.4" y="66.2" fill="#FFFFFF"/><use href="#zvb-star" x="76.6" y="65.4" fill="#36408A"/>
      <use href="#zvb-z" transform="translate(60.9 89.9) scale(1.3) translate(-20.02 -19.52)" fill="#FFFFFF"/>
      <use href="#zvb-z" transform="translate(60 89) scale(1.3) translate(-20.02 -19.52)" fill="#36408A"/>
      <path d="M51 36 L55.5 30 H64.5 L69 36 L60 48 Z" fill="url(#zvb-gem)" stroke="#1E3A8A" stroke-width="1" stroke-linejoin="round"/>
      <path d="M51 36 H69 M55.5 30 L57.5 36 L60 48 M64.5 30 L62.5 36 L60 48" fill="none" stroke="#1E3A8A" stroke-width=".6" opacity=".6"/>
      <path d="M55.5 30 H60 L57.5 36 H51 Z" fill="#FFFFFF" opacity=".55"/>
      <use href="#zvb-sparkle" x="15" y="46" fill="#FFFFFF" stroke="#A5B4FC"/>
      <use href="#zvb-sparkle" transform="translate(104 58) scale(.7)" fill="#FFFFFF" stroke="#A5B4FC"/>
      <use href="#zvb-sparkle" transform="translate(103 118) scale(.8)" fill="#FFFFFF" stroke="#A5B4FC"/>
    </symbol>
    <symbol id="m-eng-none" viewBox="0 0 120 130">
      <path d="M24 0 H48 L66 44 L46 49 Z M96 0 H72 L54 44 L74 49 Z" fill="#E8EEEC"/>
      <circle cx="60" cy="82" r="39" fill="#F3F7F6" stroke="#B9C7C2" stroke-width="1.6" stroke-dasharray="5 4"/>
      <use href="#zvb-z" transform="translate(60 84) scale(1.3) translate(-20.02 -19.52)" fill="#D6E0DC"/>
    </symbol>
    <!-- FinHero: a shield; a sprout from a coin, the Z coin, a winged trophy, a crowned and winged star -->
    <symbol id="m-rookie" viewBox="0 0 120 130">
      <path d="M60 26 L95 36 V64 C95 89 79 104 60 113 C41 104 25 89 25 64 V36 Z" fill="url(#zvb-rookie-rim)" stroke="#3E4A54" stroke-opacity=".55" stroke-width="1.2" stroke-linejoin="round"/>
      <path d="M60 32.5 L89 41 V64 C89 85 76 98 60 106 C44 98 31 85 31 64 V41 Z" fill="url(#zvb-rookie-field)" stroke="#3E4A54" stroke-opacity=".35" stroke-width="1"/>
      <path d="M60 32.5 L31 41 V64 C31 85 44 98 60 106 Z" fill="#FFFFFF" opacity=".22"/>
      <ellipse cx="60" cy="93.5" rx="15" ry="5.5" fill="#94630C"/>
      <ellipse cx="60" cy="90.5" rx="15" ry="5.5" fill="url(#zvb-coin)" stroke="#7A4E06" stroke-width="1"/>
      <ellipse cx="60" cy="90.5" rx="10" ry="3.2" fill="none" stroke="#B7811A" stroke-width=".8"/>
      <path d="M60 90 C60 82 59 74 60.5 63" fill="none" stroke="#15803D" stroke-width="3.2" stroke-linecap="round"/>
      <path d="M60 78 C51 78.5 45 73 43 64 C52 63 58.5 68 60 78 Z" fill="#22C55E" stroke="#15803D" stroke-width="1.1" stroke-linejoin="round"/>
      <path d="M60.5 69 C65 60 72 55.5 81 55 C79.5 64.5 72 70.5 60.5 69 Z" fill="#4ADE80" stroke="#15803D" stroke-width="1.1" stroke-linejoin="round"/>
      <path d="M58 75 C54 72 50 69 47 66 M63 66 C68 62 73 59 77 58" fill="none" stroke="#15803D" stroke-width=".8" opacity=".7"/>
    </symbol>
    <symbol id="m-finhero" viewBox="0 0 120 130">
      <path d="M60 26 L95 36 V64 C95 89 79 104 60 113 C41 104 25 89 25 64 V36 Z" fill="url(#zvb-finhero-rim)" stroke="#064E3B" stroke-opacity=".55" stroke-width="1.2" stroke-linejoin="round"/>
      <path d="M60 32.5 L89 41 V64 C89 85 76 98 60 106 C44 98 31 85 31 64 V41 Z" fill="url(#zvb-finhero-field)" stroke="#064E3B" stroke-opacity=".35" stroke-width="1"/>
      <path d="M60 32.5 L31 41 V64 C31 85 44 98 60 106 Z" fill="#FFFFFF" opacity=".22"/><circle cx="31.5" cy="40.5" r="1.8" fill="#ECFDF5"/><circle cx="88.5" cy="40.5" r="1.8" fill="#ECFDF5"/>
      <circle cx="60" cy="68" r="20" fill="url(#zvb-coin)" stroke="#7A4E06" stroke-width="1.3"/>
      <circle cx="60" cy="68" r="16.5" fill="none" stroke="#B7811A" stroke-width=".9" opacity=".8"/>
      <use href="#zvb-z" transform="translate(60.8 68.8) scale(1.05) translate(-20.02 -19.52)" fill="#FFF8D6"/>
      <use href="#zvb-z" transform="translate(60 68) scale(1.05) translate(-20.02 -19.52)" fill="#6E4505"/>
      <path d="M45 63 A16 16 0 0 1 55 53" fill="none" stroke="#FFFFFF" stroke-opacity=".75" stroke-width="2.2" stroke-linecap="round"/>
      <use href="#zvb-sparkle" transform="translate(40 94) scale(.65)" fill="#FFFFFF" stroke="#047857"/>
      <use href="#zvb-sparkle" transform="translate(81 92) scale(.5)" fill="#FFFFFF" stroke="#047857"/>
    </symbol>
    <symbol id="m-champion" viewBox="0 0 120 130">
      <g fill="url(#zvb-wing-silver)" stroke="#2F4F9E" stroke-width=".9" stroke-linejoin="round">
        <use href="#zvb-wing"/><use href="#zvb-wing" transform="matrix(-1 0 0 1 120 0)"/>
      </g>
      <path d="M60 26 L95 36 V64 C95 89 79 104 60 113 C41 104 25 89 25 64 V36 Z" fill="url(#zvb-champion-rim)" stroke="#1E3A8A" stroke-opacity=".55" stroke-width="1.2" stroke-linejoin="round"/>
      <path d="M60 32.5 L89 41 V64 C89 85 76 98 60 106 C44 98 31 85 31 64 V41 Z" fill="url(#zvb-champion-field)" stroke="#1E3A8A" stroke-opacity=".35" stroke-width="1"/>
      <path d="M60 32.5 L31 41 V64 C31 85 44 98 60 106 Z" fill="#FFFFFF" opacity=".22"/><circle cx="31.5" cy="40.5" r="1.8" fill="#EFF6FF"/><circle cx="88.5" cy="40.5" r="1.8" fill="#EFF6FF"/>
      <g stroke="#7A4E06" stroke-width="1.2" stroke-linejoin="round">
        <path d="M47 54 H42.5 C39.5 54 38.5 57.5 39 60.5 C40 66.5 44.5 69 49 69.5 M73 54 H77.5 C80.5 54 81.5 57.5 81 60.5 C80 66.5 75.5 69 71 69.5" fill="none" stroke="#B7811A" stroke-width="3.2" stroke-linecap="round"/>
        <path d="M46.5 50 H73.5 V57 C73.5 69.5 67.5 76.5 60 78 C52.5 76.5 46.5 69.5 46.5 57 Z" fill="url(#zvb-coin)"/>
        <path d="M57 78 H63 L64.5 85 H55.5 Z" fill="#E0A82E"/>
        <path d="M50 85 H70 A2 2 0 0 1 72 87 V93 H48 V87 A2 2 0 0 1 50 85 Z" fill="#B7811A"/>
      </g>
      <use href="#zvb-star" transform="translate(60.6 63.6) scale(.95)" fill="#FFF8D6"/>
      <use href="#zvb-star" transform="translate(60 63) scale(.95)" fill="#9A6408"/>
      <path d="M50 55 C50.5 62 52.5 67 56 71" fill="none" stroke="#FFFFFF" stroke-opacity=".75" stroke-width="2" stroke-linecap="round"/>
    </symbol>
    <symbol id="m-legend" viewBox="0 0 120 130">
      <circle cx="60" cy="66" r="60" fill="url(#zvb-glow-gold)"/>
      <g fill="url(#zvb-wing-gold)" stroke="#7A4E06" stroke-width=".9" stroke-linejoin="round">
        <use href="#zvb-wing"/><use href="#zvb-wing" transform="matrix(-1 0 0 1 120 0)"/>
      </g>
      <path d="M60 26 L95 36 V64 C95 89 79 104 60 113 C41 104 25 89 25 64 V36 Z" fill="url(#zvb-legend-rim)" stroke="#4A2A00" stroke-opacity=".55" stroke-width="1.2" stroke-linejoin="round"/>
      <path d="M60 32.5 L89 41 V64 C89 85 76 98 60 106 C44 98 31 85 31 64 V41 Z" fill="url(#zvb-legend-field)" stroke="#4A2A00" stroke-opacity=".35" stroke-width="1"/>
      <path d="M60 32.5 L31 41 V64 C31 85 44 98 60 106 Z" fill="#FFFFFF" opacity=".1"/>
      <path d="M60 71 L54.9 64 L60 50 Z" fill="#FFE27A"/><path d="M60 71 L60 50 L65.1 64 Z" fill="#E0A82E"/><path d="M60 71 L65.1 64 L80 64.5 Z" fill="#FFE27A"/><path d="M60 71 L80 64.5 L68.2 73.7 Z" fill="#E0A82E"/><path d="M60 71 L68.2 73.7 L72.3 88 Z" fill="#FFE27A"/><path d="M60 71 L72.3 88 L60 79.6 Z" fill="#E0A82E"/><path d="M60 71 L60 79.6 L47.7 88 Z" fill="#FFE27A"/><path d="M60 71 L47.7 88 L51.8 73.7 Z" fill="#E0A82E"/><path d="M60 71 L51.8 73.7 L40 64.5 Z" fill="#FFE27A"/><path d="M60 71 L40 64.5 L54.9 64 Z" fill="#E0A82E"/><path d="M60 50 L65.1 64 L80 64.5 L68.2 73.7 L72.3 88 L60 79.6 L47.7 88 L51.8 73.7 L40 64.5 L54.9 64 Z" fill="none" stroke="#6E4505" stroke-width="1.2" stroke-linejoin="round"/>
      <circle cx="60" cy="71" r="3.4" fill="#F472B6" stroke="#6E4505" stroke-width=".8"/>
      <path d="M41 33 L38.5 14 L50 23 L60 8 L70 23 L81.5 14 L79 33 Z" fill="url(#zvb-coin)" stroke="#6E4505" stroke-width="1.2" stroke-linejoin="round"/>
      <rect x="40.5" y="29" width="39" height="6.5" rx="2" fill="#E0A82E" stroke="#6E4505" stroke-width="1.1"/>
      <circle cx="38.5" cy="13.5" r="2.8" fill="#EF4444" stroke="#6E4505" stroke-width=".8"/>
      <circle cx="60" cy="7.5" r="3.2" fill="#EF4444" stroke="#6E4505" stroke-width=".8"/>
      <circle cx="81.5" cy="13.5" r="2.8" fill="#EF4444" stroke="#6E4505" stroke-width=".8"/>
      <circle cx="60" cy="32.2" r="1.9" fill="#A78BFA"/><circle cx="49" cy="32.2" r="1.4" fill="#F472B6"/><circle cx="71" cy="32.2" r="1.4" fill="#F472B6"/>
      <use href="#zvb-sparkle" x="12" y="104" fill="#FFFFFF" stroke="#E0A82E"/>
      <use href="#zvb-sparkle" transform="translate(108 100) scale(.75)" fill="#FFFFFF" stroke="#E0A82E"/>
      <use href="#zvb-sparkle" transform="translate(104 24) scale(.6)" fill="#FFFFFF" stroke="#E0A82E"/>
    </symbol>
    <symbol id="m-fh-none" viewBox="0 0 120 130">
      <path d="M60 26 L95 36 V64 C95 89 79 104 60 113 C41 104 25 89 25 64 V36 Z" fill="#F3F7F6" stroke="#B9C7C2" stroke-width="1.6" stroke-dasharray="5 4" stroke-linejoin="round"/>
      <use href="#zvb-z" transform="translate(60 68) scale(1.2) translate(-20.02 -19.52)" fill="#D6E0DC"/>
    </symbol>
  </defs>
</svg>
