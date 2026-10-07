# Session Notes — Local XAMPP Setup (2026-10-04)

Log of what was done in this Claude Code session to get Zedville running locally.

## 1. Investigated the mismatch

User's `C:\xampp\htdocs\zeville asset` folder (where the project was thought to live) only contained leftover theme assets (`admin/`, `admin1/`, `front/`, `front1/` — css/js/images) and one SQL dump. No Laravel code (`composer.json`, `artisan`, `app/`, `routes/`) existed there.

## 2. Located the real app

Searched Downloads/Desktop for a recently-downloaded server backup and found the actual Laravel codebase already in place at:

**`C:\Users\HP\Desktop\DANIEL\zedville-main`**

— complete with `artisan`, `composer.json`, `vendor/`, `.env`, `app/`, `routes/`, `database/migrations/`, a git repo, etc.

Also found `C:\Users\HP\Desktop\DANIEL\hostinger-export\` containing 3 SQL dumps:
- `live_compat.sql` — production data, collation downgraded to `utf8mb4_unicode_ci` (MariaDB-compatible) ← correct one
- `u767746464_zedville_db.sql` — same data, `utf8mb4_uca1400_ai_ci` collation (MySQL 8.0.30+ only, incompatible with XAMPP's MariaDB 10.4)
- `local_laravel_backup_20261002.sql` — older/partial (39 vs 64 tables), unused

## 3. Checked existing local databases

Both `laravel` and `zedville_live` databases already existed in the local MariaDB instance, already migrated (57/57) and seeded (10 users). No import was needed.

## 4. Wired up Apache + hosts

- Added a vhost to `C:\xampp\apache\conf\extra\httpd-vhosts.conf` pointing `zedville.local` → `zedville-main/public`
- Added `127.0.0.1 zedville.local` to the Windows hosts file (required a UAC-elevated PowerShell command, approved by the user)
- Updated `.env`: `APP_URL=http://zedville.local`
- Ran `php artisan storage:link`, cleared config cache
- Restarted Apache via `apache_stop.bat` / `apache_start.bat` (not a Windows service here, so `httpd -k restart` doesn't work)

## 5. Fixed login

User reported no login worked. Verified via curl that the app itself (CSRF, sessions, `Auth::attempt`) was functioning correctly — the actual problem was that the 10 existing test users (`*@testschool.example`, `*@otherschool.example`) had bcrypt hashes from an unknown original password, not Laravel's factory default.

**Fix:** reset all users' passwords in the local `laravel` database to `password` via:
```
php artisan tinker --execute="\App\Models\User::query()->update(['password' => bcrypt('password')]);"
```
Confirmed working — login now redirects to `/admin/dashboard` (HTTP 302) instead of back to `/login`.

This reset was only applied to the local `laravel` DB. `zedville_live` still has the original unknown hashes and is unused.

## Result

Full environment details (vhost config, DB location, login credentials, restart commands) are documented in [`LOCAL_SETUP.md`](LOCAL_SETUP.md) in this same folder.

**Still outstanding:** the stale `C:\xampp\htdocs\zeville asset` folder (old theme assets, unused) — not yet cleaned up, pending user decision.
