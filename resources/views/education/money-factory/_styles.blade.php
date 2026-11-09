{{-- The Money Factory's look (2026-11-02): the app's green and cards, a dark board for the live rates. --}}
@once
<style>
    .zm { display: flex; flex-direction: column; gap: 16px; }
    .zm-card { background: #fff; border: 1px solid #D2DDDB; border-radius: 14px; padding: 18px 20px; }
    .zm-card h2, .zm-h2 { display: flex; align-items: center; gap: 8px; font: 800 18px/1.3 "Manrope", sans-serif; color: #0F2A22; margin: 0 0 10px; text-transform: none; }
    .zm-card p { margin: 0 0 10px; font-size: 15px; line-height: 1.5; color: #33443E; }
    .zm-muted { color: #5E7069; font-size: 14px; line-height: 1.45; }
    .zm-small { font-size: 13px; color: #5E7069; line-height: 1.45; }
    .zm-grid { display: grid; gap: 16px; }
    .zm-grid--3 { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
    .zm-grid--2 { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
    .zm-ico { font-size: 22px; line-height: 1; }

    /* the hero: Zedland's capital prints its money */
    .zm-hero { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); align-items: center; gap: 12px 24px; padding: 24px 28px;
        border-radius: 18px; color: #fff; background: linear-gradient(135deg, #014A38 0%, #016950 55%, #00A47D 100%); overflow: hidden; }
    .zm-kicker { margin: 0 0 4px; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #9BE3C9; }
    .zm-hero h1 { margin: 0 0 8px; font: 800 32px/1.15 "Manrope", sans-serif; color: #fff; }
    .zm-lead { margin: 0 0 16px; font-size: 17px; line-height: 1.5; color: #E6F6F0; max-width: 560px; }
    .zm-facts { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; }
    .zm-facts div { padding: 7px 14px; border-radius: 999px; background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .25); }
    .zm-facts dt { display: inline; font-size: 13px; color: #BFEBDC; }
    .zm-facts dt::after { content: ": "; }
    .zm-facts dd { display: inline; margin: 0; font-weight: 800; font-size: 14px; color: #fff; }
    .zm-hero-art svg { display: block; width: 100%; max-width: 360px; height: auto; margin-left: auto; }
    @media (max-width: 760px) { .zm-hero { grid-template-columns: 1fr; padding: 20px; } .zm-hero-art svg { max-width: 260px; margin: 0 auto; } .zm-hero h1 { font-size: 26px; } }

    .zm-points { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 8px; }
    .zm-points li { display: flex; gap: 10px; align-items: flex-start; font-size: 14px; line-height: 1.45; color: #33443E; }
    .zm-points li > span { flex: none; width: 28px; height: 28px; display: grid; place-items: center; border-radius: 8px; background: #EEF9F5; font-size: 15px; }
    .zm-link { display: inline-block; margin-top: 12px; font-weight: 700; color: #016950; }
    .zm-link:hover { text-decoration: underline; }

    /* how a banknote is made: numbered steps */
    .zm-steps { margin: 0; padding: 0; list-style: none; counter-reset: zm-step; display: flex; flex-direction: column; gap: 10px; }
    .zm-steps li { counter-increment: zm-step; display: grid; grid-template-columns: 30px 1fr; gap: 2px 10px; font-size: 14px; line-height: 1.4; color: #33443E; }
    .zm-steps li::before { content: counter(zm-step); grid-row: span 2; width: 28px; height: 28px; display: grid; place-items: center; border-radius: 99px;
        background: #00A47D; color: #fff; font-weight: 800; font-size: 13px; }
    .zm-steps strong { color: #0F2A22; }

    /* the exchange */
    .zm-ex-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 900px) { .zm-ex-grid { grid-template-columns: 1fr; } }
    .zm-board { border-radius: 14px; padding: 14px 16px 12px; background: #0B231C; color: #E6F6F0; box-shadow: inset 0 0 0 1px #16382E; }
    .zm-board-head { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
    .zm-live { display: inline-flex; align-items: center; gap: 6px; padding: 3px 9px; border-radius: 99px; background: rgba(255, 82, 82, .14);
        color: #FF8A80; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .zm-live::before { content: ""; width: 8px; height: 8px; border-radius: 99px; background: #FF5252; animation: zm-pulse 1.4s ease-in-out infinite; }
    @keyframes zm-pulse { 50% { opacity: .25; } }
    .zm-board-title { font: 800 15px/1.3 "Manrope", sans-serif; color: #fff; }
    .zm-clock { margin-left: auto; font: 600 13px/1 ui-monospace, SFMono-Regular, Menlo, monospace; color: #8FB8AA; font-variant-numeric: tabular-nums; }
    .zm-rows { display: flex; flex-direction: column; }
    .zm-rate-row { display: grid; grid-template-columns: 52px minmax(0, 1fr) auto 86px; align-items: center; gap: 10px; padding: 8px 6px; border-radius: 8px;
        border-bottom: 1px solid rgba(255, 255, 255, .06); cursor: pointer; text-align: left; width: 100%; background: transparent; color: inherit; transition: background .6s; }
    .zm-rate-row:hover, .zm-rate-row.is-picked { background: rgba(255, 255, 255, .07); }
    .zm-rate-row.is-up { background: rgba(52, 211, 153, .18); transition: none; }
    .zm-rate-row.is-down { background: rgba(248, 113, 113, .16); transition: none; }
    .zm-rate-head { cursor: default; padding-top: 0; padding-bottom: 6px; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #8FB8AA; }
    .zm-rate-head:hover { background: transparent; }
    .zm-rate-head span:nth-child(3), .zm-rate-head span:nth-child(4) { text-align: right; }
    .zm-rate-row .zm-val { text-align: right; }
    .zm-code { font: 800 13px/1 ui-monospace, SFMono-Regular, Menlo, monospace; padding: 5px 0; text-align: center; border-radius: 6px; background: #16382E; color: #9BE3C9; }
    .zm-cur { font-size: 14px; color: #CFE7DE; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .zm-val { font: 700 16px/1 ui-monospace, SFMono-Regular, Menlo, monospace; color: #fff; font-variant-numeric: tabular-nums; }
    .zm-chg { font: 700 12px/1 ui-monospace, SFMono-Regular, Menlo, monospace; text-align: right; font-variant-numeric: tabular-nums; }
    .zm-chg.is-up { color: #34D399; } .zm-chg.is-down { color: #F87171; }
    .zm-board .zm-board-note { margin: 10px 0 0; font-size: 12px; line-height: 1.45; color: #9FC4B7; }
    @media (prefers-reduced-motion: reduce) { .zm-live::before { animation: none; } .zm-rate-row { transition: none; } }
    /* a phone: the name without its symbol, no "Currency" heading */
    @media (max-width: 520px) {
        .zm-board { padding: 12px 10px 10px; }
        .zm-rate-row { grid-template-columns: 42px minmax(0, 1fr) auto 66px; gap: 6px; padding: 8px 4px; }
        .zm-sym, .zm-rate-head span:nth-child(2) { display: none; }
        .zm-rate-head span:nth-child(2) + span { grid-column: 3; }
        .zm-cur { font-size: 13px; }
        .zm-val { font-size: 14px; }
    }

    .zm-form { border: 1px solid #D2DDDB; border-radius: 14px; padding: 16px; background: #F7FAF9; }
    .zm-form label { display: block; margin: 0 0 4px; font-size: 13px; font-weight: 700; color: #33443E; }
    .zm-form select, .zm-form input { width: 100%; padding: 9px 12px; border: 1px solid #C9D6D2; border-radius: 10px; background: #fff; font-size: 15px; color: #0F2A22; }
    .zm-field { margin-bottom: 12px; }
    .zm-sum { margin: 4px 0 12px; display: grid; grid-template-columns: 1fr auto; gap: 6px 12px; font-size: 14px; }
    .zm-sum dt { color: #44564F; }
    .zm-sum dd { margin: 0; text-align: right; font-weight: 700; color: #0F2A22; font-variant-numeric: tabular-nums; }
    .zm-sum .is-big { font-size: 18px; color: #016950; }
    .zm-sum .is-total { padding-top: 8px; border-top: 1px solid #DDE6E3; }
    .zm-btn { width: 100%; padding: 11px 16px; border: 0; border-radius: 999px; background: #00A47D; color: #fff; font-weight: 800; font-size: 15px; cursor: pointer; }
    .zm-btn:hover { background: #016950; }
    .zm-btn:disabled { opacity: .6; cursor: not-allowed; }
    .zm-btn--ghost { background: #fff; color: #016950; border: 1px solid #B9D3CB; }
    .zm-btn--ghost:hover { background: #EEF9F5; }
    .zm-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .zm-error { margin: 0 0 10px; padding: 8px 12px; border-radius: 10px; background: #FDECEC; border: 1px solid #F3B9BC; color: #8E1F25; font-size: 14px; }
    .zm-done { padding: 14px; border-radius: 12px; background: #E2F6EF; border: 1px solid #A7E3CD; color: #014A38; font-size: 15px; line-height: 1.45; margin-bottom: 12px; }
    .zm-done strong { display: block; font-size: 17px; margin-bottom: 4px; }

    .zm-tasks { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; margin-bottom: 16px; }
    .zm-task { border: 1px solid #F3D9A6; border-left: 5px solid #F59E0B; border-radius: 12px; padding: 12px 14px; background: #FFFBF2; }
    .zm-task.is-done { border-color: #A7E3CD; border-left-color: #00A47D; background: #F2FBF7; }
    .zm-task-tag { margin: 0 0 4px; font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #9A6400; }
    .zm-task.is-done .zm-task-tag { color: #017A5C; }
    .zm-task h3 { margin: 0 0 6px; font: 800 16px/1.3 "Manrope", sans-serif; color: #0F2A22; text-transform: none; }
    .zm-task p { margin: 0 0 8px; font-size: 14px; line-height: 1.45; color: #33443E; }
    .zm-task-goal { font-weight: 700; color: #0F2A22 !important; }
    .zm-task .zm-btn { width: auto; padding: 7px 16px; font-size: 14px; }

    .zm-history { margin: 16px 0 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: 8px; }
    .zm-history li { padding: 6px 12px; border-radius: 999px; background: #EEF3F1; font-size: 13px; color: #33443E; font-variant-numeric: tabular-nums; }

    /* the banknote tracker */
    .zm-tracker { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 20px; align-items: start; }
    @media (max-width: 760px) { .zm-tracker { grid-template-columns: 1fr; } }
    .zm-note { position: relative; height: 112px; border-radius: 10px; border: 2.5px solid #0E7C5F; background: repeating-radial-gradient(circle at 30% 50%, #F4FBF8 0 6px, #E4F5EE 6px 7px);
        box-shadow: 0 6px 16px rgba(15, 42, 34, .12); overflow: hidden; }
    .zm-note::after { content: ""; position: absolute; top: 0; bottom: 0; right: 64px; width: 12px; background: linear-gradient(#C4B5FD, #93C5FD, #6EE7B7, #FDE68A); opacity: .8; }
    .zm-note-z { position: absolute; left: 14px; top: 22px; width: 52px; height: 52px; display: grid; place-items: center; border-radius: 99px; background: #DFF5EC;
        border: 2px solid #0E7C5F; font: 800 28px/1 "Manrope", sans-serif; color: #0E7C5F; }
    .zm-note-v { position: absolute; right: 12px; top: 8px; font: 800 26px/1 "Manrope", sans-serif; color: #0E7C5F; }
    .zm-note-serial { position: absolute; left: 14px; bottom: 8px; font: 700 13px/1 ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing: .06em; color: #0E5C47; }
    .zm-scan-other { margin-top: 14px; }
    .zm-scan-other input { flex: 1; min-width: 0; padding: 8px 10px; border: 1px solid #C9D6D2; border-radius: 10px; font: 600 14px/1.2 ui-monospace, SFMono-Regular, Menlo, monospace; }
    .zm-scanning { display: flex; align-items: center; gap: 10px; padding: 14px; border-radius: 12px; background: #0B231C; color: #9BE3C9; font-weight: 700; }
    .zm-scan-beam { width: 60px; height: 6px; border-radius: 99px; background: linear-gradient(90deg, transparent, #34D399, transparent); animation: zm-beam 0.9s ease-in-out infinite; }
    @keyframes zm-beam { 50% { transform: translateX(30px); } }
    .zm-journey-title { margin: 0 0 10px; font: 800 16px/1.3 "Manrope", sans-serif; color: #0F2A22; text-transform: none; }
    .zm-journey { margin: 0 0 16px; padding: 0; list-style: none; position: relative; }
    .zm-journey::before { content: ""; position: absolute; left: 17px; top: 8px; bottom: 8px; width: 2px; background: #D2DDDB; }
    .zm-journey li { position: relative; display: grid; grid-template-columns: 36px minmax(0, 1fr); gap: 0 10px; padding: 6px 0; }
    .zm-journey-ico { grid-row: span 3; align-self: start; width: 36px; height: 36px; display: grid; place-items: center; border-radius: 99px; background: #fff; border: 2px solid #D2DDDB; font-size: 17px; z-index: 1; }
    .zm-journey li.is-shop .zm-journey-ico, .zm-journey li.is-person .zm-journey-ico { border-color: #00A47D; }
    .zm-journey li.is-you .zm-journey-ico { border-color: #F59E0B; background: #FFFBF2; }
    .zm-journey-name { font-weight: 700; font-size: 14px; color: #0F2A22; }
    .zm-journey-when { font-size: 13px; color: #5E7069; }
    .zm-journey-when b { color: #33443E; }
    /* how it went on to the next hand (2026-11-02) */
    .zm-journey-passed { grid-column: 2; margin-top: 2px; font-size: 13px; font-style: italic; color: #016950; }
    .zm-journey-average { margin: 0 0 14px !important; padding: 10px 12px; border-radius: 12px; background: #F7FAF9; font-size: 14px !important; }
    .zm-journey-average > span:first-of-type { font-weight: 800; color: #0F2A22; }
    .zm-journey-average .zm-muted { display: block; margin-top: 2px; font-size: 13px; }
    .zm-real-trackers { margin-top: 14px; padding: 10px 12px; border-radius: 12px; background: #EEF9F5; border: 1px solid #CDEBDD; }
    .zm-real-trackers a { display: block; margin-top: 4px; font-size: 14px; font-weight: 700; color: #016950; text-decoration: underline; }
    .zm-quiz { display: flex; flex-direction: column; gap: 12px; padding-top: 12px; border-top: 1px solid #E6ECEA; }
    .zm-q { margin: 0; padding: 10px 12px; border: 1px solid #D2DDDB; border-radius: 12px; }
    .zm-q legend { padding: 0 4px; font-weight: 700; font-size: 14px; color: #0F2A22; }
    .zm-q.is-right { border-color: #A7E3CD; background: #F2FBF7; }
    .zm-q.is-wrong { border-color: #F3B9BC; background: #FFF8F7; }
    .zm-opt { display: flex; align-items: center; gap: 8px; padding: 4px 2px; font-size: 14px; color: #33443E; cursor: pointer; }
    .zm-opt input { width: auto; accent-color: #00A47D; }
    .zm-q-mark { margin: 4px 0 0 !important; font-size: 13px !important; font-weight: 700; }
    .zm-q.is-right .zm-q-mark { color: #017A5C !important; }
    .zm-q.is-wrong .zm-q-mark { color: #8E1F25 !important; }

    /* find out more */
    .zm-more { display: flex; gap: 14px; align-items: center; padding: 16px 18px; border: 1px solid #D2DDDB; border-radius: 14px; background: #fff; transition: border-color .2s, box-shadow .2s; }
    .zm-more:hover { border-color: #00A47D; box-shadow: 0 8px 22px rgba(15, 42, 34, .10); }
    .zm-more-ico { flex: none; width: 46px; height: 46px; display: grid; place-items: center; border-radius: 12px; background: #EEF9F5; font-size: 24px; }
    .zm-more strong { display: block; font: 800 16px/1.3 "Manrope", sans-serif; color: #0F2A22; }
    .zm-more span { font-size: 14px; color: #5E7069; }
    .zm-badge { display: inline-block; margin-left: 6px; padding: 1px 8px; border-radius: 999px; background: #EDE9FE; color: #5B21B6; font-size: 12px;
        font-weight: 800; letter-spacing: .04em; text-transform: uppercase; vertical-align: middle; }

    /* the pages apart */
    .zm-page-head { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .zm-page-head h1 { margin: 0; font: 800 26px/1.2 "Manrope", sans-serif; color: #0F2A22; }
    .zm-crumb { font-size: 14px; font-weight: 700; color: #016950; }
    .zm-sources { margin: 0; padding-left: 18px; font-size: 13px; color: #5E7069; line-height: 1.6; list-style: disc; }
    .zm-sources a { color: #016950; text-decoration: underline; }
    .zm-table { width: 100%; font-size: 14px; border-collapse: collapse; margin-top: 8px; }
    .zm-table th, .zm-table td { padding: 6px 8px; border-bottom: 1px solid #EEF3F1; text-align: left; }
    .zm-table td.num, .zm-table th.num { text-align: right; font-variant-numeric: tabular-nums; }
    details.zm-details > summary { cursor: pointer; font-size: 14px; font-weight: 700; color: #016950; margin-top: 10px; }

    /* the picture to explore (2026-11-02): places that glow and say their name, windows over the page */
    .zm-hero--text { grid-template-columns: 1fr; }
    .zm-scene { position: relative; border-radius: 14px; overflow: hidden; border: 1px solid #D2DDDB; background: #F7FCFA; }
    .zm-scene-art { display: block; width: 100%; height: auto; }
    .zm-spot { position: absolute; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; gap: 6px;
        padding: 0; border: 0; background: transparent; cursor: pointer; z-index: 1; }
    .zm-spot-dot { position: relative; width: 46px; height: 46px; display: grid; place-items: center; border-radius: 99px; font-size: 22px;
        background: rgba(255, 255, 255, .92); border: 3px solid #00A47D; box-shadow: 0 4px 14px rgba(15, 42, 34, .25); transition: transform .2s, background .2s; }
    .zm-spot-dot::after { content: ""; position: absolute; inset: -9px; border-radius: 99px; border: 3px solid #00A47D; opacity: 0; animation: zm-ring 2.4s ease-out infinite; }
    @keyframes zm-ring { 0% { transform: scale(.8); opacity: .7; } 70%, 100% { transform: scale(1.35); opacity: 0; } }
    .zm-spot-label { position: absolute; top: calc(100% + 6px); left: 50%; transform: translate(-50%, -4px); white-space: nowrap; padding: 6px 12px; border-radius: 999px;
        background: #0F2A22; color: #fff; font-weight: 800; font-size: 14px; box-shadow: 0 6px 16px rgba(15, 42, 34, .3); opacity: 0; pointer-events: none; transition: opacity .15s, transform .15s; }
    .zm-spot:hover .zm-spot-dot, .zm-spot:focus-visible .zm-spot-dot { transform: scale(1.15); background: #FDE68A; }
    .zm-spot:hover .zm-spot-label, .zm-spot:focus-visible .zm-spot-label { opacity: 1; transform: translate(-50%, 0); }
    .zm-spot:focus-visible { outline: none; }
    .zm-spot:focus-visible .zm-spot-dot { outline: 3px solid #1E3A8A; outline-offset: 3px; }
    /* near the bottom a name opens upwards, near the right edge to the left */
    .zm-spot.is-up .zm-spot-label { top: auto; bottom: calc(100% + 6px); }
    .zm-spot.is-right .zm-spot-label { left: auto; right: 0; transform: translate(0, -4px); }
    .zm-spot.is-right:hover .zm-spot-label, .zm-spot.is-right:focus-visible .zm-spot-label { transform: translate(0, 0); }
    @media (prefers-reduced-motion: reduce) { .zm-spot-dot::after { animation: none; } }
    @media (max-width: 640px) { .zm-spot-dot { width: 32px; height: 32px; font-size: 16px; border-width: 2px; } .zm-spot-label { display: none; } }
    .zm-spot-list { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0 0; padding: 0; list-style: none; }
    .zm-spot-list button { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 999px; border: 1px solid #B9D3CB; background: #fff;
        color: #016950; font-weight: 700; font-size: 14px; cursor: pointer; }
    .zm-spot-list button:hover { background: #EEF9F5; }
    .zm-pop-back { position: fixed; inset: 0; z-index: 60; display: grid; place-items: center; padding: 16px; background: rgba(11, 35, 28, .55); }
    .zm-pop { position: relative; width: min(620px, 100%); max-height: calc(100vh - 32px); overflow-y: auto; padding: 22px 22px 18px; border-radius: 18px; background: #fff;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .3); }
    .zm-pop h2 { display: flex; align-items: center; gap: 8px; margin: 0 36px 12px 0; font: 800 20px/1.3 "Manrope", sans-serif; color: #0F2A22; text-transform: none; }
    .zm-pop p { margin: 0 0 12px; font-size: 15px; line-height: 1.5; color: #33443E; }
    .zm-pop-close { position: absolute; top: 12px; right: 12px; width: 36px; height: 36px; border-radius: 99px; border: 1px solid #D2DDDB; background: #fff; font-size: 16px; cursor: pointer; }
    .zm-pop-close:hover { background: #EEF3F1; }
    .zm-functions { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .zm-functions li { display: flex; gap: 12px; align-items: flex-start; padding: 10px 12px; border-radius: 12px; background: #F7FAF9; font-size: 15px; line-height: 1.45; color: #33443E; }
    .zm-functions strong { display: block; color: #0F2A22; }
    .zm-functions-ico { flex: none; width: 38px; height: 38px; display: grid; place-items: center; border-radius: 10px; background: #EEF9F5; font-size: 20px; }
    .zm-says { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 12px; }
    .zm-says-who { flex: none; width: 56px; height: 56px; display: grid; place-items: center; border-radius: 99px; background: #E0E7FF; font-size: 30px; }
    .zm-says-text { position: relative; margin: 0 !important; padding: 12px 14px; border-radius: 14px; background: #F1F5FF; border: 1px solid #C7D2FE; }

    /* the activities' pages (2026-11-02): a headline or situation, figures as chips, why once right */
    .zm-q-lead { margin: 6px 0 8px !important; padding: 10px 14px; border-left: 4px solid #0F2A22; background: #F7F5EE; font: 700 16px/1.35 Georgia, "Times New Roman", serif !important; color: #0F2A22 !important; }
    .zm-chips { display: flex; flex-wrap: wrap; gap: 6px; margin: 0 0 8px; }
    .zm-chip { padding: 4px 10px; border-radius: 999px; background: #EEF3F1; font-size: 13px; color: #33443E; }
    .zm-chip b { color: #0F2A22; font-variant-numeric: tabular-nums; }
    .zm-q-why { margin: 4px 0 0 !important; padding: 8px 10px; border-radius: 10px; background: #fff; font-size: 14px !important; color: #33443E !important; }
    .zm-order { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px; margin-top: 4px; }
    .zm-order-card { position: relative; display: flex; flex-direction: column; align-items: flex-start; gap: 2px; padding: 10px 10px 10px 12px; border: 1px solid #D2DDDB;
        border-radius: 12px; background: #fff; text-align: left; cursor: pointer; font-size: 14px; color: #0F2A22; }
    .zm-order-card:hover { border-color: #00A47D; }
    .zm-order-card.is-placed { border-color: #00A47D; background: #F2FBF7; }
    .zm-order-num { position: absolute; top: 8px; right: 8px; width: 26px; height: 26px; display: grid; place-items: center; border-radius: 99px; background: #EEF3F1; color: #5E7069; font-weight: 800; font-size: 13px; }
    .zm-order-card.is-placed .zm-order-num { background: #00A47D; color: #fff; }
    .zm-order-ico { font-size: 26px; line-height: 1.1; }
    .zm-order-text { font-size: 13px; color: #44564F; line-height: 1.35; }
    .zm-order-when { margin-top: 4px; font-size: 13px; font-style: normal; font-weight: 700; color: #016950; }

    /* real or fake: the three checks and the banknotes */
    .zm-checks { display: flex; flex-wrap: wrap; gap: 8px; }
    .zm-check { padding: 9px 16px; border-radius: 999px; border: 1px solid #B9D3CB; background: #fff; color: #016950; font-weight: 800; font-size: 15px; cursor: pointer; }
    .zm-check:hover { background: #EEF9F5; }
    .zm-check.is-on { background: #00A47D; border-color: #00A47D; color: #fff; }
    .zm-check-text { margin: 10px 0 0 !important; padding: 8px 12px; border-radius: 10px; background: #EEF9F5; }
    .zm-set-title { margin: 16px 0 8px; font: 800 15px/1.3 "Manrope", sans-serif; color: #0F2A22; text-transform: none; }
    .zm-notes { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .zm-fnote { margin: 0; text-align: center; perspective: 600px; }
    .zm-fnote svg { display: block; width: 100%; height: auto; border-radius: 10px; box-shadow: 0 4px 12px rgba(15, 42, 34, .12); transition: transform .5s; }
    .zm-fnote.is-tilted svg { transform: rotateY(22deg) rotateX(6deg); }
    .zm-fnote figcaption { margin-top: 6px; font-weight: 800; font-size: 14px; color: #0F2A22; }
    /* a phone: one banknote under the other, big enough to see the raised lines and the watermark */
    @media (max-width: 520px) { .zm-notes { grid-template-columns: 1fr; gap: 12px; } .zm-fnote svg { max-width: 320px; margin: 0 auto; } }
    @media (prefers-reduced-motion: reduce) { .zm-fnote svg { transition: none; } }

    /* prices, the exchange office, the Big Mac table, the budget */
    .zm-price-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr); gap: 16px; align-items: center; }
    @media (max-width: 760px) { .zm-price-grid { grid-template-columns: 1fr; } }
    .zm-item { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border-radius: 14px; background: #FFFBF2; border: 1px solid #F3D9A6; }
    .zm-item-ico { font-size: 44px; line-height: 1; }
    .zm-item strong { display: block; font: 800 17px/1.3 "Manrope", sans-serif; color: #0F2A22; }
    .zm-item-price { display: inline-block; margin-top: 4px; padding: 2px 10px; border-radius: 8px; background: #fff; border: 1px dashed #D9A441; font: 800 18px/1.3 "Manrope", sans-serif; color: #9A6400; }
    .zm-mini-row { display: grid; grid-template-columns: 52px minmax(0, 1fr) auto; align-items: center; gap: 10px; padding: 8px 6px; border-bottom: 1px solid rgba(255, 255, 255, .06); }
    .zm-bureau { display: grid; grid-template-columns: 52px 1fr 1fr; gap: 8px 12px; align-items: center; padding: 4px 6px; }
    .zm-bureau-head { font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #8FB8AA; text-align: right; }
    .zm-bureau .zm-val { text-align: right; }
    .zm-ask { display: inline-block; min-width: 28px; padding: 0 8px; border-radius: 8px; background: #FFF4D6; color: #9A6400; text-align: center; }
    .zm-bars { display: flex; flex-direction: column; gap: 10px; }
    .zm-bar-row { display: grid; grid-template-columns: minmax(90px, auto) minmax(0, 1fr) auto; align-items: center; gap: 10px; font-size: 14px; color: #33443E; }
    .zm-bar { height: 18px; border-radius: 6px; background: #EEF3F1; overflow: hidden; }
    .zm-bar span { display: block; height: 100%; border-radius: 6px 4px 4px 6px; }
    .zm-bar-row b { color: #0F2A22; font-variant-numeric: tabular-nums; }
</style>
@endonce
