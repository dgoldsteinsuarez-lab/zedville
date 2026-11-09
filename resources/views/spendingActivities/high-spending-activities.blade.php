@extends('layouts.profile')

@section('title', __('spending.page_title'))

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
            font-size: 12px; color: var(--text-muted);
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
            font-size: 12px; font-weight: 700;
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
            font-size: 12px; font-weight: 500;
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
            font-size: 12px; font-weight: 700; letter-spacing: 0.05em;
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

        /* ─── SCROLLBAR ─── */
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
        {{ __('spending.heading') }}
    </h1>
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

        {{-- No "Home › Educational Finance Dept. › Spending Activity" trail: Back
             and the heading already say where you are (Daniel, 2026-10-28). --}}

        <!-- Activities Grid -->
        <div class="section-header">
            <div class="section-title">{{ __('spending.all_activities') }}</div>
            <div class="section-badge">{{ trans_choice('spending.activity_count', $assigned->count(), ['count' => $assigned->count()]) }}</div>
        </div>
        <div class="activities-grid" id="activitiesGrid">
            @forelse ($assigned as $card)
                <div class="activity-card" role="button" tabindex="0" onclick="zvOpenTask('zvTask{{ $card->id }}')">
                    <div class="card-top">
                        <span class="card-number">{{ __('spending.kinds.' . $card->type) }} · {{ __('spending.activity_n', ['n' => $card->number]) }}</span>
                        <span class="card-arrow">→</span>
                    </div>
                    <div class="card-title">{{ $card->text('title') }}</div>
                    <div class="card-desc">{{ $card->text('mission') }}</div>
                    <div class="card-meta">
                        <span class="meta-tag shop">🏪 {{ str_replace(',', ', ', $card->shop) }}</span>
                        <span class="meta-tag dest">📍 {{ $card->text('destination') }}</span>
                        @if ($card->budget)
                            <span class="meta-tag budget">📝 {{ __('spending.list.budget') }}: {{ zeds($card->budget) }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <p>{{ __('spending.no_activity') }}</p>
            @endforelse
        </div>

        

    </div><!-- /content -->
</div><!-- /main -->

<!-- ══════════ MODAL ══════════ -->
@foreach ($assigned as $card)
    @include('spendingActivities._task', ['activity' => $card, 'taskId' => 'zvTask' . $card->id, 'shopLinks' => collect([
        'Supermarket' => '/spending-activities/market-list',
        'Tech Hub' => '/spending-activities/spending-tracker/tech-hub',
        'Beats - Music Store' => '/spending-activities/spending-tracker/beats-music-store',
        'BeSpirit - Sport Shop' => '/spending-activities/spending-tracker/bespirit-sport-shop',
        'Comfort Zone' => '/spending-activities/spending-tracker/comfort-zone',
        'Stationery Store' => '/spending-activities/spending-tracker/stationery-store',
        'BeU - BeLuxury' => '/spending-activities/spending-tracker/beu-beLuxury',
        'The Basics Co.' => '/spending-activities/spending-tracker/basicco',
        'Accessories' => '/spending-activities/spending-tracker/accessories',
        'Daily Essentials' => '/spending-activities/spending-tracker/daily-essentials',
    ])->map(fn ($path) => url($path) . '?activity_id=' . $card->id)->all()])
@endforeach
@endsection
