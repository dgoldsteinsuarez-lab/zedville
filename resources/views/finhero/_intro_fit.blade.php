{{-- A FinHero Task's first screen on one screen (Daniel, 2026-10-29, on Task 3:
     "Fit in the black rectangle the infos to be able to remove the scrolling up
     and down. Adjust the spacing of the texts put the box more up. Adjust the
     'back' button. The 'let's go' button" at the card's foot). The card starts
     at the top of the page instead of in the middle of a screen-high box; Back
     and the task's name sit in the free column on its left; a little less space
     between the card's lines. Only while that first screen shows. --}}
@once
<style>
    #intro-screen.screen { min-height: 0; align-items: flex-start; padding: 0; }
    #intro-screen .intro-card { max-width: 600px; padding: 18px 28px 20px; }
    #intro-screen .intro-icon { font-size: 36px; line-height: 1.1; margin-bottom: 2px; }
    #intro-screen .intro-title { font-size: 24px; margin: 0 0 4px; }
    #intro-screen .intro-description { font-size: 14px; line-height: 1.45; margin: 0 0 12px; }
    #intro-screen .rule-list { gap: 6px; margin-bottom: 12px; }
    #intro-screen .rule-row { padding: 8px 14px; gap: 12px; }
    #intro-screen .rule-icon { font-size: 26px; }
    #intro-screen .intro-instructions { padding: 10px 14px; margin-bottom: 12px; }
    #intro-screen .intro-bank { padding: 8px 12px; margin-bottom: 10px; }
    #intro-screen .intro-points-note { padding: 8px 12px; margin-bottom: 14px; }
    #intro-screen .intro-points-note p { font-size: 13px; line-height: 1.4; }
    #intro-screen .start-button { padding: 11px 44px; }
    @media (min-width: 1024px) {
        main:has(#intro-screen:not(.hidden)) { position: relative; padding-top: 16px; padding-bottom: 8px; }
        main:has(#intro-screen:not(.hidden)) > .zv-page-back { position: absolute; top: 16px; left: 32px; }
        main:has(#intro-screen:not(.hidden)) > .zv-page-back > a { margin-bottom: 0; }
        main:has(#intro-screen:not(.hidden)) .zv-task-head { position: absolute; top: 70px; left: 32px; padding: 0; }
    }
</style>
@endonce
