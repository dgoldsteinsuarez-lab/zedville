# Zedville — Refactor & Fix Planning (Controllers / Services / Providers / Kernel)

Sequenced by risk/impact. Each phase is independently shippable — don't start phase N+1 until phase N is merged and verified, since later phases assume earlier fixes are in place. No functionality should change as a *side effect* of refactors in Phase 2-4; only Phase 1 changes behavior (it's bug fixes).

## Phase 0 — Verify before touching anything (no code changes)

1. Confirm actual `role` column values on `users` table (int codes like `4` vs string `'student'`) — resolves `bugs.md` #8. Check migration + a `php artisan tinker` sample row.
2. Confirm whether `routes/admin.php` self-applies the `admin` middleware anywhere — resolves `bugs.md` #6. If not, this is a live authorization gap; escalate immediately regardless of refactor timeline.
3. Confirm the correct canonical monthly salary figure (`3952.40` vs `4250`) with product owner — resolves `bugs.md` #13.
4. Confirm the correct academic-year start month — resolves `bugs.md` #12.
5. Grep whether `EFDController`'s `spending_tracker_basicco/stationary/citymall` methods are still referenced by any route/view before deleting (`bugs.md` #18).

## Phase 1 — Critical/High bug fixes (behavior-changing, ship individually, each with a test)

Do these one at a time, each as its own PR, each verified against a real login → salary → bill-deduction flow before merge.

1. **Fix the ledger split** (`bugs.md` #1): point `BankController::creditMonthlySalary()` at `Transaction1` instead of `Transaction`, matching the column shape used everywhere else (`transaction_date`, `type`, `category`, `amount`, `balance`, `bank_account_id`, `sid`). Verify: credit salary, confirm it appears in the same balance chain `ensureMonthlyBills` reads from, confirm bills deduct correctly afterward.
2. **Add missing imports** (`bugs.md` #3, #4): `use App\Models\Mailbox;` in `CitizenActivationController.php`; `use App\Helpers\MailboxScheduler;` in `AdminStudentController.php`. Trivial, but verify the code paths that call them actually execute without error afterward (write a quick feature test hitting `authorize_direct_deposit` and `add_student`).
3. **Move salary/bills off login-trigger onto a real schedule** (`bugs.md` #2): create an Artisan command (e.g. `students:process-monthly-finances`) that chunks over active students and calls `StatementGenerator::generateForUser()` / the bill-posting logic on the correct day-of-month, idempotently (reuse the existing "already processed this month" guards). Schedule it in `Console/Kernel.php` with `->dailyAt(...)` (checking day-of-month inside the command) rather than depending on user login. Keep the login-triggered call as a redundant safety net during transition, remove once the scheduled job is proven reliable for 1-2 full months in production.
4. **Fix badge scheduling** (`bugs.md` #7): change `monthlyOn(31, ...)` to `lastDayOfMonth(...)`.
5. **Wrap balance mutations in transactions with row locks** (`bugs.md` #5): standardize on a small helper — e.g. `BalanceService::withLockedBalance($userId, callable $fn)` that opens `DB::transaction()`, does `Transaction1::where('user_id',$userId)->latest('id')->lockForUpdate()->first()`, and hands the balance to the callback. Migrate `EFDController::storeDonation`, `OrderController::placeOrder`, `ProfileController::storesurvey` onto it one at a time, each with a concurrency-oriented test (two rapid requests, assert no overdraft).
6. **Resolve admin middleware gap** (`bugs.md` #6) — once Phase 0 step 2 confirms the gap is real, add `admin` middleware to the `RouteServiceProvider` admin group (or per-route in `admin.php`, whichever the existing pattern favors). Treat as security-priority, don't wait for the rest of Phase 1.
7. **Decide and resolve the dead penalty trigger** (`bugs.md` #9) — get a product decision (enable or delete), implement accordingly.

## Phase 2 — Structural extraction (no behavior change, pure refactor, needs regression coverage first)

Before starting Phase 2, get baseline test coverage (feature tests) around: login flow, salary credit, bill posting, order placement, donation, survey submission. Refactors below must not change observable behavior — verify via these tests before/after each step.

1. **Extract a `LedgerService`** wrapping the "get latest `Transaction1` balance" pattern — IN PROGRESS, started 2026-10-10. The actual count turned out to be 80+ call sites, not ~13 (`app/Services/LedgerService.php`, built on the existing `BalanceService::lockedBalance()`). Migrated so far: `BankController::ensureMonthlyBills()` and `banks_penalty()` (both had a `max(0, ...)` floor-at-zero rule, preserved via a `floor_at_zero` option on `LedgerService::post()`), verified against `tests/Feature/Bank/ScheduledFinanceSidTest.php`. Also migrated: `EFDController::storeDonation()` and `OrderController::placeOrder()` - both already used `BalanceService::lockedBalance()` correctly, so this was a pure code-consolidation move (the per-item negative-balance guard in `placeOrder`'s loop was kept as an explicit arithmetic check rather than dropped, since `LedgerService::post()` doesn't reject on negative by default). Deliberately NOT migrated: `creditMonthlySalary()` - its balance lookup orders by `transaction_date` (not `id`) because the salary transaction is backdated to the 6th, which `LedgerService` doesn't support and migrating would silently change; `processTransfer()` - has per-account-type balance strategies (primary/emergency/money-market) and never re-validates balance at execution time for delayed/recurring transfers, both of which need their own dedicated look rather than a drop-in swap; the other ~65 call sites in `BankController`/etc. - each needs the same care taken here, so this continues incrementally, one method at a time, each with its own test.
2. **Extract fixed-bill posting** into one method (currently duplicated 3-4x across `CitizenActivationController`, `AuthenticatedSessionController::ensureMonthlyBills`, `BankController`) — e.g. `LedgerService::postMonthlyFixedBills($userId, $bankAccountId, Carbon $forDate)`. This directly supports Phase 1 step 3's scheduled command.
3. **Add a `SetSelectedSchool` middleware** (or a global model scope) to replace the ~40+ manual `session('selected_school')` reads scattered through `AdminEducationController` and siblings. Bind the resolved school onto the request/container once per request instead of re-reading session in every method.
4. ~~**Fix `sid` provenance** (`bugs.md` #14)~~ — DONE 2026-10-08 for the three call sites actually reachable from a non-session (console/scheduled) context: `creditMonthlySalary`, `ensureMonthlyBills`, `banks_penalty` in `BankController`. Verified with a real baseline test (`tests/Feature/Bank/ScheduledFinanceSidTest.php`) that reproduces the bug against the old code and passes against the fix. The remaining `session()->get('sid')` call sites are all on interactive student-initiated paths and were left alone - not touched, since the session value is correct there and `LedgerService` doesn't exist yet to make that a one-line change.
5. ~~**Consolidate Civic Chamber** (`bugs.md` #10)~~ — DONE differently 2026-10-07: the admin controller's student-facing copy was dead/unrouted, not actually drifting from EFDController's — deleted it instead of building a shared service. No consolidation needed since there was only ever one live implementation.
6. **Introduce Laravel policies** for the admin/tutor/student checks currently duplicated ad hoc (`ClosetController::requireAdmin`, inline `$user->role == 4` checks). Register in `AuthServiceProvider::$policies`. Don't remove the `IsAdmin` route middleware — policies handle in-controller/resource-level checks, middleware handles route-level.

## Phase 3 — Larger consolidations (bigger risk, plan as dedicated initiatives, not incidental to other work)

1. **Evaluate the two badge systems** (`bugs.md` #11): confirm with product whether both "Engagement Badge" and "FinHero Badge" are still both required. If yes, extract a shared `BadgeEngine` service parameterized by badge-type config (thresholds, categories, level names) so future rule changes apply once. If one is legacy, plan deprecation separately — this is a product decision, not just a refactor.
2. **Consolidate the triplicated supermarket/spending-tracker listing logic** (`SupermarketController`, `SpendingActivitiesController`) into one parameterized controller/service. The dead `EFDController` copies this item used to wait on are already gone (confirmed 2026-10-07) — this item can proceed without that precondition.
3. **Centralize config** — PARTLY DONE: `config/zedville.php` exists and holds `academic_year_start_month` (4) and `monthly_salary` (3952.40), the two values that were previously inconsistent across files (`bugs.md` #12/#13), plus `auto_penalty`/`inactive_days_threshold`. Still to extract: emergency-fund %, rent, school fees, badge thresholds.
4. **Break up the largest controllers** (`BankController` 2377 LOC, `ProfileController` 1324 LOC, `AdminEducationController` 1114 LOC) into narrower controllers + services, guided by the method-boundary findings already listed in `bugs.md`/`ProjectArchitecture.md`. Do this only after Phase 2's `LedgerService`/`SetSelectedSchool` extractions land, since those remove a large fraction of what makes these controllers fat.

## Phase 4 — Cleanup (any time, low risk, keep isolated from functional PRs)

1. ~~Delete all dead/commented-out code blocks listed in `bugs.md` #16~~ — DONE 2026-10-07.
2. ~~Delete the self-acknowledged dead triplicate methods in `EFDController` (`bugs.md` #18)~~ — already gone; the triplicated `spending_tracker_*` methods no longer exist in the codebase.
3. ~~Batch-fix naming typos (`bugs.md` #17)~~ — DONE for the pure PHP identifiers (`emmengercyfundintrest` → `emergencyFundInterest`, dead `updatepassowrd` removed). `backaccountnumber` (DB column) still needs its own migration + call-site audit pass — not done casually.
4. Re-enable or remove the inert "don't repeat quiz questions" logic in `LoginQuizController` (`bugs.md` #15) — turned out to be live, not inert (the real `getTodayQuestion()` already does `whereNotIn($answeredIds)`); only a dead alternate draft existed, now removed. Nothing left to decide here.

## Ground rules for whoever executes this plan

- No PR should combine a bug fix with a refactor — reviewers need to tell "behavior change" from "same behavior, cleaner code" at a glance.
- Every Phase 1 item needs a test that would have caught the original bug, not just a manual smoke check.
- Do not touch DB schema/migrations as part of any Phase 1-3 item unless explicitly called out (only the column-rename item in Phase 4 touches schema, and it's flagged as needing special care).
- Re-run the verification steps in Phase 0 whenever revisiting this plan after a gap in time — the codebase may have moved since this audit.

See `bugs.md` for the full defect list this plan resolves, and `ProjectArchitecture.md` for system context.
