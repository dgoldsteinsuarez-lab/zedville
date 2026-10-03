# Zedville - Deployment checklist (branch `fix/admin-access-control` -> live)

Written for the product owner and whoever helps with Hostinger. Do these steps IN ORDER.
Do not skip the backup. Nothing here has been deployed yet.

## 0. Before you start
- [ ] You have reviewed the branch on GitHub (Pull Request diff) and are happy with it.
- [ ] Pick a quiet time (no students online).
- [ ] You have Hostinger hPanel access (Files + Databases).

## 1. Back up (cannot be skipped)
- [ ] hPanel -> Databases -> phpMyAdmin -> Export -> **Custom**, structure **and data** -> download the .sql.
      (Keep this file private: it contains real student data. Store it somewhere safe, NOT in GitHub.)
- [ ] hPanel -> Files -> compress `public_html` to a zip and download it (excluding `vendor` is fine).
- [ ] Check hPanel -> Backups to see whether Hostinger also has an automatic backup. Do not assume it.

## 2. Upload the new code
- [ ] Upload the changed files (or pull the branch) into `public_html`.
- [ ] Do NOT overwrite `.env` on the server. Do NOT upload your local `.env`.
- [ ] If `composer.json`/`composer.lock` changed (they did not in this branch), run `composer install --no-dev` on the server.
- [ ] `public/asset` already exists on the server; it was only copied into the repo as a backup.

## 3. Run the database migrations
Run on the server (SSH or hPanel terminal) from the project folder:

    php artisan migrate --status        # shows what is pending
    php artisan migrate --force

Expected pending migrations are all in `database/migrations` named `2026_09_30_*`, `2026_10_01_*`,
`2026_10_02_*`, `2026_10_03_*`. They were tested against a copy of the real live structure and are safe to
run on tables that already exist (they only add what is missing). They ADD: `class_tutor`, `grade_levels`,
`autopilot_periods`, `users.nickname`, `users.must_change_password`, `classes.grade_level_id`,
`petitions/referendums.sid + class_id`, `mascots.sid`, some timestamp columns. Nothing is dropped or emptied.

**Critical one:** `2026_10_03_000007_backfill_civic_chamber_school` gives old petitions/referendums a school.
If it is skipped, students will no longer see any old referendum or petition.

Then clear caches:

    php artisan config:clear && php artisan route:clear && php artisan view:clear

## 4. Nicknames (automatic)
Existing accounts get their first name (max 15 chars) as nickname automatically (migration `2026_10_03_000008`).

## 4b. Ask IT to check the live users table (read-only query in phpMyAdmin)
The live `users` table has NO database-level uniqueness on `email` or `citizenId` (only the form checks it).
Run these two; both should return zero rows. Do not "fix" anything without a backup:

    SELECT email, COUNT(*) FROM users GROUP BY email HAVING COUNT(*) > 1;
    SELECT citizenId, COUNT(*) FROM users WHERE citizenId IS NOT NULL GROUP BY citizenId HAVING COUNT(*) > 1;

If both are empty, adding unique indexes later is safe. If not, tell me and we plan the cleanup first.

## 5. Smoke test (10 minutes)
Log in as each role and check:
- [ ] Super Admin: dashboard, School Domain, School Users (create a Tutor, note the one-time password), Reverse / Adjust page opens.
- [ ] School Admin (of a real school): sees ONLY their own school's students/classes. Grade / Class page opens.
- [ ] Tutor: "My Students" lists only their class(es); cannot open School Users or Role pages (403 expected).
- [ ] Student: dashboard, bank transfer between two test students works, civic chamber shows referendums.
- [ ] A newly created Tutor is forced to change the temporary password on first login.

## 6. Scheduled jobs (cron)
- [ ] hPanel -> Advanced -> Cron Jobs must have: `* * * * * php /home/<user>/public_html/artisan schedule:run`
- [ ] Ask whoever set it up whether it has been running (the old code had a bug that could stop it - fixed in this branch).
- [ ] Autopilot month-end job (`autopilot:apply --write`) only writes a zero-amount history line. No money moves.

## 7. Rollback (if something is badly wrong)
1. Put the old files back from the zip you downloaded in step 1.
2. Restore the database ONLY if data was damaged: phpMyAdmin -> Import the step-1 .sql.
   (The migrations only add things, so for a plain code problem restoring the code alone is enough.)
3. `php artisan config:clear && php artisan view:clear && php artisan route:clear`

## Known open items (not blockers)
- FinHero points can be claimed from the browser without proof (capped per month, rate-limited). Real fix = move game logic to the server.
- The live web root is the project root: `composer.json` and `vendor/` are reachable by URL. `.env` and `.git` are blocked.
- Autopilot does NOT yet apply class-average money (rules pending).
- Transfers are two unlinked ledger lines; reversing a transfer means reversing both lines.
