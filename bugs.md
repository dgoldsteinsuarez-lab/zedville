# Zedville — Bugs & Defects (Controllers / Services / Providers / Kernel)

Severity: 🔴 Critical (data-corrupting or runtime-fatal) · 🟠 High (real-world incorrect behavior) · 🟡 Medium (correctness risk under load/edge case) · ⚪ Low (cleanliness/inconsistency)

**Re-verified against live code 2026-09-30** (first time this list was checked against the actual codebase since it was written): #1, #2, #3, #4, #6, #7, #8, #9, #12, #13, #15 were already fixed, and #5 was fixed during this same pass (one remaining call site, `OrderController::placeOrder`) — all removed below. Every Critical and High severity item is now resolved; what's left is architectural/cosmetic. Remaining items were re-confirmed still present. If you're reading this later and something here seems wrong, check the code directly rather than trusting this list blindly — that's exactly how it went stale the first time.

## 🟡 Medium

### 10. ~~Two independent, divergent implementations of the same feature (Civic Chamber)~~ — RESOLVED 2026-10-07
Re-checked against live code: `Admin/CivicChamberController`'s student-facing methods (`civicChamber()`, `castVote()`, `submitPetition()`, `signPetition()`, `referendumResult()`, `petitionSignatureCount()`) were never routed — `routes/efd.php` wires all real student-facing petition/referendum traffic to `EFDController`, which already has the correct Grade/Class-scoped logic. Deleted the dead duplicates instead of building a shared service; there was no actual drift risk since the admin-controller copies were unreachable.

### 11. Two parallel badge systems duplicate override/recalculate/reset logic
"Engagement Badge" (`BadgeCalculatorService` + `BadgeAdminController` + `BadgeStudentController`) and "FinHero Badge" (`FinheroBadgeCalculatorService` + `FinheroAdminController` + `FinheroStudentController`) are structurally near-identical but maintained as two separate codebases. Any shared bugfix must be applied twice (and per #8, may already be inconsistently buggy in only one).
**Fix:** long-term, evaluate whether both systems are still product-required; if so, extract a shared `BadgeEngine` service parameterized by badge-type config. See `planning.md` Phase 3.

## ⚪ Low (cleanliness — not urgent, batch these during refactor passes)

### 16. ~~Large blocks of commented-out dead code~~ — RESOLVED 2026-10-07
Deleted all of the dead blocks listed below, plus the three dead draft `awardPoints`/`taskPage`/`savePoints` bodies in `FinheroStudentController.php` found during this pass (superseded by the already-live, server-validated versions):
- `ProfileController.php` — dead `getCityMood`/`findClosestMood`, dead `showMail` draft, three dead `storesurvey` drafts.
- `LoginQuizController.php` — dead alternate `getTodayQuestion` (month-based variant) + inline commented-out lines.
- `EFDController.php` — dead commented-out old `index()`.
- `CartController.php` — dead superseded `save`/`get`/`clear` bodies.
- `ClosetController.php` — dead alternate `fetchCloset` implementations + dead `formatClosetItem`/`formatCatalogItem` duplicates.
- `AdminEducationController.php` — all scattered commented-out sid-filter ternary variants.
- `AuthenticatedSessionController.php` — dead mailbox-on-first-login block.
- `Admin/CivicChamberController.php` — the dead duplicate student-facing methods, see #10.
- `AdminDashboardController.php` — dead orphaned `updatepassowrd` (typo'd, unrouted; the live route uses the correctly-spelled `updatePassword`).

### 17. Naming typos/inconsistencies
~~`emmengercyfundintrest`~~ — RESOLVED 2026-10-07, renamed to `emergencyFundInterest` across `EFDController.php`, `BankController.php`, and the 3 blade views that referenced it (`bank-my-account`, `educational-finance-departmentex`, `bank/partials/dashboard`) — pure identifier rename, no DB involved. ~~`updatepassowrd`~~ — RESOLVED, see #16. `aviableblance` — not found in current codebase, already fixed previously. Still open: `backaccountnumber` used as an actual DB column name (misspelled "bank") — needs a migration + full call-site audit, do NOT do this casually.

### Added/fixed 2026-10-03 (verified against the real live schema export)
- FIXED: scheduler crash - `withoutOverlapping('name')` (a string where minutes are expected) in `app/Console/Kernel.php` made `schedule:list/run` throw; can stop every scheduled job. Ask IT whether the live cron has been running.
- FIXED: School Admins/Tutors saw every school's data (nothing set the school at login) and could switch school via `/admin/school`; now pinned in `IsAdmin`.
- FIXED: Admin password change route pointed to a method that did not exist.
- FIXED: Grade/Class edit/delete not school-scoped; delete orphaned students; name checked against `roles`.
- FIXED: staff could open any student's closet; any student could read any referendum's results.
- RETRACTED: "13-char account number overflows varchar(10)" - false on live (`varchar(13)`); the original `123000-xxxxxx` format is restored.
- DEPLOY NOTE: `2026_10_03_000007_backfill_civic_chamber_school` must run, or old referendums/petitions (sid NULL) vanish for students.
- RESOLVED 2026-10-07: `finhero/award-points` and `save-points` now compute points server-side from `finhero_activity_registry.max_points`/already-earned sums — the client can no longer submit an arbitrary `points` value. The old client-trusting drafts were dead commented-out code; removed per #16.
- OPEN: live web root is the project root (`vendor/autoload.php`, `composer.json` reachable); `.env` and `.git` are blocked (403) but this is fragile.

---
See `planning.md` for how these are sequenced into fix phases, and `ProjectArchitecture.md` for the system context behind each finding.
