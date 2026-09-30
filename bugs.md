# Zedville — Bugs & Defects (Controllers / Services / Providers / Kernel)

Severity: 🔴 Critical (data-corrupting or runtime-fatal) · 🟠 High (real-world incorrect behavior) · 🟡 Medium (correctness risk under load/edge case) · ⚪ Low (cleanliness/inconsistency)

**Re-verified against live code 2026-09-30** (first time this list was checked against the actual codebase since it was written): #1, #2, #3, #4, #6, #7, #8, #9, #12, #13, #15 were already fixed, and #5 was fixed during this same pass (one remaining call site, `OrderController::placeOrder`) — all removed below. Every Critical and High severity item is now resolved; what's left is architectural/cosmetic. Remaining items were re-confirmed still present. If you're reading this later and something here seems wrong, check the code directly rather than trusting this list blindly — that's exactly how it went stale the first time.

## 🟡 Medium

### 10. Two independent, divergent implementations of the same feature (Civic Chamber)
`EFDController.php:618-772` (student-facing) and `Admin/CivicChamberController.php:334-573` both implement referendum/petition submit/vote/sign/result logic independently. Any bugfix or rule change applied to one will not propagate to the other — they will drift.
**Fix:** consolidate into one `CivicChamberService`, called from both controllers. See `planning.md` Phase 2.

### 11. Two parallel badge systems duplicate override/recalculate/reset logic
"Engagement Badge" (`BadgeCalculatorService` + `BadgeAdminController` + `BadgeStudentController`) and "FinHero Badge" (`FinheroBadgeCalculatorService` + `FinheroAdminController` + `FinheroStudentController`) are structurally near-identical but maintained as two separate codebases. Any shared bugfix must be applied twice (and per #8, may already be inconsistently buggy in only one).
**Fix:** long-term, evaluate whether both systems are still product-required; if so, extract a shared `BadgeEngine` service parameterized by badge-type config. See `planning.md` Phase 3.

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

---
See `planning.md` for how these are sequenced into fix phases, and `ProjectArchitecture.md` for the system context behind each finding.
