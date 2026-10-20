@extends('layouts.profile')

@section('title', 'High Spending Activities')

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ══════════════════════════════════════════
           ZedVille Design System — Exact Match
        ══════════════════════════════════════════ */
        :root {
            --bg-page:      #eef9f5;
            --bg-white:     #ffffff;
            --bg-card-alt:  #f5f7f6;
            --teal:         #1aaa8e;
            --teal-light:   #e8f7f3;
            --teal-mid:     #b6e8d8;
            --teal-dark:    #158870;
            --navy:         #1a1a2e;
            --red:          #e03e3e;
            --amber-bg:     #fff7ed;
            --amber-border: #f5d9a0;
            --amber-text:   #8a5a00;
            --blue-bg:      #eff4ff;
            --blue-border:  #c5d7f5;
            --blue-text:    #2a5aa0;
            --border:       #e4eae7;
            --text-primary: #1a1a2e;
            --text-secondary: #666666;
            --text-muted:   #999999;
            --sidebar-w:    220px;
            --radius-card:  14px;
            --radius-pill:  50px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: var(--bg-page);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            font-size: 14px;
            line-height: 1.5;
        }

        /* ─── MAIN ─── */
        .main {
            /*margin-left: var(--sidebar-w);*/
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ─── TOPBAR ─── */
       
        /* ─── PAGE CONTENT ─── */
        .content {
            padding: 32px 32px 64px;
            max-width: 1060px;
        }

        /* ─── PAGE HEADER ─── */
        .page-header { margin-bottom: 24px; }
        .breadcrumb {
            font-size: 12px; color: var(--text-muted);
            margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
        }
        .breadcrumb a { color: var(--text-muted); text-decoration: none; }
        .breadcrumb a:hover { color: var(--teal); }
        .breadcrumb .sep { color: var(--border); }
        .breadcrumb .current { color: var(--teal); font-weight: 500; }
        .page-header h1 {
            font-size: 22px; font-weight: 700;
            color: var(--navy); margin-bottom: 6px;
            letter-spacing: -0.02em;
        }
        .page-header h1 .highlight { color: var(--teal); }
        .page-header p {
            font-size: 13px; color: var(--text-secondary);
            line-height: 1.65; max-width: 560px;
        }

        /* ─── BUDGET BAR ─── */
        .budget-bar {
            display: flex; flex-wrap: wrap; gap: 8px;
            margin-bottom: 28px;
        }
        .budget-chip {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--bg-white); border: 1px solid var(--border);
            padding: 8px 16px; border-radius: var(--radius-pill);
            font-size: 12px; color: var(--text-secondary);
        }
        .budget-chip strong { color: var(--navy); font-weight: 600; }
        .chip-dot {
            width: 7px; height: 7px; border-radius: 50%;
            flex-shrink: 0;
        }
        .chip-dot.teal   { background: var(--teal); }
        .chip-dot.orange { background: #f59e0b; }
        .chip-dot.blue   { background: #3b82f6; }

        /* ─── SECTION HEADER ─── */
        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 14px;
        }
        .section-title { font-size: 15px; font-weight: 600; color: var(--navy); }
        .section-badge {
            font-size: 11px; color: var(--text-muted);
            background: var(--bg-white); border: 1px solid var(--border);
            padding: 4px 12px; border-radius: var(--radius-pill);
        }

        /* ─── ACTIVITY CARDS ─── */
        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(298px, 1fr));
            gap: 12px;
            margin-bottom: 40px;
        }
        .activity-card {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            padding: 20px;
            cursor: pointer;
            transition: box-shadow 0.2s, border-color 0.2s, transform 0.18s;
        }
        .activity-card:hover {
            box-shadow: 0 6px 24px rgba(26,170,142,0.1), 0 2px 8px rgba(0,0,0,0.05);
            border-color: var(--teal-mid);
            transform: translateY(-2px);
        }
        .card-top {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 12px;
        }
        .card-number {
            display: inline-flex; align-items: center;
            font-size: 10px; font-weight: 700;
            letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--teal); background: var(--teal-light);
            padding: 3px 10px; border-radius: var(--radius-pill);
        }
        .card-arrow {
            width: 26px; height: 26px; border-radius: 50%;
            border: 1.5px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted); font-size: 12px;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
        }
        .activity-card:hover .card-arrow {
            background: var(--teal); border-color: var(--teal); color: #fff;
        }
        .card-title {
            font-size: 14px; font-weight: 600;
            color: var(--navy); margin-bottom: 6px; line-height: 1.35;
        }
        .card-desc {
            font-size: 12px; color: var(--text-secondary);
            line-height: 1.6; margin-bottom: 14px;
        }
        .card-meta { display: flex; flex-wrap: wrap; gap: 6px; }
        .meta-tag {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 500;
            padding: 3px 9px; border-radius: 6px;
        }
        .meta-tag.shop   { background: var(--teal-light); color: #0a7a5c; }
        .meta-tag.dest   { background: var(--blue-bg);    color: var(--blue-text); }
        .meta-tag.budget { background: var(--amber-bg);   color: var(--amber-text); }

        /* ─── IT RULES SECTION ─── */
        .it-section {
            border-top: 1px solid var(--border);
            padding-top: 32px;
        }
        .it-section-header {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 16px;
        }
        .it-section-header h2 {
            font-size: 15px; font-weight: 600; color: var(--navy);
        }
        .it-team-badge {
            font-size: 10px; font-weight: 700; letter-spacing: 0.05em;
            text-transform: uppercase;
            background: var(--amber-bg); color: var(--amber-text);
            border: 1px solid var(--amber-border);
            padding: 3px 10px; border-radius: var(--radius-pill);
        }
        .it-rule {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 8px;
        }
        .it-rule strong {
            display: block; font-size: 13px; font-weight: 600;
            color: var(--navy); margin-bottom: 5px;
        }
        .it-rule p {
            font-size: 12px; color: var(--text-secondary); line-height: 1.65;
        }
        .it-warning {
            background: var(--amber-bg);
            border: 1px solid var(--amber-border);
            border-radius: 10px; padding: 14px 18px; margin-top: 12px;
        }
        .it-warning strong {
            display: block; font-size: 13px; font-weight: 600;
            color: var(--amber-text); margin-bottom: 5px;
        }
        .it-warning p {
            font-size: 12px; color: var(--amber-text); line-height: 1.65;
        }

        /* ─── MODAL ─── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(10,25,20,0.45);
            backdrop-filter: blur(4px);
            z-index: 1000; display: none;
            align-items: center; justify-content: center;
            padding: 24px;
        }
        .modal-overlay.active { display: flex; }

        .modal {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: 20px;
            max-width: 580px; width: 100%;
            max-height: 88vh; overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0,0,0,0.13);
        }

        .modal-header {
            padding: 24px 24px 20px;
            border-bottom: 1px solid var(--border);
            position: relative;
        }
        .modal-close {
            position: absolute; top: 18px; right: 18px;
            width: 32px; height: 32px; border-radius: 50%;
            border: 1.5px solid var(--border); background: var(--bg-white);
            color: var(--text-secondary); font-size: 13px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            line-height: 1;
        }
        .modal-close:hover { background: var(--red); border-color: var(--red); color: #fff; }

        .modal-label {
            display: inline-flex; align-items: center;
            font-size: 10px; font-weight: 700;
            letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--teal); background: var(--teal-light);
            padding: 3px 10px; border-radius: var(--radius-pill);
            margin-bottom: 10px;
        }
        .modal-title {
            font-size: 20px; font-weight: 700; color: var(--navy);
            line-height: 1.25; padding-right: 40px;
        }

        .modal-body { padding: 20px 24px 24px; }

        .mission-box {
            background: var(--teal-light);
            border: 1px solid var(--teal-mid);
            border-radius: 10px; padding: 14px 16px; margin-bottom: 14px;
        }
        .mission-label {
            font-size: 10px; font-weight: 700;
            letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--teal); margin-bottom: 6px;
        }
        .mission-box p { font-size: 13px; color: var(--navy); line-height: 1.65; }

        .special-req-box {
            background: var(--blue-bg);
            border: 1px solid var(--blue-border);
            border-radius: 10px; padding: 12px 16px; margin-bottom: 14px;
        }
        .special-label {
            font-size: 10px; font-weight: 700;
            letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--blue-text); margin-bottom: 6px;
        }
        .special-req-box p { font-size: 12px; color: #2a4a80; line-height: 1.55; }

        .details-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 8px; margin-bottom: 16px;
        }
        .detail-item {
            background: var(--bg-page);
            border: 1px solid var(--border);
            border-radius: 10px; padding: 12px 14px;
        }
        .detail-label {
            font-size: 10px; font-weight: 700;
            letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--text-muted); margin-bottom: 5px;
        }
        .detail-value { font-size: 13px; font-weight: 600; color: var(--navy); }
        .detail-value.teal   { color: var(--teal); }
        .detail-value.orange { color: #d97706; }
        .detail-value.blue   { color: var(--blue-text); }

        .rules-list { margin-bottom: 20px; }
        .rules-title {
            font-size: 10px; font-weight: 700;
            letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--text-muted); margin-bottom: 10px;
        }
        .rule {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 9px 0; border-bottom: 1px solid var(--border);
        }
        .rule:last-child { border-bottom: none; }
        .rule .check {
            width: 20px; height: 20px; border-radius: 50%;
            background: var(--teal-light); color: var(--teal);
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700;
            flex-shrink: 0; margin-top: 1px;
        }
        .rule p { font-size: 12px; color: var(--text-secondary); line-height: 1.55; }
        .rule p strong { color: var(--navy); font-weight: 600; }

        /* ─── BUTTONS ─── */
        .btn-pill {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 11px 22px; border: none;
            border-radius: var(--radius-pill);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all 0.2s;
            text-decoration: none;
        }
        .btn-pill.teal {
            background: var(--teal); color: #fff; width: 100%; margin-bottom: 8px;
        }
        .btn-pill.teal:hover {
            background: var(--teal-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(26,170,142,0.28);
        }
        .btn-pill.outline {
            background: var(--bg-white); color: var(--navy);
            border: 2px solid var(--navy);
        }
        .btn-pill.outline:hover { background: var(--bg-page); }

        /* ─── SCROLLBAR ─── */
        .modal::-webkit-scrollbar { width: 5px; }
        .modal::-webkit-scrollbar-track { background: transparent; }
        .modal::-webkit-scrollbar-thumb { background: var(--teal-mid); border-radius: 3px; }
        .sidebar::-webkit-scrollbar { width: 3px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; }
            .content { padding: 20px 16px 48px; }
            .details-grid { grid-template-columns: 1fr; }
            .activities-grid { grid-template-columns: 1fr; }
        }
    </style>
  @endpush
  <div class="flex items-center justify-between pb-6">
    <h1 class="text-xl font-bold whitespace-nowrap">
        Spending Activity
    </h1>

    <a href="{{ url()->previous() }}"
       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
        ← Back
    </a>
</div>

<div class="grid grid-cols-1 gap-5 ">
    <div class="themeTabspills">
        <div class="w-full">
            <div id="root"></div>
        </div>
    </div>
</div>
 <!-- ══════════ MAIN ══════════ -->
<div class="main">

    <!-- Page Content -->
    <div class="content">

        <!-- Header -->
        <div class="page-header">
            <div class="breadcrumb">
                <a href="#">Home</a>
                <span class="sep">›</span>
                <a href="#">Educational Finance Dept.</a>
                <span class="sep">›</span>
                <span class="current">Spending Activity</span>
            </div>
        </div>

        

        <!-- Activities Grid -->
        <div class="section-header">
            <div class="section-title">All Activities</div>
            <div class="section-badge">1 activities</div>
        </div>
        <div class="activities-grid" id="activitiesGrid"></div>

        

    </div><!-- /content -->
</div><!-- /main -->

<!-- ══════════ MODAL ══════════ -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal" id="modalContent"></div>
</div>
@endsection
@push('scripts')
<script>
    // Daniel, 2026-10-21: "did you find all the activities for Spending
    // activities?" - the 10 High activities are now a real DB catalog
    // (app/Models/Activity.php), and the one assigned to this student's
    // class is resolved server-side (EFDController::high_spending_
    // activities()) instead of read from calendar_events.activityType
    // (which nothing ever actually set). The server already knows which
    // one, so it's passed directly - no client-side array/number lookup
    // needed any more.
    const a = {!! $activity ? $activity->toJson() : 'null' !!};

    async function getStudentSalary() {
        return 3953; // ⚠️ Placeholder — IT team replaces this
    }

    let minZeds = 0, maxZeds = 0;

    async function init() {
        const salary = await getStudentSalary();

        minZeds = Math.round(salary * 0.10);
        maxZeds = Math.round(salary * 0.20);

        document.querySelectorAll('.min-zeds').forEach(el => el.textContent = minZeds);
        document.querySelectorAll('.max-zeds').forEach(el => el.textContent = maxZeds);

        const grid = document.getElementById('activitiesGrid');
        grid.innerHTML = '';

        if (!a) {
            grid.innerHTML = '<p>No activity assigned</p>';
            return;
        }

        const card = document.createElement('div');
        card.className = 'activity-card';
        card.onclick = () => openModal(a);

        card.innerHTML = `
            <div class="card-top">
                <span class="card-number">Activity ${a.number}</span>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-title">${a.title}</div>
            <div class="card-desc">${a.mission}</div>
            <div class="card-meta">
                <span class="meta-tag shop">🏪 ${a.shop.split(',').join(', ')}</span>
                <span class="meta-tag dest">📍 ${a.destination}</span>
                <span class="meta-tag budget">Ƶ ${minZeds}–${maxZeds}</span>
            </div>
        `;

        grid.appendChild(card);
    }
    function openModal(a) {
        const maxLabel = a.max_items ? `${a.min_items}–${a.max_items} items` : `${a.min_items}+ items`;
        const shops = a.shop.split(',').map(s => s.trim());
        const shopButtons = shops.map(s =>
            `<button class="btn-pill teal" onclick="startShopping('${s}')">🛒 Start Shopping at ${s} →</button>`
        ).join('');

        const modal = document.getElementById('modalContent');
        modal.innerHTML = `
            <div class="modal-header">
                <button class="modal-close" onclick="closeModal()">✕</button>
                <div class="modal-label">Activity ${a.number} of 10</div>
                <div class="modal-title">${a.title}</div>
            </div>
            <div class="modal-body">

                <div class="mission-box">
                    <div class="mission-label">🎯 Your Mission</div>
                    <p>${a.mission}</p>
                </div>

                ${a.special ? `
                <div class="special-req-box">
                    <div class="special-label">⚡ Special Requirements</div>
                    <p>${a.special}</p>
                </div>` : ''}

                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Budget Range</div>
                        <div class="detail-value orange">Ƶ ${minZeds}–${maxZeds} (10–20%)</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Items Required</div>
                        <div class="detail-value teal">${maxLabel}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Shop At</div>
                        <div class="detail-value">${a.shop}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Delivery To</div>
                        <div class="detail-value blue">${a.destination}</div>
                    </div>
                </div>

                <div class="rules-list">
                    <div class="rules-title">Shopping Rules</div>
                    <div class="rule"><span class="check">✓</span><p>Spend between <strong>10–20% of your salary</strong> (Ƶ ${minZeds}–${maxZeds})</p></div>
                    <div class="rule"><span class="check">✓</span><p>Buy <strong>${maxLabel}</strong> for this activity</p></div>
                    ${a.special ? `<div class="rule"><span class="check">✓</span><p><strong>Special:</strong> ${a.special}</p></div>` : ''}
                    <div class="rule"><span class="check">✓</span><p>Shop at <strong>${a.shop}</strong></p></div>
                    <div class="rule"><span class="check">✓</span><p>Delivered automatically to <strong>${a.destination}</strong></p></div>
                    <div class="rule"><span class="check">✓</span><p>Below 10%? You'll be prompted to spend more. Above 20%? Alert to remove items.</p></div>
                </div>

                ${shopButtons}
            </div>
        `;
        document.getElementById('modalOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('modalOverlay').classList.remove('active');
        document.body.style.overflow = '';
    }

    /*function startShopping(shop) {
        // ╔══════════════════════════════════════════════════════════════╗
        // ║  🔌 IT TEAM: Replace with navigation to actual shop page   ║
        // ║  EXAMPLE: window.location.href = '/shops/' + shopSlug;     ║
        // ╚══════════════════════════════════════════════════════════════╝
        alert(`🛒 Navigating to ${shop}...\n\nIT Team: Connect this button to your platform's shop page.`);
        closeModal();
    }*/

    const shopRoutes = {
 "Supermarket": "{{ url('/spending-activities/market-list') }}?activity_id={{ $activity->id ?? '' }}",
 "Tech Hub": "{{ url('/spending-activities/spending-tracker/tech-hub') }}?activity_id={{ $activity->id ?? '' }}",
 "Beats - Music Store": "{{ url('/spending-activities/spending-tracker/beats-music-store') }}?activity_id={{ $activity->id ?? '' }}",
  "BeSpirit - Sport Shop": "{{ url('/spending-activities/spending-tracker/bespirit-sport-shop') }}?activity_id={{ $activity->id ?? '' }}",
   "Comfort Zone": "{{ url('/spending-activities/spending-tracker/comfort-zone') }}?activity_id={{ $activity->id ?? '' }}",
"Stationery Store": "{{ url('/spending-activities/spending-tracker/stationery-store') }}?activity_id={{ $activity->id ?? '' }}",
"BeU - BeLuxury": "{{ url('/spending-activities/spending-tracker/beu-beLuxury') }}?activity_id={{ $activity->id ?? '' }}",
"The Basics Co.": "{{ url('/spending-activities/spending-tracker/basicco') }}?activity_id={{ $activity->id ?? '' }}",

};
function startShopping(shop) {
    const url = shopRoutes[shop];

    if (url) {
        window.location.href = url; // ✅ redirect
    } else {
        alert("Shop not mapped: " + shop);
    }
}

    document.getElementById('modalOverlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModal();
    });

    init();
</script>
@endpush