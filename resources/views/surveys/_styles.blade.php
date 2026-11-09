{{-- Styles shared by the survey (wizard) and its saved results (profile.partials.surveys-results). --}}
@once
<style>
    /* wide and compact, so a step fits the screen without scrolling (Daniel,
       2026-10-28: "expand the questions on the browser"). width: the steps size to
       the survey's own width, so it can't come from its content. */
    .zvq { width: 100%; max-width: 1100px; margin: 0 auto; background: #fff; border: 1px solid #D2DDDB; border-radius: 16px; padding: 18px 22px; position: relative; container-type: inline-size; }
    .zvq [x-cloak] { display: none !important; }
    .zvq-head { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 10px; }
    .zvq-title { font-size: 13px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #00A47D; }
    .zvq-progress { display: flex; align-items: center; gap: 10px; font-size: 12px; color: #6B7B76; }
    .zvq-bar { width: 140px; height: 8px; background: #E3ECE8; border-radius: 999px; overflow: hidden; }
    .zvq-bar-fill { height: 100%; background: linear-gradient(90deg, #00A47D, #2BC79F); border-radius: 999px; transition: width .3s; }
    .zvq-guide { display: flex; gap: 12px; align-items: center; margin-bottom: 10px; }
    .zvq-avatar { flex: none; width: 42px; height: 42px; border-radius: 999px; background: #E7FBF3; display: flex; align-items: center; justify-content: center; font-size: 23px; }
    .zvq-bubble { position: relative; background: #F4F8F6; border: 1px solid #E3ECE8; border-radius: 14px; padding: 8px 14px; flex: 1; }
    .zvq-guide-name { font-size: 12px; font-weight: 700; color: #016950; margin-bottom: 2px; }
    /* on a wide step, her name and her question share one line */
    @container (min-width: 760px) {
        .zvq-bubble { display: flex; align-items: baseline; gap: 10px; }
        .zvq-guide-name { flex: none; margin: 0; }
        /* her question, then the survey's title over the progress - one row, not two */
        .zvq-top { display: grid; grid-template-columns: minmax(0, 1fr) auto; column-gap: 16px; align-items: center; margin-bottom: 8px; }
        .zvq-top > .zvq-guide { grid-column: 1; grid-row: 1; margin-bottom: 0; }
        .zvq-top > .zvq-head { grid-column: 2; grid-row: 1; margin-bottom: 0; flex-direction: column; align-items: flex-end; gap: 6px; }
    }
    .zvq-guide-name span { font-weight: 500; color: #8A9A95; }
    .zvq-guide-text { margin: 0; font-size: 15px; line-height: 1.55; color: #2A3B36; }
    .zvq-step-title { font-size: 20px; font-weight: 800; color: #0F2A22; margin: 0 0 8px; }
    .zvq-step-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
    .zvq-muted { color: #8A9A95; font-size: 14px; }
    .zvq-error { margin-bottom: 12px; padding: 10px 14px; border-radius: 10px; background: #FDECEC; color: #B4232B; font-size: 14px; font-weight: 600; }
    .zvq-note { padding: 12px 14px; border-radius: 10px; background: #FFF5D4; border: 1px solid #F1E2B0; color: #6b5417; font-size: 14px; }
    .zvq-link { color: #016950; font-weight: 700; text-decoration: underline; }
    .zvq-promise { display: flex; gap: 10px; align-items: center; padding: 14px; border: 1px solid #D2DDDB; border-radius: 12px; cursor: pointer; font-weight: 600; color: #0F2A22; }
    .zvq-promise input { width: 20px; height: 20px; accent-color: #00A47D; }
    /* the answers one under the other (Daniel, 2026-10-29: "not two columns of the
       choices"), in compact cards so a step still fits the screen */
    .zvq-options { display: grid; gap: 8px; }
    .zvq-option { display: flex; flex-direction: column; gap: 2px; text-align: left; width: 100%; padding: 9px 14px; border: 1px solid #D2DDDB; border-radius: 12px; background: #fff; cursor: pointer; transition: border-color .15s, background .15s, box-shadow .15s; }
    .zvq-option:hover { border-color: #00A47D; }
    .zvq-option.is-on { border-color: #00A47D; background: #F0FBF7; box-shadow: 0 0 0 2px rgba(0, 164, 125, .25); }
    /* name and text on the left, Need/Want and the price on the right */
    .zvq-option-row { flex-direction: row; align-items: center; justify-content: space-between; gap: 12px; }
    .zvq-option-main { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .zvq-option-title { font-weight: 700; color: #0F2A22; }
    .zvq-option-text { font-size: 13px; color: #6B7B76; }
    .zvq-option-side { flex: none; display: flex; flex-direction: row; align-items: center; gap: 10px; }
    /* with the room, one line per answer - its name, what it means, then Need/Want and the
       price - so up to eight answers and Next fit the screen */
    @container (min-width: 700px) {
        .zvq-options:has(> .zvq-option-row) { gap: 6px; }
        .zvq-option-row { padding-top: 4px; padding-bottom: 4px; }
        section > .zvq-step-title { margin-bottom: 6px; }
        .zvq-option-row .zvq-option-main { flex-direction: row; align-items: baseline; flex-wrap: wrap; column-gap: 10px; }
        .zvq-option-row .zvq-option-title { line-height: 20px; }
        .zvq-option-row .zvq-option-text { line-height: 18px; }
        .zvq-option-row .zvq-tag { line-height: 16px; }
    }
    .zvq-option-price { font-size: 13px; color: #5B6B66; white-space: nowrap; }
    .zvq-tag { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .zvq-tag.is-need { background: #E7FBF3; color: #016950; }
    .zvq-tag.is-want { background: #FFF0E0; color: #A3550A; }
    .zvq-count { font-size: 13px; font-weight: 700; padding: 4px 12px; border-radius: 999px; background: #F1F4F3; color: #5B6B66; }
    .zvq-count.is-done { background: #E7FBF3; color: #016950; }
    .zvq-group { margin-top: 14px; }
    .zvq-group-title { font-size: 12px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #8A9A95; margin-bottom: 8px; }
    .zvq-items { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; }
    .zvq-item { display: grid; grid-template-columns: auto 1fr; grid-template-areas: "check name" "check price"; column-gap: 10px; align-items: center; text-align: left; padding: 9px 12px; border: 1px solid #D2DDDB; border-radius: 10px; background: #fff; cursor: pointer; transition: border-color .15s, background .15s; }
    .zvq-item:hover:not(:disabled) { border-color: #00A47D; }
    .zvq-item.is-on { border-color: #00A47D; background: #F0FBF7; }
    .zvq-item.is-locked { cursor: default; }
    .zvq-item.is-full { opacity: .45; cursor: not-allowed; }
    .zvq-item-check { grid-area: check; width: 24px; height: 24px; border-radius: 999px; border: 1px solid #B7E3D6; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: #00A47D; }
    .zvq-item.is-on .zvq-item-check { background: #00A47D; border-color: #00A47D; color: #fff; }
    .zvq-item-name { grid-area: name; font-weight: 600; font-size: 14px; color: #0F2A22; line-height: 1.25; }
    .zvq-item-price { grid-area: price; font-size: 12px; color: #6B7B76; }
    .zvq-footer { position: sticky; bottom: 0; margin: 12px -22px -18px; padding: 9px 22px; background: #0F2A22; color: #fff; border-radius: 0 0 16px 16px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; z-index: 2; }
    .zvq-footer-label { font-size: 12px; text-transform: uppercase; letter-spacing: .06em; opacity: .7; }
    .zvq-footer-total { font-size: 22px; font-weight: 800; }
    .zvq-footer-right { text-align: right; }
    .zvq-footer-pct { font-size: 13px; opacity: .85; margin-bottom: 4px; }
    .zvq-footer-split { display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap; }
    .zvq-nav { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 18px; }
    .zvq-footer + .zvq-nav { margin-top: 14px; }
    /* with the room, the month so far in one row, as a bar inside the card above Back /
       Next (its pull to the card's edge made it overlap them) */
    @container (min-width: 700px) {
        .zvq-footer { margin: 8px 0 0; padding-top: 7px; padding-bottom: 7px; border-radius: 12px; }
        .zvq-footer + .zvq-nav { margin-top: 8px; }
        .zvq-footer > div:first-child { display: flex; align-items: baseline; gap: 12px; }
        .zvq-footer-right { display: flex; align-items: center; gap: 12px; }
        .zvq-footer-pct { margin-bottom: 0; }
        .zvq-footer-total { font-size: 20px; }
    }
    .zvq-nav .themeBtn:disabled { opacity: .5; cursor: not-allowed; }
    .zvq-type { padding: 18px; border-radius: 14px; background: linear-gradient(135deg, #E7FBF3, #F4F8F6); border: 1px solid #B7E3D6; text-align: center; margin-bottom: 12px; }
    .zvq-type-title { font-size: 26px; font-weight: 800; color: #016950; margin-bottom: 6px; }
    .zvq-type-text { margin: 0 0 8px; color: #2A3B36; }
    .zvq-type-tip { margin: 0; font-size: 14px; font-weight: 600; color: #8a6d1d; }
    .zvq-summary { border: 1px solid #E3ECE8; border-radius: 12px; padding: 12px 16px; }
    .zvq-summary-title { font-size: 12px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #8A9A95; margin-bottom: 6px; }
    .zvq-summary-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px dashed #E3ECE8; font-size: 14px; }
    .zvq-summary-right { display: flex; gap: 10px; align-items: center; }
    .zvq-summary-total { border-bottom: 0; font-size: 16px; }
    .zvq-split { display: flex; height: 10px; border-radius: 999px; overflow: hidden; background: #E3ECE8; margin-top: 8px; }
    .zvq-split-needs { background: #00A47D; }
    .zvq-split-wants { background: #F39A3B; }
    .zvq-split-legend { display: flex; gap: 16px; margin-top: 6px; font-size: 12px; color: #5B6B66; }
    .zvq-split-legend i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; vertical-align: -1px; }
    .zvq-split-legend i.is-need { background: #00A47D; }
    .zvq-split-legend i.is-want { background: #F39A3B; }
    /* In Settings the survey has the card's whole width, without a card inside the
       card, and the page above it is tighter - a step fits the screen (2026-10-28) */
    main:has(.zv-settings) { padding-top: 24px; padding-bottom: 8px; position: relative; }
    /* Back beside the page's title, not on a row of its own */
    main:has(.zv-settings) > .zv-page-back { position: absolute; top: 24px; left: 32px; }
    main:has(.zv-settings) > .zv-page-back > a { margin-bottom: 0; }
    main:has(.zv-settings) .zv-settings-title { padding-left: 120px; min-height: 38px; }
    .zv-settings:has(#tab3.active) { max-width: 1140px; padding: 10px 18px 6px; margin-top: 8px; }
    .zv-settings .zvq { border: 0; padding: 0; max-width: none; }
    .zv-settings .zvq-footer { margin: 8px 0 0; border-radius: 12px; }
    @media (max-width: 640px) {
        .zvq { padding: 16px; border-radius: 12px; }
        .zvq-footer { margin: 16px -16px -16px; padding: 10px 16px; }
        /* narrow: Need/Want and the price under the name */
        .zvq-option-row { flex-direction: column; align-items: stretch; gap: 6px; }
        .zvq-option-side { justify-content: space-between; }
    }
</style>
@endonce
