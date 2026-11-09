<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">
<style>
    /* ══════════════════════════════════════════
           ZEDVILLE DESIGN SYSTEM
        ══════════════════════════════════════════ */
    :root {
        --bg-page: #eef9f5;
        --bg-white: #ffffff;
        --teal: #1aaa8e;
        --teal-light: #e8f7f3;
        --teal-mid: #b6e8d8;
        --teal-dark: #158870;
        --navy: #1a1a2e;
        --red: #e03e3e;
        --border: #e4eae7;
        --text-secondary: #666666;
        --text-muted: #999999;
        --sidebar-w: 220px;
        --sidebar-coll: 64px;
        --radius-pill: 50px;
        --radius-card: 14px;
        --transition-sb: 0.26s cubic-bezier(0.4, 0, 0.2, 1);

        --q-HC-color: #d97706;
        --q-HC-bg: #fffbeb;
        --q-HC-border: #fde68a;

        --q-HU-color: #dc2626;
        --q-HU-bg: #fef2f2;
        --q-HU-border: #fecaca;

        --q-LC-color: #1aaa8e;
        --q-LC-bg: #eef9f5;
        --q-LC-border: #b6e8d8;

        --q-LU-color: #2563eb;
        --q-LU-bg: #eff6ff;
        --q-LU-border: #bfdbfe;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        background: var(--bg-page);
        color: var(--navy);
        min-height: 100vh;
        display: flex;
        font-size: 14px;
        line-height: 1.5;
    }

    .content {
        padding: 28px 28px 64px;
        max-width: 860px;
    }

    .page-heading {
        font-size: 22px;
        font-weight: 700;
        color: var(--navy);
        letter-spacing: -0.02em;
        margin-bottom: 4px;
    }

    .page-heading span {
        color: var(--teal);
    }

    .page-subheading {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 22px;
    }

    .tab-row {
        display: flex;
        gap: 6px;
        margin-bottom: 22px;
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-pill);
        padding: 4px;
        width: fit-content;
    }

    .tab-btn {
        padding: 7px 22px;
        border-radius: var(--radius-pill);
        border: none;
        background: transparent;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
        cursor: pointer;
        transition: background 0.18s, color 0.18s, box-shadow 0.18s;
    }

    .tab-btn.active {
        background: var(--teal);
        color: #fff;
        box-shadow: 0 2px 8px rgba(26, 170, 142, 0.25);
    }

    .tab-btn:hover:not(.active) {
        color: var(--navy);
        background: var(--bg-page);
    }

    .tab-page {
        display: none;
    }

    .tab-page.active {
        display: block;
    }

    .summary-card {
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        padding: 20px 24px;
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
        transition: background 0.3s, border-color 0.3s;
    }

    .summary-divider {
        width: 1px;
        height: 72px;
        background: var(--border);
        flex-shrink: 0;
    }

    .summary-col-left {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .summary-col-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }

    .summary-section-label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .month-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .month-nav {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .month-nav-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1.5px solid var(--border);
        background: var(--bg-white);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--text-muted);
        transition: background 0.15s, border-color 0.15s, color 0.15s;
    }

    .month-nav-btn:hover:not(:disabled) {
        background: var(--teal-light);
        border-color: var(--teal-mid);
        color: var(--teal);
    }

    .month-nav-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .month-nav-btn svg {
        width: 13px;
        height: 13px;
        stroke-width: 2.5;
    }

    .month-name-label {
        font-size: 15px;
        font-weight: 700;
        color: var(--navy);
        min-width: 148px;
        text-align: center;
        letter-spacing: -0.01em;
    }

    .current-badge {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        background: var(--teal-light);
        color: var(--teal);
        padding: 3px 10px;
        border-radius: var(--radius-pill);
        border: 1px solid var(--teal-mid);
    }

    .cal-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: var(--bg-white);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-pill);
        padding: 7px 16px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        color: var(--teal);
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s;
        margin-bottom: 16px;
    }

    .cal-toggle-btn:hover {
        background: var(--teal-light);
        border-color: var(--teal-mid);
    }

    .cal-toggle-btn svg {
        width: 12px;
        height: 12px;
        stroke-width: 2.5;
        transition: transform 0.22s ease;
    }

    .cal-toggle-btn.open svg {
        transform: rotate(180deg);
    }

    .cal-wrap {
        display: none;
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        padding: 20px;
        margin-bottom: 16px;
    }

    .cal-wrap.open {
        display: block;
    }

    .cal-month-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--navy);
        margin-bottom: 12px;
    }

    .cal-dow-row {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
        margin-bottom: 4px;
    }

    .cal-dow {
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--text-muted);
        padding: 4px 0;
    }

    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }

    .cal-cell {
        aspect-ratio: 1;
        border-radius: 9px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        position: relative;
        background: #f5f7f6;
        color: #ccc;
        transition: transform 0.12s;
        cursor: default;
    }

    .cal-cell.has-mood {
        cursor: pointer;
    }

    .cal-cell.has-mood:hover {
        transform: scale(1.06);
    }

    .cal-cell.is-today {
        box-shadow: 0 0 0 2px var(--teal);
    }

    .cal-dot {
        position: absolute;
        bottom: 3px;
        right: 3px;
        width: 4px;
        height: 4px;
        border-radius: 50%;
    }

    .cal-multi {
        position: absolute;
        top: 2px;
        left: 3px;
        font-size: 10px; /* a count in the day's corner, like the bell's */
        font-weight: 700;
        line-height: 1;
    }

    .cal-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid var(--border);
    }

    .cal-legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: var(--text-secondary);
    }

    .cal-legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .face-block {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }

    .face-block .face-label {
        font-size: 13px;
        font-weight: 600;
        text-align: center;
    }

    .face-block.sm .face-label {
        font-size: 12px;
    }

    .q-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-pill);
        border: 1px solid;
        letter-spacing: 0.03em;
    }

    .city-current-card {
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        padding: 20px 24px;
        margin-bottom: 20px;
    }

    .city-current-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .city-current-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--navy);
    }

    .city-current-sub {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .city-current-body {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .city-stats {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .city-stat-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .city-stat-label {
        font-size: 12px;
        color: var(--text-secondary);
        width: 120px;
        flex-shrink: 0;
    }

    .city-stat-track {
        flex: 1;
        height: 6px;
        background: var(--border);
        border-radius: 3px;
        overflow: hidden;
        max-width: 160px;
    }

    .city-stat-fill {
        height: 100%;
        border-radius: 3px;
    }

    .city-stat-val {
        font-size: 12px;
        font-weight: 700;
        color: var(--navy);
        width: 36px;
        text-align: right;
        flex-shrink: 0;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .section-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--navy);
    }

    .past-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .past-card {
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: box-shadow 0.18s, border-color 0.18s;
    }

    .past-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        border-color: var(--teal-mid);
    }

    .past-month-box {
        width: 48px;
        height: 52px;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        gap: 1px;
    }

    .past-month-abbr {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .past-month-year {
        font-size: 15px;
        font-weight: 700;
        line-height: 1.1;
    }

    .past-info {
        flex: 1;
        min-width: 0;
    }

    .past-mood-name {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .past-meta {
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.65;
    }

    .past-tag {
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: var(--radius-pill);
        border: 1px solid;
        text-align: center;
        line-height: 1.5;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .no-data-msg {
        font-size: 13px;
        color: var(--text-muted);
        padding: 20px 0;
        text-align: center;
    }

    ::-webkit-scrollbar {
        width: 5px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--teal-mid);
        border-radius: 3px;
    }

    @media (max-width: 768px) {
        .sidebar {
            display: none;
        }

        .main {
            margin-left: 0 !important;
        }

        .content {
            padding: 16px 16px 48px;
        }

        .summary-card {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .summary-divider {
            display: none;
        }

        .city-current-body {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 560px) {
        .past-card {
            flex-wrap: wrap;
        }
    }
</style>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="themeTabspills">
        <div class="w-full">
            <div class="main" id="mainArea">
                <div class="content">

                    <!-- Tab Row -->
                    <div class="tab-row">
                        <button class="tab-btn active" onclick="showTab('my', this)">😊 {{ __('mood.my_mood') }}</button>
                        <button class="tab-btn" onclick="showTab('city', this)">🏙️ {{ __('mood.city_mood') }}</button>
                    </div>

                    <!-- ── MY MOOD TAB ── -->
                    <div class="tab-page active" id="page-my">
                        <div class="summary-card" id="my-summary"></div>

                        <div class="month-bar">
                            <div class="month-nav">
                                <button class="month-nav-btn" onclick="changeMonth(-1)" title="{{ __('mood.previous_month') }}">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <div class="month-name-label" id="month-label"></div>
                                <button class="month-nav-btn" id="next-btn" onclick="changeMonth(1)" title="{{ __('mood.next_month') }}">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                            <div id="month-badge-wrap"></div>
                        </div>

                        <button class="cal-toggle-btn" id="cal-toggle-btn" onclick="toggleCal()">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                            {{ __('mood.view_calendar') }}
                        </button>
                        <div class="cal-wrap" id="cal-wrap"></div>
                    </div>

                    <!-- ── CITY MOOD TAB ── -->
                    <div class="tab-page" id="page-city">
                        <div id="city-current"></div>
                        <div class="section-header">
                            <div class="section-title">{{ __('mood.past_months') }}</div>
                        </div>
                        <div class="past-list" id="past-list"></div>
                    </div>

                </div><!-- /content -->
            </div><!-- /main -->
        </div>
    </div>
</div>
<script>
    (function () {
        // Texts in the student's language (lang/<language>/mood.php, layout.php).
        const MD_T = {{ \Illuminate\Support\Js::from(__('mood.js')) }};
        const MOOD_NAMES = {{ \Illuminate\Support\Js::from(__('layout.mood')) }};
        /* ══════════════════════════════════════════════════════════════════
           MOOD DEFINITIONS
        ══════════════════════════════════════════════════════════════════ */
        const MOODS = [
            { id: 'furious', label: MOOD_NAMES.furious, q: 'HU', e: 9, p: 2 },
            { id: 'angry', label: MOOD_NAMES.angry, q: 'HU', e: 8, p: 3 },
            { id: 'nervous', label: MOOD_NAMES.nervous, q: 'HU', e: 6, p: 3 },
            { id: 'worried', label: MOOD_NAMES.worried, q: 'HU', e: 6, p: 4 },
            { id: 'excited', label: MOOD_NAMES.excited, q: 'HC', e: 9, p: 8 },
            { id: 'ecstatic', label: MOOD_NAMES.ecstatic, q: 'HC', e: 10, p: 9 },
            { id: 'happy', label: MOOD_NAMES.happy, q: 'HC', e: 7, p: 8 },
            { id: 'joyful', label: MOOD_NAMES.joyful, q: 'HC', e: 7, p: 7 },
            { id: 'lonely', label: MOOD_NAMES.lonely, q: 'LU', e: 3, p: 2 },
            { id: 'sad', label: MOOD_NAMES.sad, q: 'LU', e: 2, p: 3 },
            { id: 'hopeless', label: MOOD_NAMES.hopeless, q: 'LU', e: 1, p: 1 },
            { id: 'disappointed', label: MOOD_NAMES.disappointed, q: 'LU', e: 2, p: 2 },
            { id: 'calm', label: MOOD_NAMES.calm, q: 'LC', e: 4, p: 8 },
            { id: 'serene', label: MOOD_NAMES.serene, q: 'LC', e: 3, p: 9 },
            { id: 'at_ease', label: MOOD_NAMES.at_ease, q: 'LC', e: 3, p: 7 },
            { id: 'content', label: MOOD_NAMES.content, q: 'LC', e: 4, p: 7 },
        ];

        const QS = {
            HU: { color: '#dc2626', bg: 'var(--q-HU-bg)', border: 'var(--q-HU-border)', l1: MD_T.high_energy, l2: MD_T.uncomfortable },
            HC: { color: '#d97706', bg: 'var(--q-HC-bg)', border: 'var(--q-HC-border)', l1: MD_T.high_energy, l2: MD_T.comfortable },
            LU: { color: '#2563eb', bg: 'var(--q-LU-bg)', border: 'var(--q-LU-border)', l1: MD_T.low_energy, l2: MD_T.uncomfortable },
            LC: { color: '#1aaa8e', bg: 'var(--q-LC-bg)', border: 'var(--q-LC-border)', l1: MD_T.low_energy, l2: MD_T.comfortable },
        };

        const MN = Array.from({ length: 12 }, (_, m) => new Date(2000, m, 1)
            .toLocaleString(document.documentElement.lang, { month: 'long' }).replace(/^./, c => c.toUpperCase()));
        const MN3 = MN.map(m => m.slice(0, 3).toUpperCase());

        /* ── PHP → JS: inject server-side data ── */

        /**
         * myLogs  : all mood_logs rows for the current authenticated user.
         * cityMonths: pre-aggregated city stats per year-month (all users).
         * todayMyLog: the most recent log entry for the current user today (or null).
         */
        const myLogs = @json($myLogs);           // [{date:'2026-03-29', mood:'happy', energy:8, pleasantness:9, loginCount:2}, ...]
        const cityMonths = @json($cityMonths);   // [{year:2026, month:3, avgEnergy:7.8, avgPleasantness:6.2, daysLogged:12, totalLogs:18}, ...]
        const todayMyLog = @json($todayMyLog);   // {mood:'happy', energy:8, pleasantness:9, loginCount:1} | null

        /* ══════════════════════════════════════════════════════════════════
           HELPERS
        ══════════════════════════════════════════════════════════════════ */
        function moodById(id) {
            return MOODS.find(m => m.id === id) || MOODS[0];
        }

        function closest(e, p) {
            let best = MOODS[0], dist = Infinity;
            MOODS.forEach(m => {
                const d = Math.sqrt((m.e - e) ** 2 + (m.p - p) ** 2);
                if (d < dist) { dist = d; best = m; }
            });
            return best;
        }

        function face(mood, size) {
            const q = QS[mood.q], s = `stroke="${q.color}"`;
            let eyes = '', mouth = '';
            if (['furious', 'angry'].includes(mood.id)) {
                eyes = `<line x1="20" y1="21" x2="26" y2="24" ${s} stroke-width="2" stroke-linecap="round"/><line x1="34" y1="24" x2="40" y2="21" ${s} stroke-width="2" stroke-linecap="round"/><circle cx="23" cy="25" r="2" fill="${q.color}"/><circle cx="37" cy="25" r="2" fill="${q.color}"/>`;
            } else if (['ecstatic', 'excited'].includes(mood.id)) {
                eyes = `<path d="M21 26 Q23 23 25 26" ${s} stroke-width="2" fill="none" stroke-linecap="round"/><path d="M35 26 Q37 23 39 26" ${s} stroke-width="2" fill="none" stroke-linecap="round"/>`;
            } else {
                eyes = `<circle cx="23" cy="26" r="2" fill="${q.color}"/><circle cx="37" cy="26" r="2" fill="${q.color}"/>`;
            }
            if (['happy', 'ecstatic', 'excited', 'joyful', 'calm', 'serene'].includes(mood.id)) {
                mouth = `<path d="M21 35 Q30 43 39 35" ${s} stroke-width="2.2" fill="none" stroke-linecap="round"/>`;
            } else if (['furious', 'angry', 'hopeless', 'lonely'].includes(mood.id)) {
                mouth = `<path d="M21 39 Q30 33 39 39" ${s} stroke-width="2.2" fill="none" stroke-linecap="round"/>`;
            } else if (mood.id === 'worried') {
                mouth = `<ellipse cx="30" cy="38" rx="5" ry="4" fill="none" ${s} stroke-width="2"/>`;
            } else {
                mouth = `<line x1="22" y1="38" x2="38" y2="38" ${s} stroke-width="2.2" stroke-linecap="round"/>`;
            }
            return `<svg width="${size}" height="${size}" viewBox="0 0 60 60"><circle cx="30" cy="30" r="22" fill="none" ${s} stroke-width="2.2"/>${eyes}${mouth}</svg>`;
        }

        function faceBlock(mood, size, extra = '') {
            const q = QS[mood.q];
            const labelSize = size < 48 ? 11 : 13;
            return `<div class="face-block${size < 48 ? ' sm' : ''}">
        ${face(mood, size)}
        <div class="face-label" style="color:${q.color};font-size:${labelSize}px">${mood.label}</div>
        ${extra ? `<div style="font-size:12px;color:var(--text-muted);margin-top:1px">${extra}</div>` : ''}
    </div>`;
        }

        function qBadge(q) {
            const qs = QS[q];
            return `<span class="q-badge" style="color:${qs.color};background:${qs.bg};border-color:${qs.color}44">${qs.l1} · ${qs.l2}</span>`;
        }

        /* Build a lookup map from myLogs: key = "YYYY-MM-DD" */
        function buildMyLogMap() {
            const map = {};
            myLogs.forEach(entry => {
                const dk = entry.date;
                if (!map[dk]) {
                    map[dk] = { mood: entry.mood, energy: +entry.energy, pleasantness: +entry.pleasantness, loginCount: 0 };
                }
                map[dk].loginCount = entry.loginCount || 1;
            });
            return map;
        }

        const myLogMap = buildMyLogMap();

        /* ══════════════════════════════════════════════════════════════════
           MY MOOD TAB STATE
        ══════════════════════════════════════════════════════════════════ */
        const now = new Date();
        let vY = now.getFullYear();
        let vM = now.getMonth() + 1;  // 1-based
        let calOpen = false;

        /* ══════════════════════════════════════════════════════════════════
           RENDER — MY MOOD SUMMARY CARD
        ══════════════════════════════════════════════════════════════════ */
        function renderSummary() {
            const key = `${vY}-${String(vM).padStart(2, '0')}`;
            const isCurrent = (vY === now.getFullYear() && vM === (now.getMonth() + 1));
            const todayKey = now.toISOString().slice(0, 10);

            /* Today's mood */
            let todayMood = null, todayExtra = '';
            if (isCurrent && todayMyLog) {
                todayMood = moodById(todayMyLog.mood);
                todayExtra = (todayMyLog.loginCount > 1 ? MD_T.checkins_today : MD_T.checkin_today).replace(':count', todayMyLog.loginCount);
            }
            const tq = todayMood ? QS[todayMood.q] : null;

            /* Month average */
            /* Month dominant mood */
            const monthEntries = myLogs.filter(e => e.date && e.date.startsWith(key));

            let avgMood = null;

            if (monthEntries.length) {

                const moodCounts = {};

                monthEntries.forEach(entry => {
                    moodCounts[entry.mood] = (moodCounts[entry.mood] || 0) + 1;
                });

                let dominantMood = null;
                let highestCount = 0;

                Object.keys(moodCounts).forEach(mood => {
                    if (moodCounts[mood] > highestCount) {
                        highestCount = moodCounts[mood];
                        dominantMood = mood;
                    }
                });

                avgMood = moodById(dominantMood);
            }

            /* Unique days logged in this month */
            const daysSet = new Set(monthEntries.map(e => e.date));
            const daysLogged = daysSet.size;

            const sc = document.getElementById('my-summary');
            sc.style.borderColor = tq ? tq.color + '44' : 'var(--border)';

            sc.innerHTML = `
        <div class="summary-col-left">
            <div class="summary-section-label">${MD_T.today}</div>
            ${todayMood
                    ? faceBlock(todayMood, 52, todayExtra)
                    : `<div style="font-size:12px;color:var(--text-muted);padding:8px 0">${MD_T.not_logged}</div>`}
        </div>
        <div class="summary-divider" style="background:${tq ? tq.color + '33' : 'var(--border)'}"></div>
        <div class="summary-col-right">
            <div class="summary-section-label">${MD_T.month_average.replace(':month', MN[vM - 1])}</div>
            ${avgMood
                    ? faceBlock(avgMood, 52, (daysLogged !== 1 ? MD_T.days_logged : MD_T.day_logged).replace(':count', daysLogged))
                    : `<div style="font-size:12px;color:var(--text-muted);padding:8px 0">${MD_T.no_data}</div>`}
        </div>`;

            document.getElementById('month-label').textContent = `${MN[vM - 1]} ${vY}`;
            const bw = document.getElementById('month-badge-wrap');
            bw.innerHTML = isCurrent ? `<span class="current-badge">${MD_T.current_month}</span>` : '';
            document.getElementById('next-btn').disabled = isCurrent;
        }

        /* ══════════════════════════════════════════════════════════════════
           RENDER — CALENDAR
        ══════════════════════════════════════════════════════════════════ */
        function renderCalendar() {
            const key = `${vY}-${String(vM).padStart(2, '0')}`;
            const days = new Date(vY, vM, 0).getDate();
            const first = new Date(vY, vM - 1, 1).getDay();
            const todayStr = now.toISOString().slice(0, 10);

            /* Build per-day aggregates for this month from myLogs */
            const dayMap = {};
            myLogs.filter(e => e.date && e.date.startsWith(key)).forEach(e => {
                const dk = e.date;
                if (!dayMap[dk]) dayMap[dk] = { energySum: 0, pleasantnessSum: 0, count: 0, loginCount: 0 };
                dayMap[dk].energySum += +e.energy;
                dayMap[dk].pleasantnessSum += +e.pleasantness;
                dayMap[dk].count++;
                dayMap[dk].loginCount = e.loginCount || dayMap[dk].count;
            });

            let html = `<div class="cal-month-title">${MN[vM - 1]} ${vY}</div>`;
            html += `<div class="cal-dow-row">`;
            Array.from({ length: 7 }, (_, d) => new Date(2000, 0, 2 + d)
                .toLocaleString(document.documentElement.lang, { weekday: 'short' }).slice(0, 2)).forEach(d => { html += `<div class="cal-dow">${d}</div>`; });
            html += `</div><div class="cal-grid">`;

            for (let i = 0; i < first; i++) html += `<div></div>`;

            for (let d = 1; d <= days; d++) {
                const dk = `${vY}-${String(vM).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                const entry = dayMap[dk];
                const mood = entry ? closest(entry.energySum / entry.count, entry.pleasantnessSum / entry.count) : null;
                const q = mood ? QS[mood.q] : null;
                const isTod = dk === todayStr;
                const lc = entry ? entry.loginCount : 0;

                html += `<div class="cal-cell ${mood ? 'has-mood' : ''} ${isTod ? 'is-today' : ''}"
            style="background:${q ? q.color + '18' : '#f5f7f6'};color:${q ? q.color : '#ccc'};"
            title="${mood ? mood.label + (lc > 1 ? ' (×' + lc + ')' : '') : ''}">
            ${d}
            ${mood ? `<div class="cal-dot" style="background:${q.color}"></div>` : ''}
            ${lc > 1 ? `<div class="cal-multi" style="color:${q.color}">×${lc}</div>` : ''}
        </div>`;
            }

            html += `</div><div class="cal-legend">`;
            Object.entries(QS).forEach(([k, qs]) => {
                html += `<div class="cal-legend-item"><div class="cal-legend-dot" style="background:${qs.color}"></div>${qs.l1} · ${qs.l2}</div>`;
            });
            html += `<div class="cal-legend-item"><div class="cal-legend-dot" style="background:var(--text-muted)"></div>×2 = ${MD_T.multiple_checkins}</div></div>`;

            document.getElementById('cal-wrap').innerHTML = html;
        }

        function toggleCal() {
            calOpen = !calOpen;
            const btn = document.getElementById('cal-toggle-btn');
            const wrap = document.getElementById('cal-wrap');
            wrap.classList.toggle('open', calOpen);
            btn.classList.toggle('open', calOpen);
            btn.innerHTML = `
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        ${calOpen ? MD_T.hide_calendar : MD_T.view_calendar}`;
            if (calOpen) renderCalendar();
        }

        function changeMonth(dir) {
            vM += dir;
            if (vM > 12) { vM = 1; vY++; }
            if (vM < 1) { vM = 12; vY--; }
            renderSummary();
            if (calOpen) renderCalendar();
        }

        /* ══════════════════════════════════════════════════════════════════
           RENDER — CITY MOOD TAB (fully dynamic)
        ══════════════════════════════════════════════════════════════════ */
        function renderCity() {
            const curY = now.getFullYear();
            const curM = now.getMonth() + 1;

            /* ── Current month ── */
            const current = cityMonths.find(r => r.year == curY && r.month == curM);
            //console.log(current);
            const cityCurrentEl = document.getElementById('city-current');

            if (current && (current.totalLogs > 0 || current.daysLogged > 0)) {
                const cm = moodById(current.dominantMood);
                const q = QS[cm.q];
                const daysInMonth = new Date(curY, curM, 0).getDate();
                const energyPct = Math.round((+current.avgEnergy / 10) * 100);
                const pleasantnessPct = Math.round((+current.avgPleasantness / 10) * 100);
                const daysPct = Math.round((current.daysLogged / daysInMonth) * 100);

                cityCurrentEl.innerHTML = `
            <div class="city-current-card" style="border-color:${q.color}44">
                <div class="city-current-header">
                    <div>
                        <div class="city-current-title">${MN[curM - 1]} ${curY}</div>
                        <div class="city-current-sub">${MD_T.class_average} · ${MD_T.total_checkins.replace(':count', current.totalLogs)}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <span class="current-badge">${MD_T.current_month}</span>
                        ${qBadge(cm.q)}
                    </div>
                </div>
                <div class="city-current-body">
                    ${faceBlock(cm, 64)}
                    <div class="city-stats">
                        <div class="city-stat-row">
                            <div class="city-stat-label">⚡ ${MD_T.avg_energy}</div>
                            <div class="city-stat-track"><div class="city-stat-fill" style="width:${energyPct}%;background:${q.color}"></div></div>
                            <div class="city-stat-val" style="color:${q.color}">${(+current.avgEnergy).toFixed(1)}/10</div>
                        </div>
                        <div class="city-stat-row">
                            <div class="city-stat-label">☀️ ${MD_T.avg_comfort}</div>
                            <div class="city-stat-track"><div class="city-stat-fill" style="width:${pleasantnessPct}%;background:${q.color}"></div></div>
                            <div class="city-stat-val" style="color:${q.color}">${(+current.avgPleasantness).toFixed(1)}/10</div>
                        </div>
                        <div class="city-stat-row">
                            <div class="city-stat-label">📅 ${MD_T.days_logged_label}</div>
                            <div class="city-stat-track"><div class="city-stat-fill" style="width:${daysPct}%;background:var(--border)"></div></div>
                            <div class="city-stat-val">${current.daysLogged}/${daysInMonth}</div>
                        </div>
                    </div>
                </div>
            </div>`;
            } else {
                cityCurrentEl.innerHTML = `
            <div class="city-current-card">
                <div class="city-current-header">
                    <div>
                        <div class="city-current-title">${MN[curM - 1]} ${curY}</div>
                        <div class="city-current-sub">${MD_T.class_average}</div>
                    </div>
                    <span class="current-badge">${MD_T.current_month}</span>
                </div>
                <div class="no-data-msg">${MD_T.no_entries}</div>
            </div>`;
            }

            /* ── Past months (all except current) ── */
            const past = cityMonths
                .filter(r => !(r.year == curY && r.month == curM))
                .sort((a, b) => (b.year * 100 + b.month) - (a.year * 100 + a.month));

            const pastListEl = document.getElementById('past-list');

            if (!past.length) {
                pastListEl.innerHTML = `<div class="no-data-msg">${MD_T.no_past}</div>`;
                return;
            }

            pastListEl.innerHTML = past.map(r => {
                const m = moodById(r.dominantMood);
                const pq = QS[m.q];
                return `
            <div class="past-card">
                <div class="past-month-box" style="background:${pq.bg};border:1px solid ${pq.color}33">
                    <div class="past-month-abbr" style="color:${pq.color}">${MN3[r.month - 1]}</div>
                    <div class="past-month-year" style="color:${pq.color}">${r.year}</div>
                </div>
                ${faceBlock(m, 44)}
                <div class="past-info">
                    <div class="past-mood-name" style="color:${pq.color}">${m.label}</div>
                    <div class="past-meta">
                        ⚡ ${MD_T.energy} ${(+r.avgEnergy).toFixed(1)}/10 · ☀️ ${MD_T.comfort} ${(+r.avgPleasantness).toFixed(1)}/10<br>
                        ${(r.daysLogged !== 1 ? MD_T.days_logged : MD_T.day_logged).replace(':count', r.daysLogged)} · ${MD_T.total_checkins.replace(':count', r.totalLogs)}
                    </div>
                </div>
                <div class="past-tag" style="color:${pq.color};background:${pq.bg};border-color:${pq.color}44">
                    ${pq.l1}<br>${pq.l2}
                </div>
            </div>`;
            }).join('');
        }

        /* ══════════════════════════════════════════════════════════════════
           TAB SWITCHING
        ══════════════════════════════════════════════════════════════════ */
        function showTab(t, btn) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-page').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(`page-${t}`).classList.add('active');
        }

        /* expose only required functions */
        window.toggleCal = toggleCal;
        window.showTab = showTab;
        window.changeMonth = changeMonth;


        /* ══════════════════════════════════════════════════════════════════
           INIT
        ══════════════════════════════════════════════════════════════════ */
        renderSummary();
        renderCity();
    })();
</script>