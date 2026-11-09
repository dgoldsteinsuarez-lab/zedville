@extends('layouts.profile')

@section('title', 'NPOs ')

@section('content')

<style>
    :root {
        --green-primary: #00A47D;
        --green-hover: #016950;
        --green-light: #EEF9F5;
        --green-muted: #9FD8CB;
        --green-text: #016950;
        --card-shadow: 0 2px 8px rgba(0,0,0,0.07);
        --card-radius: 8px;
        --border-color: #D2DDDB;
        --bg-page: #F7FCF7;
        --text-primary: #222222;
        --text-secondary: #5C5C5C;
        --text-muted: #999999;
    }

    body, .content-area {
        background-color: var(--bg-page);
    }

    /* ── Toggle buttons ── */
    .view-toggle {
        display: inline-flex;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }

    .view-toggle button {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-muted);
        background: transparent;
        border: none;
        cursor: pointer;
        transition: background 0.18s, color 0.18s;
        white-space: nowrap;
    }

    .view-toggle button svg {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
    }

    .view-toggle button.active {
        background: var(--green-primary);
        color: #fff;
    }

    .view-toggle button:not(.active):hover {
        background: var(--green-light);
        color: var(--green-text);
    }

    /* ── Grid view ── */
    #npo-grid.grid-view {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 20px;
    }

    @media (min-width: 640px) {
        #npo-grid.grid-view { grid-template-columns: repeat(2, 1fr); }
    }
    @media (min-width: 1024px) {
        #npo-grid.grid-view { grid-template-columns: repeat(4, 1fr); }
    }

    /* ── List view ── */
    #npo-grid.list-view {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    /* ── Card – shared ── */
    .npo-card {
        background: #fff;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-color);
        overflow: hidden;
        transition: box-shadow 0.2s, transform 0.2s;
    }

    .npo-card:hover {
        box-shadow: 0 6px 20px rgba(0, 164, 125, 0.15);
        transform: translateY(-2px);
    }

    /* ── Grid card ── */
    .grid-view .npo-card {
        display: flex;
        flex-direction: column;
    }

    .grid-view .npo-card .card-img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        display: block;
    }

    .grid-view .npo-card .card-body {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .grid-view .npo-card .card-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .grid-view .npo-card .card-desc {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.55;
        flex: 1;
        margin-bottom: 14px;
    }

    /* ── List card ── */
    .list-view .npo-card {
        display: flex;
        flex-direction: row;
        align-items: stretch;
    }

    .list-view .npo-card .card-img {
        width: 140px;
        min-width: 140px;
        height: 130px;
        object-fit: cover;
        display: block;
        border-radius: 0;
    }

    @media (max-width: 640px) {
        .list-view .npo-card { flex-direction: column; }
        .list-view .npo-card .card-img { width: 100%; height: 140px; min-width: unset; }
    }

    .list-view .npo-card .card-body {
        padding: 14px 18px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .list-view .npo-card .card-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .list-view .npo-card .card-desc {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.5;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* ── Donate button ── */
    .btn-donate {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 20px;
        background: var(--green-primary);
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        border-radius: 30px;
        border: 1px solid var(--green-hover);
        box-shadow: 0 3px 0 var(--green-hover);
        text-decoration: none;
        cursor: pointer;
        transition: 0.2s;
        align-self: flex-start;
    }

    .btn-donate:hover {
        background: var(--green-hover);
        transform: translateY(3px);
        box-shadow: 0 1px 0 var(--green-hover);
        color: #fff;
        text-decoration: none;
    }

    .btn-donate svg {
        width: 14px;
        height: 14px;
    }

    /* ── Category badge ── */
    .npo-badge {
        display: inline-block;
        padding: 2px 8px;
        background: var(--green-light);
        color: var(--green-text);
        font-size: 12px;
        font-weight: 600;
        border-radius: 20px;
        margin-bottom: 6px;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    /* ── Page header ── */
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding-bottom: 20px;
    }

    .page-header h1 {
        font-size: 20px;
        font-weight: 700;
        font-family: 'Manrope', sans-serif;
        color: var(--text-primary);
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .npo-count {
        font-size: 13px;
        color: var(--text-muted);
    }

    /* ── List view right side action area ── */
    .list-view .card-action-col {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: center;
        padding: 14px 18px 14px 0;
        min-width: 130px;
    }

    .grid-view .card-action-col {
        display: none;
    }

    /* In grid view, btn is inside card-body; in list view it moves to action col */
    .list-view .card-body .btn-donate {
        display: none;
    }

    .grid-view .card-action-col { display: none; }
</style>

<!-- Page Header -->
<div class="page-header">
    <h1>{{ __('messages.nav_npos') }} <span class="npo-count">— {{ trans_choice('npos.organisations', $npos->count()) }}</span></h1>
    <div class="header-right">
        <div class="view-toggle" id="viewToggle">
            <button id="btnList" class="active" onclick="setView('list')" title="{{ __('npos.list_view') }}">
                <!-- List icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/>
                    <line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                {{ __('npos.list') }}
            </button>
            <button id="btnGrid" onclick="setView('grid')" title="{{ __('npos.grid_view') }}">
                <!-- Grid icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
                {{ __('npos.grid') }}
            </button>
        </div>
    </div>
</div>

<!-- NPO Cards -->
<div id="npo-grid" class="list-view">
@foreach($npos as $npo)
<!-- Card 1: Animal Shelter -->
    <div class="npo-card">
        <img src="{{ asset('public/uploads/npos/'.$npo->image) }}" class="card-img" alt="{{ ct('activities', $npo->name) }}">
        <div class="card-body">
            <span class="npo-badge">{{ ct('activities', $npo->category) }}</span>
            <h3 class="card-title">{{ ct('activities', $npo->name) }}</h3>
            <p class="card-desc">
                {{ ct('activities', $npo->content) }}
            </p>
            <a href="{{ route('education.npos.donate', $npo->slug) }}" class="btn-donate">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                {{ __('npos.donate_now') }}
            </a>
        </div>
        <div class="card-action-col">
            <a href="{{ route('education.npos.donate', $npo->slug) }}" class="btn-donate">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                {{ __('npos.donate_now') }}
            </a>
        </div>
    </div>
@endforeach

</div>

<script>
    function setView(mode) {
        const grid = document.getElementById('npo-grid');
        const btnList = document.getElementById('btnList');
        const btnGrid = document.getElementById('btnGrid');

        if (mode === 'grid') {
            grid.className = 'grid-view';
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
        } else {
            grid.className = 'list-view';
            btnList.classList.add('active');
            btnGrid.classList.remove('active');
        }

        // Persist preference
        try { localStorage.setItem('npoViewMode', mode); } catch(e) {}
    }

    // Restore preference on load
    (function() {
        try {
            const saved = localStorage.getItem('npoViewMode');
            if (saved === 'grid') setView('grid');
        } catch(e) {}
    })();
</script>

@endsection
