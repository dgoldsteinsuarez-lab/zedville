# Zedville — Bugs & Defects (Controllers / Services / Providers / Kernel)

Severity: 🔴 Critical (data-corrupting or runtime-fatal) · 🟠 High (real-world incorrect behavior) · 🟡 Medium (correctness risk under load/edge case) · ⚪ Low (cleanliness/inconsistency)

## 🔴 Critical

### 1. Salary credited to a ledger nothing else reads (`Transaction` vs `Transaction1` split)
`BankController::creditMonthlySalary()` writes salary credits to the `Transaction` model. Every other balance-touching flow in the app — `OrderController`, `SupermarketController`, `SpendingActivitiesController`, `ProfileController::storesurvey`, `EFDController::storeDonation`, `CitizenActivationController`, `AuthenticatedSessionController::ensureMonthlyBills`, `StatementGenerator`, and the bank statement view — reads/writes `Transaction1`.
Result: the salary the student receives never appears in the balance chain that expenses deduct from. This is the exact symptom originally reported ("expenses on the 7th aren't deducted from balance = last balance + salary").
**Fix:** point `creditMonthlySalary()` at `Transaction1` with matching columns (`transaction_date`, `type`, `category`, `amount`, `balance`, `bank_account_id`). See `planning.md` Phase 1.

### 2. Monthly salary/bills are login-gated with no backfill
`StatementGenerator::generateForUser()` (salary + emergency-fund transfer + needs debits) and `AuthenticatedSessionController::ensureMonthlyBills()` (rent/electricity/water/internet/school fees) only run from `AuthenticatedSessionController::store()`, gated by day-of-month (`>=6`, `>=7` respectively). Nothing in `Console/Kernel.php` schedules either. A student who doesn't log in inside that window in a given month **never receives that month's salary or bills**, and there is no catch-up mechanism.
**Fix:** move both to a scheduled command (cron-driven, idempotent, chunked over users) instead of triggering on login. See `planning.md` Phase 1.

### 3. `Mailbox::create()` called with no import — likely fatal
`CitizenActivationController.php:144` and `:566` call `Mailbox::create(...)`. The file's `use` block (lines 5-14) imports `BankAccount`, `StatementGenerator`, `BankStatement`, `ManagePayeeBiller`, `AutoDebitRequest`, `Transaction`, `Transaction1` — **no `use App\Models\Mailbox;`**. Unless a global class alias is configured elsewhere, this throws `Class "App\Http\Controllers\Mailbox" not found` at runtime whenever that code path executes.
**Fix:** add `use App\Models\Mailbox;`. One-line fix, verify path executes in a test before merging.

### 4. `MailboxScheduler::scheduleForEvent()` called with no import — likely fatal
`AdminStudentController::add_student` (line 118) calls `MailboxScheduler::scheduleForEvent(...)`. The file's imports (lines 4-13) don't include `App\Helpers\MailboxScheduler`. Same class-not-found risk as #3, on the admin "add student" flow.
**Fix:** add the missing `use` statement.

## 🟠 High

### 5. No transaction/locking around balance mutations — real overdraft race condition
`EFDController::storeDonation` (lines 527-583) reads the latest balance, checks it against the donation amount, then creates a `Transaction1` row — with **no `DB::transaction()`, no `lockForUpdate()`**. Two concurrent donation requests for the same user can both read the same stale balance, both pass the check, both post — allowing the balance to go negative (or double-spend) under concurrent load.
`OrderController::placeOrder` (lines 76-155) does wrap in `DB::beginTransaction()/commit()/rollBack()`, which is better, but the balance read (lines 41-44) still happens without `lockForUpdate()`, so two simultaneous orders can still race between read and transaction-open.
`ProfileController::storesurvey` reads `$availableBalance` at line 725, checks it at line 752, but doesn't open `DB::beginTransaction()` until line 766 — the check happens **outside** the transaction boundary, same race.
**Fix:** wrap every balance-read-then-write sequence in `DB::transaction()` and add `->lockForUpdate()` on the balance query. Standardize this as one helper (`BalanceService::debit()`/`credit()`) — see `planning.md` Phase 1.

### 7. `badges:calculate-monthly` scheduled on day 31 — skips 5 months a year
`Console/Kernel.php:55-58` schedules the badge calculation command with `->monthlyOn(31, '23:59')`. Laravel's scheduler does not clamp day 31 to month-end; months with fewer than 31 days (Feb, Apr, Jun, Sep, Nov) simply never fire this job.
**Fix:** use `->lastDayOfMonth('23:59')` instead.

### 8. `getAllActiveStudents()` may filter on the wrong role value, silently no-op-ing badge calculation
`BadgeCalculatorService.php:234` and `FinheroBadgeCalculatorService.php:177` both filter `DB::table('users')->where('role', 'student')`. Every other controller in the codebase checks `$user->role == 4` (integer/numeric role code) — e.g. `ProfileController.php:315`, `AdminStudentController.php:22`, `AuthenticatedSessionController.php:163,171,175`. If `role` is stored numerically (which the rest of the codebase implies), this query returns **zero rows**, and the entire monthly badge calculation silently does nothing for the whole student body, every month, with no error.
**Fix:** confirm the actual stored `role` value for students against the `users` table/migration, then align both badge services to match the rest of the codebase.

### 9. Dead penalty trigger — last-day-of-month penalty never actually runs
`AuthenticatedSessionController.php:95-96` — the call to apply the end-of-month penalty (`app(BankController::class)->banks_penalty(...)`) is commented out, but the surrounding date-math/existence-check block (lines 78-97, ~20 lines) still executes on every login and computes nothing useful.
**Fix:** either re-enable the call (if penalties are intended) or delete the dead block. Needs a product decision — flag to whoever owns the penalty feature before changing behavior.

## 🟡 Medium

### 10. Two independent, divergent implementations of the same feature (Civic Chamber)
`EFDController.php:618-772` (student-facing) and `Admin/CivicChamberController.php:334-573` both implement referendum/petition submit/vote/sign/result logic independently. Any bugfix or rule change applied to one will not propagate to the other — they will drift.
**Fix:** consolidate into one `CivicChamberService`, called from both controllers. See `planning.md` Phase 2.

### 11. Two parallel badge systems duplicate override/recalculate/reset logic
"Engagement Badge" (`BadgeCalculatorService` + `BadgeAdminController` + `BadgeStudentController`) and "FinHero Badge" (`FinheroBadgeCalculatorService` + `FinheroAdminController` + `FinheroStudentController`) are structurally near-identical but maintained as two separate codebases. Any shared bugfix must be applied twice (and per #8, may already be inconsistently buggy in only one).
**Fix:** long-term, evaluate whether both systems are still product-required; if so, extract a shared `BadgeEngine` service parameterized by badge-type config. See `planning.md` Phase 3.

### 12. Academic-year start month is inconsistent across files
`BadgeCalculatorService.php:211`, `FinheroBadgeCalculatorService.php:161`, `ParticipationService.php:122`, `ProfileController.php:1133` use month `9`; `ProfileController.php:1273` uses `8` (0-based, effectively also September but fragile); `FinheroPointService.php:21` uses month `4`. This isn't just duplication — it's a genuine behavioral inconsistency between the point/badge systems about when the "academic year" rolls over.
**Fix:** centralize into one config value (`config('zedville.academic_year_start_month')`), audit which value is actually correct with the product owner, fix `FinheroPointService.php` to match.

### 13. Hardcoded monthly salary value differs across files
`3952.40` appears in `CitizenActivationController.php:132`, `EFDController.php:163`, `StatementGenerator.php:70` (as a fallback), while `BankController::creditMonthlySalary` uses `4250` — two different salary figures for the same concept in the same app.
**Fix:** centralize into config, confirm the correct figure, fix the outlier.

### 14. `sid` (school/session id) written from `session('sid')` instead of the authoritative record
`CitizenActivationController.php:68,208,231,253,339`, `OrderController.php:107,120,252,266`, `ProfileController.php:242,775,838,870`, `StatementGenerator.php:124,164,193,240` all persist `session('sid')` onto transaction/order rows. Under concurrent tabs, an expired/regenerated session, or admin impersonation, this can silently mis-tag a row with the wrong school/session id, corrupting later per-school reporting.
**Fix:** derive `sid` from `$user->sid` / `$bankAccount->sid` (the authoritative source) at write time instead of trusting session state. See `planning.md` Phase 2.

### 15. `LoginQuizController` computes "already answered" IDs but never uses them
`LoginQuizController.php:28` computes `$answeredIds`, but the `whereNotIn` filter that would use it is commented out (line 33); the active query (lines 36-38) is plain `inRandomOrder()`. The dedup-questions feature is fully inert — the query runs and its result is discarded every call.
**Fix:** either re-enable the `whereNotIn` filter (if "don't repeat questions" is still wanted) or remove the now-pointless `$answeredIds` computation.

## ⚪ Low (cleanliness — not urgent, batch these during refactor passes)

### 16. Large blocks of commented-out dead code
- `ProfileController.php:275-315, 337-368, 421-459, 496-575, 576-697` — ~280 cumulative lines across four superseded draft rewrites of `storesurvey`/`getCityMood`.
- `LoginQuizController.php:63-127` — 65-line dead alternate implementation.
- `EFDController.php:29-57, 588-591` — dead old `index()` + dead query.
- `CartController.php:12-24, 41-46, 59-66` — dead superseded method bodies.
- `ClosetController.php:235-250` — dead alternate `fetchCloset` join implementation.
- `AdminEducationController.php` — ~15 scattered 3-7 line commented-out sid-filter ternary variants.
- `AuthenticatedSessionController.php:48-58` — ~11 lines dead mailbox-on-first-login logic.

**Fix:** delete in a dedicated cleanup PR, not mixed into functional changes, so diffs stay reviewable. See `planning.md` Phase 4.

### 17. Naming typos/inconsistencies
`emmengercyfundintrest` (`EFDController.php:166,188`, also present in `BankController.php`), `updatepassowrd` (`AdminDashboardController.php:62`), `backaccountnumber` used as an actual DB column name (misspelled "bank"), `aviableblance` (in `BankController.php`, per prior review).
**Fix:** cosmetic-only, batch-rename during a dedicated pass with full regression test coverage (renaming DB columns needs a migration + all call sites, do NOT do this casually).

### 18. Self-acknowledged dead triplicate code never removed
`EFDController.php:307,378` — the code's own comments say "Remove Belo[w] 3 later and use this single function" / "Remove above 3 later," referring to `spending_tracker_basicco/stationary/citymall` being superseded by `spending_tracker`. Never acted on.
**Fix:** low-risk deletion once confirmed unreferenced by any route/view.

---
See `planning.md` for how these are sequenced into fix phases, and `ProjectArchitecture.md` for the system context behind each finding.
