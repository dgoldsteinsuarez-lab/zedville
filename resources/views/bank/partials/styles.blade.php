{{-- The bank's look (2026-10-29): the app's green, cards with room, one money format. --}}
@once
<style>
    .zb-top { margin: -8px 0 18px; }
    .zb-h1 { font: 800 22px/1.2 "Manrope", sans-serif; color: #0F2A22; margin: 0 0 12px; }
    .zb-tabs { display: flex; flex-wrap: wrap; gap: 4px; border-bottom: 1px solid #D2DDDB; }
    .zb-tab { padding: 10px 16px; margin-bottom: -1px; font: 700 15px/1.3 "Manrope", sans-serif; color: #44564F; border-bottom: 3px solid transparent; }
    .zb-tab:hover { color: #016950; }
    .zb-tab.is-on { color: #016950; border-bottom-color: #00A47D; }
    .zb-tab--help { margin-left: auto; font-weight: 600; }
    .zb-errors { margin: 0 0 16px; padding: 12px 16px; border-radius: 12px; background: #FDECEC; border: 1px solid #F3B9BC; color: #8E1F25; font-size: 14px; }
    .zb-errors ul { margin: 6px 0 0; padding-left: 20px; list-style: disc; }

    .zb-page { max-width: none; } /* the whole width (2026-10-30: "maximize the spaces on the browser") */
    .zb-grid { display: grid; gap: 16px; }
    .zb-grid--3 { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
    .zb-grid--2 { grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); }
    .zb-card { background: #fff; border: 1px solid #D2DDDB; border-radius: 14px; padding: 18px 20px; }
    .zb-card h2, .zb-h2 { font: 800 17px/1.3 "Manrope", sans-serif; color: #0F2A22; margin: 0 0 12px; }
    .zb-muted { color: #5E7069; font-size: 14px; line-height: 1.45; }
    .zb-small { font-size: 13px; color: #5E7069; }

    /* the three accounts, each its colour */
    .zb-acc { position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 8px; }
    .zb-acc::before { content: ""; position: absolute; inset: 0 0 auto; height: 5px; background: var(--acc, #00A47D); }
    .zb-acc--current { --acc: #00A47D; }
    .zb-acc--emergency { --acc: #F59E0B; }
    .zb-acc--money_market { --acc: #7C3AED; }
    .zb-acc-name { display: flex; align-items: center; gap: 8px; font: 800 16px/1.3 "Manrope", sans-serif; color: #0F2A22; }
    .zb-dot { width: 10px; height: 10px; border-radius: 99px; background: var(--acc, #00A47D); flex: none; }
    .zb-amount { font: 800 30px/1.15 "Manrope", sans-serif; color: #0F2A22; font-variant-numeric: tabular-nums; }
    .zb-amount--off { font-size: 18px; color: #8A9A95; }
    .zb-num { font: 600 13px/1.4 ui-monospace, SFMono-Regular, Menlo, monospace; color: #5E7069; letter-spacing: .02em; }
    .zb-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: auto; padding-top: 6px; }
    .zb-actions .themeBtn, .zb-actions .whiteBtn { padding: 8px 16px; font-size: 14px; }

    /* this month */
    .zb-bar { display: flex; height: 12px; border-radius: 99px; overflow: hidden; background: #EEF3F1; margin: 4px 0 12px; }
    .zb-bar > span { display: block; height: 100%; }
    .zb-c-needs { background: #00A47D; } .zb-c-wants { background: #F59E0B; } .zb-c-savings { background: #7C3AED; } .zb-c-other { background: #64748B; }
    .zb-legend { display: grid; grid-template-columns: 1fr auto; gap: 8px 12px; font-size: 14px; }
    .zb-legend dt { display: flex; align-items: center; gap: 8px; color: #33443E; }
    .zb-legend dd { margin: 0; text-align: right; font-weight: 700; color: #0F2A22; font-variant-numeric: tabular-nums; }
    .zb-legend .zb-sw { width: 12px; height: 12px; border-radius: 3px; flex: none; }
    .zb-legend .is-total { padding-top: 8px; border-top: 1px solid #E6ECEA; }

    /* lists: bills, movements, orders, saved beneficiaries */
    .zb-list { display: flex; flex-direction: column; }
    .zb-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #EEF3F1; }
    .zb-row:last-child { border-bottom: 0; }
    .zb-row-main { flex: 1; min-width: 0; }
    .zb-row-title { font-size: 15px; font-weight: 600; color: #0F2A22; overflow-wrap: anywhere; }
    .zb-row-sub { font-size: 13px; color: #5E7069; }
    .zb-row-amount { font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; color: #0F2A22; }
    .zb-in { color: #017A5C; }
    .zb-tick { width: 26px; height: 26px; border-radius: 99px; display: grid; place-items: center; flex: none; font-weight: 800; font-size: 14px; }
    .zb-tick.is-paid { background: #E2F6EF; color: #017A5C; }
    .zb-tick.is-due { background: #FFF4DE; color: #9A6400; }
    .zb-tick.is-late { background: #FDECEC; color: #8E1F25; }

    /* forms */
    .zb-choices { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 18px; }
    .zb-choice { display: block; padding: 14px 16px; border: 2px solid #D2DDDB; border-radius: 14px; background: #fff; color: #0F2A22; }
    .zb-choice:hover { border-color: #00A47D; }
    .zb-choice.is-on { border-color: #00A47D; background: #F0FBF7; box-shadow: 0 0 0 3px rgba(0, 164, 125, .15); }
    .zb-choice b { display: block; font: 800 16px/1.3 "Manrope", sans-serif; margin-bottom: 4px; }
    .zb-choice span { font-size: 14px; color: #5E7069; line-height: 1.4; }
    .zb-field { margin-bottom: 14px; }
    .zb-field label, .zb-label { display: block; margin-bottom: 6px; font-weight: 700; font-size: 14px; color: #0F2A22; }
    .zb-page .form-control { display: block; width: 100%; min-height: 44px; padding: 9px 12px; border: 1px solid #B9C9C3; border-radius: 10px;
        background: #fff; color: #0F2A22; font-size: 15px; line-height: 1.4; }
    .zb-page .form-control:focus { outline: none; border-color: #00A47D; box-shadow: 0 0 0 3px rgba(0, 164, 125, .2); }
    .zb-page .form-control::placeholder { color: #8A9A95; }
    .zb-help { margin-top: 4px; font-size: 13px; color: #5E7069; }
    .zb-row2 { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
    .zb-seg { display: inline-flex; flex-wrap: wrap; gap: 6px; }
    .zb-seg label { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid #D2DDDB; border-radius: 99px; font-weight: 600; font-size: 14px; cursor: pointer; margin: 0; }
    .zb-seg input { accent-color: #00A47D; }
    .zb-seg label:has(input:checked) { border-color: #00A47D; background: #F0FBF7; color: #016950; }
    .zb-check { padding: 16px; border-radius: 12px; background: #F4F8F6; border: 1px solid #E3ECE8; margin-bottom: 14px; }
    .zb-check p { margin: 0 0 6px; font-size: 16px; color: #0F2A22; }
    .zb-warn { margin: 8px 0 0; padding: 10px 12px; border-radius: 10px; background: #FFF4DE; color: #7A4F00; font-size: 14px; }

    /* an easy activity (Engagement) */
    .zb-task { border-left: 5px solid #EC4899; margin-bottom: 16px; }
    .zb-task.is-done { border-left-color: #00A47D; }
    .zb-task h2 { margin-bottom: 8px; }
    .zb-task-tag { margin: 0 0 4px; font-size: 13px; font-weight: 700; color: #9D174D; }
    .zb-task.is-done .zb-task-tag { color: #016950; }
    .zb-task-steps { margin: 0 0 10px; padding-left: 22px; list-style: decimal; font-size: 15px; line-height: 1.5; color: #0F2A22; }
    .zb-task-steps li { margin-bottom: 4px; }
    .zb-task-foot { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; justify-content: space-between; }
    .zb-task-foot .themeBtn { padding: 8px 16px; font-size: 14px; }
    .zb-task-done { margin: 0; font-weight: 700; color: #017A5C; }

    /* statement */
    .zb-sum { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); margin-bottom: 8px; }
    .zb-sum > div { padding: 12px 14px; border-radius: 12px; background: #F4F8F6; }
    .zb-sum dt { font-size: 13px; color: #5E7069; }
    .zb-sum dd { margin: 2px 0 0; font: 800 20px/1.2 "Manrope", sans-serif; color: #0F2A22; font-variant-numeric: tabular-nums; }
    .zb-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .zb-table th { text-align: left; font-size: 13px; color: #5E7069; font-weight: 700; padding: 8px 10px; border-bottom: 2px solid #E6ECEA; }
    .zb-table td { padding: 9px 10px; border-bottom: 1px solid #EEF3F1; vertical-align: top; }
    .zb-table .num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .zb-table tfoot td { font-weight: 800; border-bottom: 0; border-top: 2px solid #E6ECEA; }

    /* card & details */
    .zb-detail { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; background: #F4F8F6; margin-bottom: 8px; }
    .zb-detail dt { font-size: 13px; color: #5E7069; }
    .zb-detail dd { margin: 2px 0 0; font: 700 16px/1.3 ui-monospace, SFMono-Regular, Menlo, monospace; color: #0F2A22; overflow-wrap: anywhere; }
    .zb-copy { margin-left: auto; flex: none; padding: 6px 12px; border-radius: 99px; border: 1px solid #B7E3D6; background: #fff; color: #016950; font-weight: 700; font-size: 13px; }
    .zb-copy.is-done { background: #00A47D; border-color: #00A47D; color: #fff; }
    .zb-debit { position: relative; padding: 20px; border-radius: 16px; color: #fff; background: linear-gradient(135deg, #0F2A22, #016950 60%, #00A47D); box-shadow: 0 12px 28px rgba(1, 105, 80, .25); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    .zb-debit-bank { font: 800 15px/1 "Manrope", sans-serif; letter-spacing: .02em; }
    .zb-debit-number { margin: 26px 0 16px; font-size: 20px; letter-spacing: .08em; }
    .zb-debit-meta { display: flex; gap: 24px; font-size: 13px; }
    .zb-debit-meta b { display: block; font-size: 15px; }

    @media print {
        aside, header, .zb-tabs, .zb-noprint, .zv-page-back, .zv-top { display: none !important; }
        main { padding: 0 !important; overflow: visible !important; }
        .zb-card { border: 0; padding: 0; }
    }
</style>
@endonce
