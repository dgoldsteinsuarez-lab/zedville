# Zedville — Project Architecture (Controllers / Services / Providers / Kernel)

Scope of this doc: `app/Http/Controllers/**`, `app/Services/**`, `app/Providers/**`, `app/Http/Kernel.php`, `app/Console/Kernel.php` only. No other folders were reviewed. This is a snapshot for a new developer or AI agent joining cold — read this before touching any of the above.

## 1. What the app is

Zedville is a dummy-finance learning platform for students. Each student is a "virtual citizen" who receives a virtual monthly salary, pays virtual expenses (rent, school fees, utilities, groceries, shopping), and learns budgeting through simulated banking, a supermarket, a closet/shop, quizzes, and gamified badges.

## 2. Layering (as it actually exists, not as it should be)

```
Route (routes/*.php)
   -> Controller (fat — validation + business rules + persistence + view assembly, mostly in one method)
        -> Service (only 6 exist; most controllers skip this layer entirely)
             -> Eloquent Model  (mostly)
             -> DB::table() / DB::raw()  (ClosetController, a few Admin controllers — bypasses ORM)
```

Only 6 real services exist:
- `BadgeCalculatorService` — "Engagement Badge" system
- `FinheroBadgeCalculatorService` — "FinHero Badge" system (parallel, independent duplicate of the above concept)
- `FinheroPointService`
- `ParticipationService` — awards/dedupes gamification points, called from `EFDController`, `OrderController`
- `StatementGenerator` — monthly salary credit, emergency-fund transfer, fixed-needs debits, generates `BankStatement` records
- `CitizenActivationService` — small state machine for the onboarding/activation flow

Everything else (survey logic, mailbox composition, mood tracking, badge admin overrides, civic chamber referendums) lives directly inside controllers.

## 3. The two ledgers — read this before touching any balance code

There are two transaction models: `Transaction` and `Transaction1`.

- **`Transaction1` is the real, live ledger.** Every controller that reads/writes a student's balance uses it: `OrderController`, `SupermarketController`, `SpendingActivitiesController`, `ProfileController::storesurvey`, `EFDController::storeDonation`, `CitizenActivationController`, `AuthenticatedSessionController::ensureMonthlyBills`, `StatementGenerator`, and the bank views/statements.
- **`Transaction` is dead/orphaned everywhere except `BankController::creditMonthlySalary`**, which writes salary credits into `Transaction` — a table nothing else reads from. See `bugs.md` #1 for the fallout.

**Rule for any future work touching balances: use `Transaction1`. Never `Transaction`.**

## 4. Balance-read pattern (used 13+ places, no shared helper)

```php
$latestTxn = Transaction1::where('user_id', $userId)->latest('id')->first();
$lastBalance = $latestTxn ? $latestTxn->balance : 0;
```

There is no `bank_accounts.current_balance` column being trusted, no ledger service, no cache. Every balance check re-derives the balance by finding the latest row. This is both a duplication problem and a concurrency risk (see `bugs.md`).

## 5. Parallel/duplicate subsystems

Two pairs of features were built twice, independently, and never merged:

| Feature | Implementation A | Implementation B |
|---|---|---|
| Gamification badges | `BadgeCalculatorService` + `BadgeAdminController` + `BadgeStudentController` (Engagement Badge) | `FinheroBadgeCalculatorService` + `FinheroAdminController` + `FinheroStudentController` (FinHero Badge) |
| Civic Chamber (referendum/petition) | `EFDController.php:618-772` (student-facing) | `Admin/CivicChamberController.php:334-573` |

Also near-duplicated three times: the supermarket/spending-tracker product-listing controller logic (`SupermarketController`, `SpendingActivitiesController`, and a self-flagged-obsolete triplicate inside `EFDController.php:307-441` — the author's own comment says "Remove Belo[w] 3 later," never done).

## 6. Multi-tenancy / school scoping

There's no middleware or global scope for "which school." Instead, ~40+ individual controller methods (mostly in `AdminEducationController.php`) manually read `session('selected_school')` and branch on it inline. No `SetSelectedSchool` middleware exists. This is the single biggest missed abstraction in the Admin layer — see `planning.md` Phase 2.

## 7. Auth/authorization model

- `AuthServiceProvider::$policies` is empty. No Laravel policies/gates are used anywhere.
- Role checks are done ad hoc per-controller: `$user->role == 4`, `ClosetController::requireAdmin()`, a custom `IsAdmin` route middleware registered in `Http/Kernel.php:66` as `'admin'`.
- **`RouteServiceProvider::boot()` (lines 39-43) applies only `['web','auth']` to the `admin.php` route group — the `admin` middleware is never attached.** The code literally has the comment `// Add 'admin' middleware if needed` next to it, unaddressed. Any authenticated user may be able to reach admin routes unless `routes/admin.php` itself adds the middleware per-route (not reviewed — out of scope, flagged for verification).
  - **Update, Oct 2026 (`fix/admin-access-control` branch):** fixed — `RouteServiceProvider::boot()` now applies `['web','auth','admin']`. `IsAdmin` also grew real role awareness (`User::isSuperAdmin()/isSchoolAdmin()/isTutor()/isAdminOrTutor()`, `User::ROLE_*` constants) and pins non-Super-Admins to their own school via `session('selected_school')` on every request. See §13.

## 8. Scheduling — what's actually cron-driven vs login-driven

`app/Console/Kernel.php` `schedule()` has exactly 3 entries:

1. `everyMinute()` — loops **every** user (`User::all()`, no chunking) and calls `MailboxScheduler::scheduleForEvent('login', ...)` for each, every minute, regardless of whether they logged in.
2. `everyMinute()` — processes due `Transfer` records (`type=later`/`recurring`) and dispatches `ProcessScheduledTransfer` jobs. **This is the only properly queued scheduled work in the app.**
3. `monthlyOn(31, '23:59')` — runs `badges:calculate-monthly` artisan command. Using day 31 means this **silently skips** in Feb/Apr/Jun/Sep/Nov (Laravel scheduler does not clamp to month-end unless `lastDayOfMonth()` is used).

**Monthly salary and monthly fixed bills (rent/school/electricity/water/internet) are NOT scheduled at all.** They only fire from `AuthenticatedSessionController::store()` on login, gated by day-of-month checks (`>=6` for salary via `StatementGenerator`, `>=7` for bills via `ensureMonthlyBills`). A student who doesn't log in during the eligible window that month gets nothing, with no backfill/catch-up job. This is the root timing mechanism behind the original reported bug (expenses "not deducting" — see `bugs.md` #1 and #2).

## 9. Kernel middleware summary

`app/Http/Kernel.php`:
- Global: TrustProxies, HandleCors, PreventRequestsDuringMaintenance, ValidatePostSize, TrimStrings, ConvertEmptyStringsToNull. `TrustHosts` is commented out.
- `web` group: standard Breeze stack, no custom school/tenant middleware.
- `api` group: `throttle:api` + SubstituteBindings only — Sanctum is commented out, so API auth posture is unclear (flagged for verification).
- Route middleware includes `admin => IsAdmin::class` but (per §7) it's not wired into the admin route group at the provider level.

## 10. Config values that are NOT centralized

These are hardcoded independently in multiple files instead of one config file (`config/zedville.php` or similar does not exist for this):

- **Update, Oct 2026:** `config/zedville.php` now exists and centralizes `academic_year_start_month`, `monthly_salary`, `auto_penalty`, and `inactive_days_threshold` (§13). The duplication below predates it; new code should read from this file, not re-hardcode.
- Monthly salary `3952.40` / `4250` — `CitizenActivationController.php:132`, `EFDController.php:163`, `StatementGenerator.php:70`, `BankController.php` (multiple, differing values — see `bugs.md`).
- Emergency fund % (`20`) — `CitizenActivationController.php:52`, `EFDController.php:164`, `StatementGenerator.php:138`.
- Rent (`300`) / School Fees (`1000`) — `CitizenActivationController.php:239,261,278-279`, `AuthenticatedSessionController.php:186,189-190`, and again in `BankController.php`.
- Academic-year start month — inconsistently `9` in three files, `8` (0-based) in a fourth, and `4` in `FinheroPointService.php:21` — a genuine cross-module inconsistency, not just duplication.

## 11. LOC map (Controllers/Services/Providers/Kernel only)

| File | LOC |
|---|---|
| app/Http/Controllers/BankController.php | 2377 |
| app/Http/Controllers/ProfileController.php | 1324 |
| app/Http/Controllers/Admin/AdminEducationController.php | 1114 |
| app/Http/Controllers/EFDController.php | 794 |
| app/Http/Controllers/Admin/AdminDashboardController.php | 582 |
| app/Http/Controllers/Admin/CivicChamberController.php | 573 |
| app/Http/Controllers/Admin/FinheroAdminController.php | 533 |
| app/Http/Controllers/CitizenActivationController.php | 401 |
| app/Http/Controllers/Admin/BadgeAdminController.php | 344 |
| app/Services/BadgeCalculatorService.php | 321 |
| app/Http/Controllers/OrderController.php | 303 |
| app/Http/Controllers/ClosetController.php | 286 |
| app/Services/FinheroBadgeCalculatorService.php | 276 |
| app/Services/ParticipationService.php | 273 |
| app/Http/Controllers/SpendingActivitiesController.php | 273 |
| app/Services/StatementGenerator.php | 269 |
| app/Http/Controllers/Auth/AuthenticatedSessionController.php | 243 |
| app/Http/Controllers/Student/FinheroStudentController.php | 203 |
| app/Http/Controllers/SupermarketController.php | 186 |
| app/Http/Controllers/Admin/CalendarEventController.php | 171 |
| app/Http/Controllers/LoginQuizController.php | 170 |
| app/Http/Controllers/Admin/WellbeingController.php | 170 |
| app/Http/Controllers/Auth/RegisteredUserController.php | 125 |
| app/Http/Controllers/Admin/AdminStudentController.php | 120 |
| app/Http/Controllers/Admin/AdminEmailTemplateController.php | 117 |
| app/Providers/RouteServiceProvider.php | 93 |
| app/Http/Controllers/CartController.php | 77 |
| app/Http/Controllers/Student/BadgeStudentController.php | 74 |
| app/Console/Kernel.php | 73 |
| app/Http/Kernel.php | 68 |
| app/Http/Controllers/QuizController.php | 64 |
| app/Http/Controllers/Auth/NewPasswordController.php | 61 |
| app/Services/FinheroPointService.php | 48 |
| app/Http/Controllers/Auth/PasswordResetLinkController.php | 44 |
| app/Providers/EventServiceProvider.php | 42 |
| app/Services/CitizenActivationService.php | 41 |
| app/Http/Controllers/Auth/ConfirmablePasswordController.php | 41 |
| app/Http/Controllers/CMSController.php | 33 |
| app/Providers/AuthServiceProvider.php | 30 |
| app/Http/Controllers/Auth/PasswordController.php | 29 |
| app/Providers/AppServiceProvider.php | 28 |
| app/Http/Controllers/Auth/VerifyEmailController.php | 28 |
| app/Http/Controllers/Auth/EmailVerificationNotificationController.php | 25 |
| app/Http/Controllers/Auth/EmailVerificationPromptController.php | 22 |
| app/Providers/BroadcastServiceProvider.php | 21 |
| app/Http/Controllers/StatementController.php | 18 |
| app/Http/Controllers/Controller.php | 13 |
| **TOTAL** | **12521** |

## 12. What's already good (use these as the template for refactors)

- `CitizenActivationService::getNextRoute` — small, pure, single-responsibility state machine.
- `ParticipationService::award`/`alreadyEarned` — clean dedup logic, monthly cap in one place, best-documented service in the codebase.
- `OrderController::placeOrder` — the only balance-mutating flow wrapped in `DB::beginTransaction()/commit()/rollBack()` with a real try/catch. Needs `lockForUpdate()` added, but structurally it's the right shape.
- `Auth/*` controllers (Breeze scaffolding) — small, single-purpose, framework-idiomatic.
- `ClosetController` — has consistent `requireAuth`/`requireAdmin`/`requireAdminOrTutor` guards and private formatter methods for JSON shaping, despite bypassing Eloquent.

See `bugs.md` for the full defect list and `planning.md` for the fix sequencing.

## 13. Tutor-power subsystems added October 2026

Built against `1. Zedville_Admin_Roles_Permissions_v1 (2).docx` (the canonical Admin Roles & Permissions reference — see §0 context in that doc for the full role hierarchy). Two phases: first the Tutor dual-mode UI, then six specific Tutor/staff powers the doc described but the code didn't have yet. Everything here deliberately reuses the patterns above rather than inventing new ones — see the cross-references.

**Tutor dual-mode UI** (`fix/admin-access-control` branch):
- `users.autopilot_enabled` (boolean, default true) — a Tutor's own citizen account runs on Autopilot by default (per §5.2 of the doc); `AutopilotController::toggleSelf()` lets them switch to manual. `AutopilotApply` (the existing month-end command) now excludes tutors who opted out.
- `CitizenActivationService::provisionForTutor()` — silently gives a Tutor the same `BankAccount`/mandatory-bills/first-statement a student gets through the onboarding wizard, called on every Tutor login (idempotent). A Tutor never sees the wizard, so this fills the same records directly from their profile.
- Every page in both `layouts.admin` (Tutor Mode) and `layouts.profile` (Student Mode) now shows a persistent colored banner naming which mode you're in, with a one-click switch — the closest practical equivalent of the doc's §8 "Tutor Dual-View Toggle," without a shared single-toggle widget (the two modes are structurally different layouts, not components of one page).
- `components/school-badge.blade.php` — one reusable partial for "which school am I looking at," used in the admin header and on the Autopilot/City Proposals pages. Only Super Admin sees the switch-school affordance (§6 multi-tenancy — everyone else is pinned via `IsAdmin`).
- `TutorStudentController` (`routes/admin.php` → `/admin/my-students`) batch-loads a one-glance summary per student (this month's `activity_completions` count, Engagement badge, FinHero badge, latest `MoodLog`, current balance from `Transaction1`) in a fixed number of queries regardless of class size, plus a per-student snapshot page (`/admin/my-students/{id}`) composing badge history + budget (from `BankStatement.statement_data`) + mood trend in one place.

**Six specific powers from the doc's permission matrix, not previously built:**

| Power (doc §) | What was added | Reuses |
|---|---|---|
| Petition 3-month auto-expiry (§6, §7) | `petitions:expire-stale` command, scheduled daily | Existing `petitions.status`/`created_at` columns — no new table |
| Tutor inactive-student alerts (§2.3) | `students:alert-inactive` command, scheduled daily | `NotificationService` (§ below) entirely — zero new UI |
| Assignment due-date extensions (§2.3) | `assignment_extensions` table, `Tutor\AssignmentExtensionController`, `/admin/extensions` | `User::canManageStudent()` (§6 scoping pattern) |
| Salary bonuses, one-time/recurring (§2.3, §4.2) | `salary_bonuses` table, `Admin\SalaryBonusController`, `bonuses:apply` command (scheduled daily) | `BalanceService::lockedBalance()` + `Transaction1` ledger (§3-4) and `LedgerController`'s mandatory-reason/append-only pattern exactly |
| City name & quote proposal/approval (§2.4, §3) | `city_proposals` table; student submit in `EFDController` (matches where petitions submit), staff approve in `Admin\CivicChamberController` (matches where petitions approve) | The existing split-brain Civic Chamber pattern (§5) — deliberately extended rather than resolved, so as not to silently change the architecture of an unrelated feature while building a new one |
| 12-month data retention (§7) | `users.anonymized_at`, `students:anonymize-inactive` command, scheduled monthly | Nothing deleted — anonymizes PII fields only, ledger/badge/petition history untouched, same append-only philosophy as everything above |

**A new scheduling principle, stated once here because it applies to all three money/notification commands above:** `ProcessScheduledTransfer` (§8) self-dispatches its own next occurrence via a queued `->delay()` *and* is independently re-polled every minute by `Console/Kernel.php` — two triggers for one event. `bonuses:apply`, `students:alert-inactive`, `petitions:expire-stale`, and `students:anonymize-inactive` are all instead a single daily/monthly `$schedule->command(...)` entry with one idempotency check each (`last_applied_at`, a 7-day notification lookback, `status`, `anonymized_at` respectively) — one trigger, one source of truth, matching the safer shape already used by `monthly-finance-backfill` (§8), not the Transfer precedent.

**Known v1 scope cut:** assignment extensions are recorded and listed for the Tutor but not yet surfaced inside the student-facing FullCalendar widget (that JS is shared and complex — a deliberate fast-follow, not a silent gap).

**Known compliance caveat:** the 12-month anonymization job implements a reasonable technical default (plain login-inactivity). The doc's actual trigger ("does not continue to the next level or becomes inactive") may need to be tied to academic-year rollover instead — confirm with the school before treating this as GDPR/BDSG sign-off.

**Notification system** (referenced above, exists independently of this work but is new since the original snapshot): `App\Services\NotificationService` writes to `app_notifications` (not Laravel's built-in `notifications` table — `users` already uses `Notifiable` for something else). Delivered via a bell/drawer injected into every page extending `layouts.profile` by a global view composer in `AppServiceProvider::boot()`. Most events are logged via Eloquent Observers (`app/Observers/*`), not inline controller code — the new inactive-student alert is the first consumer that calls `NotificationService::log()`/`logForUsers()` directly from a scheduled command instead, since inactivity isn't a model-write event an Observer could hook.

## 14. Badges, Bank Admin, and the Grade/Class split — how the pieces connect (October 2026)

These four pieces of work all touch the same underlying systems (§3-6 above) and were built in sequence against a set of product docs Daniel supplied directly, each comparing the doc against live code before changing anything. Reading them together, not in isolation, matters: the Grade/Class split changed what "a school" means structurally, the Bank Admin work exposed existing ledger code through a real UI for the first time, and the FinHero point-defaults work is the first piece of the FinHero scoring rebuild — which the next phase (not started) will build directly on top of.

### 14.1 Grade/Class split
`App\Models\Grade` (new, table `grades`) and `App\Models\SchoolClass` (table `classes`, formerly confusingly also called `Grade`) are now separate: a Grade (e.g. "Grade 8") can contain several Classes (e.g. "8A", "8B"), each with its own Tutor(s) and roster, sharing the Grade's curriculum Level. `GradeController` (CRUD for Grades) and `GradeGeneralInformationController` (`/admin/grades/{id}/general-information`) exist for this. `users.grade` is unchanged in meaning — it has always pointed at a *Class* id (`SchoolClass`, not `Grade`), despite the column name; see `User::gradeRelation()`'s doc comment. This split is the foundation the Bank Admin student-list Class filter (§14.2) is built on.

### 14.2 Bank Admin Functions (§ "Bank" nav group)
Driven by `Bank Admin Functions.pdf` / `Zedville_Admin_Structure_Proposal_v1.pdf` (comparison done 2026-10-12, see `planning.md` Phase 5). The backend (`LedgerController::reverse`/`reset`, `SalaryBonusController`) already existed and matched the spec closely — the actual gap was that it was scattered across three disconnected admin pages, each with its own separate student-picker dropdown. Consolidated onto `admin/student/details/{id}`'s new "Bank" tab (balance, Reset Balance, Reverse Transaction with mandatory reason, Salary Management with a Type field), reusing the existing controllers/routes unchanged — no duplicated backend logic, just a different calling page. Nav reorganized: "City Bank" renamed **"Bank"**, now groups Accounts/Reverse-Adjust/Salary Bonuses together (previously spread across "Users" and a bare top-level item).

### 14.3 Badge system consolidation
Confirmed directly (not deferred) that "Engagement Badge" (`BadgeCalculatorService`) and "FinHero Badge" (`FinheroBadgeCalculatorService`, §5 table above) are both genuinely required — the student profile page deliberately shows them as two distinct side-by-side cards, and their scoring models are different enough (pass/fail gate+matrix vs. weighted percentage ladder) that unifying them into one generic engine would be a false abstraction. What *was* truly duplicated between the two services — academic-year calculation, accumulated-points summing, threshold lookup — is now shared via `App\Services\Concerns\CalculatesBadgeAccumulation`. See `planning.md` Phase 3 #1 / `bugs.md` #11.

### 14.4 FinHero global point defaults — the first piece of a larger, not-yet-finished rebuild
`Zedville_FinHero_Point_Values_v1.pdf` (comparison done 2026-10-18) revealed the live FinHero scoring doesn't match the doc anywhere near as closely as the badge/bank work above did. The doc specifies a tiered "Approach System" — 3/2/1/0 points per question depending on which attempt tier the student is in, with escalating hints — applied identically across five registered activities (Task 1-5) totaling 87 points, plus a fixed 10 (Daily Quiz) + 10 (Library) = 107 total available in a full month. Live code: Task 1 has a tier array but with different numbers; Tasks 2-4 are flat "1 point, first try only, no tiers, no escalating hints"; Task 5 doesn't exist in this repository at all (not on `main`, not on this branch, not on the `origin` remote after a fresh fetch — if it exists, it was deployed to Hostinger without ever being committed here, which is a standing risk worth resolving independently of the badge work).

Given that gap, Daniel chose to sequence the work: build the **global point-defaults admin layer first** (done), the actual per-task Approach System/point-value rebuild **task by task after** (Task 1 done, Tasks 2-4 and Task 5 open). What's done:
- `finhero_activity_defaults` (new table, one row per `activity_key`): the single default point value (and, for tasks, default salary) applied across every school. Seeded with the doc's real §3 table for all five tasks (`task_1`=3 ... `task_3`=33) via migration, including `task_5` even though its view doesn't exist yet.
- `finhero_activity_registry.max_points`/`salary` (already nullable) now mean "no override — inherit the default" when null; an admin-set value on a specific row still overrides it. Resolution (`row value ?? default value`) is wired into the actual scoring reads — `FinheroBadgeCalculatorService::getTotalAvailable()`/`getActivityPoints()`, `FinheroStudentController::awardPoints()`/`savePoints()` — not just the admin display, so editing a default has real effect on points a student can earn going forward.
- New Super-Admin-only screen, `admin/finhero/point-defaults` ("FinHero Point Defaults" in the nav, under Education) to view/add/edit defaults.
- **`App\Services\FinheroApproachSystem`** — the doc's §2 tier table (`[3,2,1,0]` points over `[2,2,2,∞]` wrong attempts) as the single source of truth. The client (a task view) only ever reports which tier a student is on; the server resolves that tier to a point value, same "never trust a raw point number from the browser" principle `awardPoints()` already used for `source_key`.
- **Task 1 rebuilt to match** (`resources/views/finhero/task-1.blade.php`'s `APPROACHES` array + all point-display text), using the new tier service via `FinheroStudentController::savePoints()`'s new `approach` parameter.
- **A real, pre-existing, live bug found and fixed along the way:** `savePoints()` validated the per-question `source_key` ("task_1_question_1") directly against `finhero_activity_registry.activity_key`, which only ever has rows for the *whole task* ("task_1") — every save call, from any task, would have 422'd. `finhero_student_points` had zero rows in the live dev DB, consistent with this never having worked for anything. Fixed by deriving the base `activity_key` (stripping `_question_N`) for the registry/active-task check, while still bookkeeping earned points by the full compound key — this incidentally unblocks Tasks 2-4's existing flat per-question saves too, not just Task 1's new tiered ones, even before their own tier rebuild happens.

**Tasks 2-4 rebuilt the same way** (2026-10-18): same `APPROACH_TIERS` mirror + `redactFinalStep()` pattern in each view, same `savePoints()` `approach` param, same server-side point resolution. Task 2 (8 questions, 24 pts) and Task 4 (8 questions, 24 pts, mixing "number" and "choice" question types — choice questions have no extra step content to redact, so their fuller-hint tier just keeps showing the existing hint, which already stops short of the answer) match the doc exactly. **Task 3 doesn't**: the doc registers it as 11 questions (33 pts, Part A + Part B), but only 10 were ever built in `buildQuestions()` — the 11th isn't invented here, that's curriculum content. True achievable max is 30; `finhero_activity_defaults.task_3` was corrected to 30 (not the doc's 33) via a follow-up migration, with a regression test pinning that number so a future question addition is deliberate, not silent drift.

**A second real, pre-existing bug found while verifying the rebuild end-to-end:** `savePoints()` stores points with `source_type='task'`, but `FinheroBadgeCalculatorService::getActivityPoints()` only ever summed `source_type='activity'` rows (the separate `awardPoints()` endpoint's bucket, for generic "IT calls this when a student finishes something" activities). Every point a student earned from any Task, correctly saved to `finhero_student_points`, was silently never counted toward their monthly FinHero total or badge at all — meaning the entire Approach System rebuild would have had zero visible effect on a student's actual badge without this fix. Resolved by rolling per-question `'task'` rows up to their parent task's `activity_key` (stripping `_question_N`) alongside the existing `'activity'` rows, both capped at the same active-activity max.

**Still open:** Task 5 (pending Daniel sharing whatever exists on Hostinger — or, per the user, it may be live under the name "Match Needs and Wants" rather than literally "task-5"; searched this repo broadly, including the Admin Structure doc's hint that it might live under the bank-statement flow as "Month Statement," and found nothing). Also still open for every task: no school has an actual `finhero_activity_registry` row for any task yet (the table is empty in the live dev DB) — a Super Admin needs to register/activate each one via the Activity Manager before any student can actually earn points from it, independent of the code now being correct.
