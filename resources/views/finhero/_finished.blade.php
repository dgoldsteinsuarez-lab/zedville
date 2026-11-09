{{-- Under a finished FinHero Task (Daniel, 2026-10-28: "put a finished button down
     below the activity"): Finish, back to the department, whose board now shows it
     done. Sits inside the task's #completion, so it only shows once every question
     is done. --}}
@include('partials.finish')
@once
<style>
    /* Once the task is finished, the salary / bank statement bar stops riding
       along at the top: scrolling down, it covered the results card (Daniel,
       2026-10-28). It stays in its place above the questions. */
    #game-screen:has(#completion:not(.hidden)) :is(.reference, .bank-sticky) { position: static; }
</style>
@endonce
