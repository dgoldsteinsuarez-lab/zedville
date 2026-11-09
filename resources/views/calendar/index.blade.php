@extends('layouts.profile')

@section('title', __('calendar.title'))

{{-- a page of the menu: without the Back button, the month gets its height --}}
@section('no_page_back', true)

{{-- The student calendar, option A (Daniel, 2026-11-04: "Make option A for the
     calendar"): a month, a week and a day that fit on the screen - no hours,
     because an activity lasts whole days, and no scrolling up and down.
     Month: each activity on its last day. Week: one bar per activity across
     the days it runs. Day: what to do, what is done and what comes next.
     The data come from App\Support\StudentCalendar. --}}

@push('styles')
<style>
.zvcal {
    --zc-ink: #0F2A22; --zc-muted: #4A5D56; --zc-line: #D2DDDB; --zc-line-soft: #EEF3F1; --zc-head: #F2F7F5;
    --zc-req: #016950; --zc-opt: #00A47D; --zc-done-bg: #E2E8E6; --zc-done-ink: #3F524B;
    --zc-today: #C2410C; --zc-today-ink: #9A3412; --zc-today-bg: #FFF4E8;
    display: flex; flex-direction: column; gap: 12px; min-height: 520px;
    color: var(--zc-ink); font-family: Manrope, system-ui, sans-serif;
}
.zvcal button { font-family: inherit; }
.zvcal button:focus-visible, .zvcal a:focus-visible { outline: 3px solid rgba(0, 164, 125, .45); outline-offset: 2px; }
.zvcal-bar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; flex: none; }
.zvcal-nav { display: flex; align-items: center; gap: 8px; }
.zvcal-btn { height: 44px; min-width: 44px; padding: 0 16px; border-radius: 10px; border: 1px solid var(--zc-line); background: #fff; color: var(--zc-req); font-weight: 700; font-size: 14px; display: inline-grid; place-items: center; cursor: pointer; }
.zvcal-btn.is-icon { width: 44px; padding: 0; color: var(--zc-ink); }
.zvcal-btn:hover { background: var(--zc-head); }
.zvcal-heading { margin: 0; font-size: 22px; font-weight: 800; }
.zvcal-views { display: flex; border: 1px solid var(--zc-line); border-radius: 10px; overflow: hidden; }
.zvcal-views button { height: 44px; padding: 0 18px; border: 0; background: #fff; color: var(--zc-req); font-weight: 700; font-size: 14px; cursor: pointer; }
.zvcal-views button + button { border-left: 1px solid var(--zc-line); }
.zvcal-views button[aria-pressed="true"] { background: var(--zc-req); color: #fff; }
.zvcal-legend { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; flex: none; font-size: 12px; font-weight: 600; color: var(--zc-muted); }
.zvcal-legend > span { display: inline-flex; align-items: center; gap: 6px; }
.zvcal-key { width: 12px; height: 12px; border-radius: 3px; box-sizing: border-box; display: inline-block; }
.zvcal-key.is-req { background: var(--zc-req); }
.zvcal-key.is-opt { border: 2px solid var(--zc-opt); }
.zvcal-key.is-done { background: var(--zc-done-bg); }
.zvcal-key.is-today { border-radius: 99px; background: var(--zc-today); }
.zvcal-legend .zvcal-hint { margin-left: auto; }
.zvcal-frame { flex: 1; min-height: 0; display: flex; flex-direction: column; border: 1px solid var(--zc-line); border-radius: 12px; overflow: hidden; }

/* month */
.zvcal-names { height: 28px; flex: none; display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); background: var(--zc-head); font-size: 12px; font-weight: 700; color: var(--zc-muted); text-transform: uppercase; letter-spacing: .06em; }
.zvcal-names div { display: grid; place-items: center; }
.zvcal-row { flex: 1; min-height: 0; display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); border-top: 1px solid #E2E8E6; }
.zvcal-cell { min-width: 0; min-height: 0; box-sizing: border-box; padding: 6px 7px; border-left: 1px solid var(--zc-line-soft); display: flex; flex-direction: column; gap: 3px; overflow: hidden; }
.zvcal-cell:first-child { border-left: 0; }
.zvcal-top { flex: none; height: 24px; display: flex; align-items: center; gap: 4px; min-width: 0; }
.zvcal-num { margin-left: auto; flex: none; display: flex; justify-content: flex-end; align-items: center; gap: 6px; border: 0; background: none; padding: 0; cursor: pointer; color: var(--zc-ink); min-width: 0; max-width: 100%; }
.zvcal-n { height: 24px; min-width: 24px; display: grid; place-items: center; font-size: 13px; font-weight: 600; border-radius: 99px; }
.zvcal-cell.is-out .zvcal-n { color: #9AA9A3; }
.zvcal-cell.is-today .zvcal-n { background: var(--zc-today); color: #fff; font-weight: 800; }
.zvcal-todo { font-size: 12px; font-weight: 700; color: var(--zc-today-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.zvcal .zvcal-pill { flex: none; height: 20px; box-sizing: border-box; display: flex; align-items: center; gap: 4px; padding: 0 6px; border-radius: 6px; font-size: 12px; line-height: 20px; font-weight: 600; white-space: nowrap; overflow: hidden; text-decoration: none; }
.zvcal-pill span { overflow: hidden; text-overflow: ellipsis; }
.zvcal-pill svg { flex: none; }
.zvcal .is-req.zvcal-pill, .zvcal .is-req.zvcal-pill:hover, .zvcal .is-req.zvcal-wbar, .zvcal .is-req.zvcal-wbar:hover { background: var(--zc-req); color: #fff; }
.zvcal .is-opt.zvcal-pill, .zvcal .is-opt.zvcal-pill:hover, .zvcal .is-opt.zvcal-wbar, .zvcal .is-opt.zvcal-wbar:hover { background: #fff; color: var(--zc-req); box-shadow: inset 0 0 0 1.5px var(--zc-opt); }
.zvcal .is-done.zvcal-pill, .zvcal .is-done.zvcal-pill:hover, .zvcal .is-done.zvcal-wbar, .zvcal .is-done.zvcal-wbar:hover { background: var(--zc-done-bg); color: var(--zc-done-ink); }
.zvcal-pill:hover, .zvcal-wbar:hover { filter: brightness(.94); }
.zvcal-more { flex: 0 1 auto; min-width: 0; border: 0; background: none; padding: 0 2px; font-size: 12px; font-weight: 700; color: var(--zc-req); cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* week */
.zvcal-weekhead { height: 54px; flex: none; display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); background: var(--zc-head); border-bottom: 1px solid #E2E8E6; }
.zvcal-wday { border: 0; background: none; padding: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; cursor: pointer; color: var(--zc-ink); }
.zvcal-wday.is-today { background: var(--zc-today-bg); }
.zvcal-wname { font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--zc-muted); }
.zvcal-wday.is-today .zvcal-wname { color: var(--zc-today-ink); }
.zvcal-wnum { height: 26px; min-width: 26px; display: grid; place-items: center; font-size: 14px; font-weight: 700; border-radius: 99px; }
.zvcal-wday.is-today .zvcal-wnum { background: var(--zc-today); color: #fff; font-weight: 800; }
.zvcal-bars { flex: none; max-height: 60%; overflow-y: auto; padding: 10px 0; display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); row-gap: 6px; border-bottom: 1px solid #E2E8E6; }
.zvcal-barcell { padding: 0 6px; min-width: 0; }
.zvcal .zvcal-wbar { height: 26px; box-sizing: border-box; padding: 0 10px; border-radius: 6px; display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-decoration: none; }
.zvcal-wbar svg { flex: none; }
.zvcal-wbar-title { overflow: hidden; text-overflow: ellipsis; }
.zvcal-wbar-tail { margin-left: auto; flex: none; font-weight: 700; opacity: .9; }
.zvcal-notes { flex: 1; min-height: 0; display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
.zvcal-notecol { min-width: 0; box-sizing: border-box; padding: 10px 8px; border-left: 1px solid var(--zc-line-soft); display: flex; flex-direction: column; gap: 6px; font-size: 12px; overflow: hidden; }
.zvcal-notecol:first-child { border-left: 0; }
.zvcal-note { padding: 6px 8px; border-radius: 8px; background: var(--zc-head); color: #33443E; line-height: 1.35; overflow-wrap: anywhere; }
.zvcal-notedate { display: none; font-size: 12px; font-weight: 800; }
.zvcal-note strong { color: var(--zc-ink); }

/* day */
.zvcal-dayview { flex: 1; min-height: 0; display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 20px; }
.zvcal-daymain, .zvcal-dayside { min-height: 0; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; }
.zvcal-sub { margin: 0; font-size: 15px; font-weight: 800; }
.zvcal-sub span { font-weight: 600; color: var(--zc-muted); }
.zvcal-card { flex: none; padding: 14px 16px; border: 1px solid var(--zc-line); border-radius: 12px; display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; column-gap: 16px; row-gap: 8px; }
.zvcal-card-top { display: flex; align-items: center; gap: 10px; min-width: 0; }
.zvcal-card-title { font-size: 15px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.zvcal-chip { flex: none; padding: 2px 8px; border-radius: 99px; font-size: 12px; font-weight: 700; }
.zvcal-chip.is-req { background: var(--zc-req); color: #fff; }
.zvcal-chip.is-opt { box-shadow: inset 0 0 0 1.5px var(--zc-opt); color: var(--zc-req); }
.zvcal .zvcal-open { grid-row: span 2; height: 44px; padding: 0 18px; border-radius: 10px; background: var(--zc-opt); color: #fff; font-weight: 700; font-size: 14px; display: inline-grid; place-items: center; text-decoration: none; }
.zvcal .zvcal-open:hover { background: var(--zc-req); color: #fff; }
.zvcal-card-time { display: flex; align-items: center; gap: 12px; font-size: 12.5px; color: #33443E; min-width: 0; }
.zvcal-track { flex: 1; min-width: 40px; height: 8px; border-radius: 99px; background: var(--zc-done-bg); overflow: hidden; }
.zvcal-track div { height: 100%; border-radius: 99px; background: #F59E0B; }
.zvcal-card-time strong { flex: none; }
.zvcal-card-time .zvcal-last { flex: none; color: var(--zc-muted); }
.zvcal-doneline { flex: none; display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 10px; background: var(--zc-head); font-size: 13px; color: var(--zc-done-ink); }
.zvcal-doneline svg { flex: none; color: var(--zc-opt); }
.zvcal-doneline .zvcal-done-title { font-weight: 600; }
.zvcal-doneline .zvcal-done-when { margin-left: auto; flex: none; }
.zvcal-soon { flex: none; padding: 12px 14px; border: 1px dashed #B9C7C2; border-radius: 12px; font-size: 13px; line-height: 1.4; }
.zvcal-soon-title { font-weight: 700; }
.zvcal-soon-when { color: var(--zc-muted); }
.zvcal-fine { margin-top: auto; flex: none; padding: 12px 14px; border-radius: 12px; background: var(--zc-today-bg); color: #7C2D12; font-size: 12.5px; line-height: 1.45; }
.zvcal-empty { margin: 0; padding: 4px 2px; color: var(--zc-muted); font-size: 13px; }
.zvcal-bars .zvcal-empty { grid-column: 1 / -1; padding: 0 12px; }

@media (max-width: 760px) {
    .zvcal-heading { order: -1; width: 100%; font-size: 19px; }
    .zvcal-legend .zvcal-hint, .zvcal-todo, .zvcal-wbar-tail { display: none; }
    /* a phone: each day's activities as dots in rows, and the week's notes as one list */
    .zvcal-cell { flex-direction: row; flex-wrap: wrap; align-content: flex-start; }
    .zvcal-top { flex-basis: 100%; }
    .zvcal-more { display: none; }
    .zvcal .zvcal-pill { width: 10px; height: 10px; padding: 0; border-radius: 99px; }
    .zvcal-pill span, .zvcal-pill svg { display: none; }
    .zvcal-notes { grid-template-columns: minmax(0, 1fr); overflow-y: auto; }
    .zvcal-notecol { border-left: 0; padding: 6px 10px; }
    .zvcal-notecol.is-empty { display: none; }
    .zvcal-notedate { display: block; }
    .zvcal-dayview { grid-template-columns: minmax(0, 1fr); }
}
</style>
@endpush

@section('content')
<div class="zvcal" x-data="zvCalendar(window.zvCalendarConfig)">
    <h1 class="sr-only">{{ __('calendar.title') }}</h1>

    <div class="zvcal-bar">
        <div class="zvcal-nav">
            <button type="button" class="zvcal-btn is-icon" @click="move(-1)" :aria-label="T.previous[view]" :title="T.previous[view]">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button type="button" class="zvcal-btn is-icon" @click="move(1)" :aria-label="T.next[view]" :title="T.next[view]">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            </button>
            <button type="button" class="zvcal-btn" @click="goToday()">{{ __('calendar.today') }}</button>
        </div>
        <h2 class="zvcal-heading" x-text="heading" aria-live="polite"></h2>
        <div class="zvcal-views" role="group" aria-label="{{ __('calendar.view') }}">
            @foreach (['month', 'week', 'day'] as $name)
                <button type="button" @click="setView('{{ $name }}')" :aria-pressed="view === '{{ $name }}' ? 'true' : 'false'">{{ __('calendar.' . $name) }}</button>
            @endforeach
        </div>
    </div>

    <div class="zvcal-legend">
        <span><i class="zvcal-key is-req"></i>{{ __('efd.required') }}</span>
        <span><i class="zvcal-key is-opt"></i>{{ __('efd.optional') }}</span>
        <span><i class="zvcal-key is-done"></i>{{ __('calendar.done') }}</span>
        <span><i class="zvcal-key is-today"></i>{{ __('calendar.today') }}</span>
        {{-- hybrid classes: inform, don't explain (Daniel, 2026-10-30) --}}
        @unless ($hybrid)
            <span class="zvcal-hint" x-show="view === 'month'">{{ __('calendar.hint_month') }}</span>
            <span class="zvcal-hint" x-show="view === 'week'" x-cloak>{{ __('calendar.hint_week') }}</span>
        @endunless
    </div>

    {{-- Month: each activity on its last day --}}
    <div class="zvcal-frame" x-show="view === 'month'" x-cloak>
        <div class="zvcal-names">
            <template x-for="name in weekNames" :key="name"><div x-text="name"></div></template>
        </div>
        <template x-for="week in monthWeeks" :key="week.key">
            <div class="zvcal-row">
                <template x-for="day in week.days" :key="day.date">
                    <div class="zvcal-cell" :class="{ 'is-out': day.out, 'is-today': day.today }">
                        <div class="zvcal-top">
                            <button type="button" class="zvcal-more" x-show="day.more" @click="openDay(day.date)" x-text="say(T.more, { count: day.more })"></button>
                            <button type="button" class="zvcal-num" @click="openDay(day.date)" :aria-label="dayLabel(day.date) + (day.todo ? ', ' + say(T.to_do_count, { count: day.todo }) : '')">
                                <span class="zvcal-todo" x-show="day.todo" x-text="say(T.to_do_count, { count: day.todo })"></span>
                                <span class="zvcal-n" x-text="day.num"></span>
                            </button>
                        </div>
                        <template x-for="item in day.shown" :key="item.key">
                            <a class="zvcal-pill" :class="'is-' + kind(item)" :href="item.url || '#'" :title="item.title" @click="follow($event, item, day.date)">
                                <svg x-show="!item.done" width="9" height="9" viewBox="0 0 10 10" aria-hidden="true"><path d="M5 0L10 5L5 10L0 5Z" fill="currentColor"/></svg>
                                <svg x-show="item.done" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                <span x-text="item.title"></span>
                            </a>
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Week: one bar per activity across the days it runs, and what happens each day --}}
    <div class="zvcal-frame" x-show="view === 'week'" x-cloak>
        <div class="zvcal-weekhead">
            <template x-for="day in weekDays" :key="day.date">
                <button type="button" class="zvcal-wday" :class="{ 'is-today': day.today }" @click="openDay(day.date)" :aria-label="dayLabel(day.date)">
                    <span class="zvcal-wname" x-text="day.name"></span>
                    <span class="zvcal-wnum" x-text="day.num"></span>
                </button>
            </template>
        </div>
        <div class="zvcal-bars">
            <template x-for="bar in weekBars" :key="bar.key">
                <div class="zvcal-barcell" :style="'grid-column: ' + bar.col + '; grid-row: ' + bar.row">
                    <a class="zvcal-wbar" :class="'is-' + kind(bar.item)" :href="bar.item.url || '#'" :title="bar.item.title" @click="follow($event, bar.item, bar.item.last)">
                        <svg x-show="bar.item.done" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                        <span class="zvcal-wbar-title" x-text="bar.item.title"></span>
                        <span class="zvcal-wbar-tail" x-text="bar.tail"></span>
                    </a>
                </div>
            </template>
            <p class="zvcal-empty" x-show="!weekBars.length">{{ __('calendar.nothing_week') }}</p>
        </div>
        <div class="zvcal-notes">
            <template x-for="day in weekDays" :key="'notes-' + day.date">
                <div class="zvcal-notecol" :class="{ 'is-empty': !day.notes.length }">
                    <div class="zvcal-notedate" x-text="dayLabel(day.date)"></div>
                    <template x-for="note in day.notes" :key="note.key">
                        <div class="zvcal-note"><strong x-text="note.head"></strong><br><span x-text="note.text"></span></div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- Day: what to do, what is done, what comes next --}}
    <div class="zvcal-dayview" x-show="view === 'day'" x-cloak>
        <div class="zvcal-daymain">
            <h3 class="zvcal-sub">{{ __('calendar.to_do') }} <span x-text="'· ' + dayTodo.length"></span></h3>
            <template x-for="item in dayTodo" :key="item.key">
                <div class="zvcal-card">
                    <div class="zvcal-card-top">
                        <span class="zvcal-card-title" x-text="item.title" :title="item.title"></span>
                        <span class="zvcal-chip" :class="item.required ? 'is-req' : 'is-opt'" x-text="item.required ? T.required_one : T.optional_one"></span>
                    </div>
                    <template x-if="item.url"><a class="zvcal-open" :href="item.url">{{ __('calendar.open') }}</a></template>
                    <div class="zvcal-card-time">
                        <div class="zvcal-track" aria-hidden="true"><div :style="'width: ' + item.used + '%'"></div></div>
                        <strong x-text="item.left"></strong>
                        <span class="zvcal-last" x-text="item.lastText"></span>
                    </div>
                </div>
            </template>
            <p class="zvcal-empty" x-show="!dayTodo.length">{{ __('calendar.nothing_day') }}</p>

            <h3 class="zvcal-sub" x-show="dayDone.length">{{ __('calendar.done') }} <span x-text="'· ' + dayDone.length"></span></h3>
            <template x-for="item in dayDone" :key="item.key">
                <div class="zvcal-doneline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                    <span class="zvcal-done-title" x-text="item.title"></span>
                    <span class="zvcal-done-when" x-text="item.when"></span>
                </div>
            </template>
        </div>

        {{-- a div, not an aside: the layout styles every aside as its sidebar --}}
        <div class="zvcal-dayside">
            <h3 class="zvcal-sub">{{ __('calendar.coming_up') }}</h3>
            <template x-for="item in daySoon" :key="item.key">
                <div class="zvcal-soon">
                    <div class="zvcal-soon-title" x-text="item.title"></div>
                    <div class="zvcal-soon-when" x-text="item.when"></div>
                </div>
            </template>
            <p class="zvcal-empty" x-show="!daySoon.length">{{ __('calendar.nothing_soon') }}</p>
            @if ($fineAmount > 0)
                <div class="zvcal-fine">{{ __('efd.fine_note', ['amount' => zeds($fineAmount)]) }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $texts = __('calendar');
    // the same words as the Education department board; trans_choice always puts
    // the number in :count, so the plural is taken with 99 and given its :count back
    $texts['days_left'] = str_replace('99', ':count', trans_choice('efd.days_left', 99));
    $texts['last_day_today'] = __('efd.last_day');
@endphp
<script>
window.zvCalendarConfig = {{ \Illuminate\Support\Js::from($calendar + ['texts' => $texts]) }};

function zvCalendar(config) {
    const DAY = 86400000;
    // dates are 'YYYY-MM-DD', worked out at midnight UTC so no clock change moves them
    const parse = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(Date.UTC(y, m - 1, d)); };
    const iso = (d) => d.toISOString().slice(0, 10);
    const add = (d, n) => new Date(d.getTime() + n * DAY);
    const between = (a, b) => Math.round((parse(b) - parse(a)) / DAY);
    const fmt = (d, options) => new Intl.DateTimeFormat(config.locale, Object.assign({ timeZone: 'UTC' }, options)).format(typeof d === 'string' ? parse(d) : d);
    const capital = (s) => s.charAt(0).toLocaleUpperCase(config.locale) + s.slice(1);
    const T = config.texts;

    return {
        T,
        view: 'month',
        today: config.today,
        cursor: config.today,
        items: config.activities.map((item, n) => Object.assign({ key: 'a' + n }, item)),
        height: 600,
        narrow: false,

        init() {
            try {
                const saved = localStorage.getItem('zvcal.view');
                if (['month', 'week', 'day'].includes(saved)) this.view = saved;
            } catch (e) {}
            this.$nextTick(() => this.fit());
            window.addEventListener('resize', () => this.fit());
        },
        // the whole calendar on the screen, without scrolling (Daniel: "you would not to scroll up and down all the time")
        fit() {
            this.height = Math.max(520, window.innerHeight - this.$root.getBoundingClientRect().top - 24);
            this.$root.style.height = this.height + 'px';
            this.narrow = window.innerWidth <= 760;
        },
        say(text, values) {
            return Object.keys(values).reduce((t, k) => t.split(':' + k).join(values[k]), text);
        },
        setView(view) {
            this.view = view;
            try { localStorage.setItem('zvcal.view', view); } catch (e) {}
        },
        move(step) {
            const c = parse(this.cursor);
            this.cursor = this.view === 'month'
                ? iso(new Date(Date.UTC(c.getUTCFullYear(), c.getUTCMonth() + step, 1)))
                : iso(add(c, step * (this.view === 'week' ? 7 : 1)));
        },
        goToday() { this.cursor = this.today; },
        openDay(date) { this.cursor = date; this.setView('day'); },
        // an activity without a page of its own opens its day
        follow(event, item, date) {
            if (!item.url) { event.preventDefault(); this.openDay(date); }
        },
        kind(item) { return item.done ? 'done' : (item.required ? 'req' : 'opt'); },
        running(date) { return this.items.filter((i) => i.start <= date && date <= i.last); },
        todo(date) { return this.running(date).filter((i) => !i.done); },
        dayLabel(date) { return capital(fmt(date, { weekday: 'long', day: 'numeric', month: 'long' })); },

        get heading() {
            if (this.view === 'month') return capital(fmt(this.cursor, { month: 'long', year: 'numeric' }));
            if (this.view === 'day') return capital(fmt(this.cursor, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
            const f = new Intl.DateTimeFormat(config.locale, { timeZone: 'UTC', day: 'numeric', month: 'long', year: 'numeric' });
            const from = this.weekStart, to = add(this.weekStart, 6);
            return f.formatRange ? f.formatRange(from, to) : f.format(from) + ' – ' + f.format(to);
        },

        // weeks start on Monday, as in Spanish, French and German schools
        get weekNames() {
            const monday = parse('2026-11-02');
            return [0, 1, 2, 3, 4, 5, 6].map((i) => fmt(add(monday, i), { weekday: 'short' }));
        },
        get monthWeeks() {
            const c = parse(this.cursor);
            const month = c.getUTCMonth();
            let day = new Date(Date.UTC(c.getUTCFullYear(), month, 1));
            day = add(day, -((day.getUTCDay() + 6) % 7));
            const weeks = [];
            do {
                const days = [];
                for (let i = 0; i < 7; i++, day = add(day, 1)) {
                    const date = iso(day);
                    const out = day.getUTCMonth() !== month;
                    // the other months' days only show their number, as in the design
                    days.push({ date, num: day.getUTCDate(), out, today: date === this.today,
                        due: out ? [] : this.items.filter((item) => item.last === date), todo: date === this.today ? this.todo(date).length : 0 });
                }
                weeks.push({ key: days[0].date, days });
            } while (day.getUTCMonth() === month);

            // as many 20px pills as fit under the day's number; the rest behind "+N" beside it
            // (a phone shows them all, as dots)
            const row = (this.height - 116) / weeks.length - 1;
            const fits = this.narrow ? Infinity : Math.max(0, Math.floor((row - 36) / 23));
            weeks.forEach((week) => week.days.forEach((d) => {
                d.shown = d.due.slice(0, fits);
                d.more = d.due.length - d.shown.length;
            }));

            return weeks;
        },

        get weekStart() {
            const c = parse(this.cursor);
            return add(c, -((c.getUTCDay() + 6) % 7));
        },
        get weekDays() {
            return [0, 1, 2, 3, 4, 5, 6].map((i) => {
                const d = add(this.weekStart, i);
                const date = iso(d);
                return { date, name: fmt(d, { weekday: 'short' }), num: d.getUTCDate(), today: date === this.today, notes: this.notes(date) };
            });
        },
        get weekBars() {
            const first = iso(this.weekStart), last = iso(add(this.weekStart, 6));
            return this.items
                .filter((i) => i.start <= last && i.last >= first)
                .sort((a, b) => a.start.localeCompare(b.start) || a.last.localeCompare(b.last))
                .map((i, n) => {
                    const from = i.start < first ? 0 : between(first, i.start);
                    const to = i.last > last ? 6 : between(first, i.last);
                    return {
                        key: i.key, item: i, row: n + 1, col: (from + 1) + ' / ' + (to + 2),
                        tail: i.last > last
                            ? this.say(T.until, { date: fmt(i.last, { day: 'numeric', month: 'short' }) })
                            : fmt(i.last, { weekday: 'short', day: 'numeric' }),
                    };
                });
        },
        notes(date) {
            const notes = [];
            const starting = this.items.filter((i) => i.start === date);
            if (starting.length) notes.push({ key: 'new', head: T.new, text: starting.map((i) => i.title).join(', ') });
            if (date === this.today) notes.push({ key: 'today', head: T.today, text: this.say(T.to_do_count, { count: this.todo(date).length }) });
            const ending = this.items.filter((i) => i.last === date);
            if (ending.length) notes.push({ key: 'last', head: T.last_day, text: ending.map((i) => i.title + (i.done ? ' (' + T.done_one + ')' : '')).join(', ') });
            return notes;
        },

        get dayTodo() {
            const date = this.cursor;
            return this.todo(date)
                .sort((a, b) => a.last.localeCompare(b.last))
                .map((i) => {
                    // the days left, that day included, as on the board (App\Support\Deadline)
                    const left = between(date, i.last) + 1;
                    return Object.assign({}, i, {
                        used: Math.round(100 * between(i.start, date) / (between(i.start, i.last) + 1)),
                        left: left === 1
                            ? (date === this.today ? T.last_day_today : T.last_day)
                            : this.say(T.days_left, { count: left }),
                        lastText: this.say(T.last_day_on, { date: fmt(i.last, { day: 'numeric', month: 'short' }) }),
                    });
                });
        },
        get dayDone() {
            return this.running(this.cursor)
                .filter((i) => i.done)
                .map((i) => Object.assign({}, i, {
                    when: !i.done_on ? '' : (i.done_on === this.today ? T.today_lc : fmt(i.done_on, { weekday: 'short', day: 'numeric', month: 'short' })),
                }));
        },
        get daySoon() {
            return this.items
                .filter((i) => i.start > this.cursor)
                .sort((a, b) => a.start.localeCompare(b.start))
                .slice(0, 4)
                .map((i) => Object.assign({}, i, {
                    when: this.say(T.starts_on, { date: fmt(i.start, { day: 'numeric', month: 'long' }) }) + ' · ' + (i.required ? T.required_one : T.optional_one),
                }));
        },
    };
}
</script>
@endpush
