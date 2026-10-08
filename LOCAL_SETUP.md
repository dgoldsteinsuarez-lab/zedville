# Zedville — Local Development Setup (XAMPP)

This documents how this Laravel app is wired up to run locally on this machine via XAMPP.

## Project location

- **App code:** `C:\Users\HP\Desktop\DANIEL\zedville-main` (this folder — has `artisan`, `composer.json`, `app/`, `routes/`, `vendor/`, `.env`)
- **Not the app:** `C:\xampp\htdocs\zeville asset` only contains leftover theme assets (css/js/images for admin/admin1/front/front1) and an old SQL dump. It is not used to serve the site.

## Local URL

**http://zedville.local**

Wired up via:
- Apache vhost in `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
  ```
  <VirtualHost *:80>
      ServerName zedville.local
      DocumentRoot "C:/Users/HP/Desktop/DANIEL/zedville-main/public"
      <Directory "C:/Users/HP/Desktop/DANIEL/zedville-main/public">
          Options Indexes FollowSymLinks
          AllowOverride All
          Require all granted
      </Directory>
      ErrorLog "logs/zedville.local-error.log"
      CustomLog "logs/zedville.local-access.log" common
  </VirtualHost>
  ```
- Windows hosts file entry (`C:\Windows\System32\drivers\etc\hosts`):
  ```
  127.0.0.1    zedville.local
  ```
- `.env`: `APP_URL=http://zedville.local`

## Database

- **Engine:** MariaDB 10.4 (bundled with XAMPP)
- **Host / port:** `127.0.0.1:3306`
- **Database name:** `laravel` (set via `DB_DATABASE` in `.env`)
- **Credentials:** user `root`, no password
- **Data files on disk:** `C:\xampp\mysql\data\laravel\`
- **Browse it via phpMyAdmin:** http://localhost/phpmyadmin → select `laravel` in the left sidebar

Already migrated (57/57 migrations) and seeded (10 users) — no import needed on this machine.

There is also an unused second database, `zedville_live`, in the same MariaDB instance — a leftover export, not referenced by `.env`. Safe to ignore or drop.

### SQL dump reference

Of the three dumps found in `C:\Users\HP\Desktop\DANIEL\hostinger-export\`:
- `live_compat.sql` — production data with collation downgraded to `utf8mb4_unicode_ci` for MariaDB 10.4 compatibility. **Use this one** if re-importing locally.
- `u767746464_zedville_db.sql` — same data, but uses `utf8mb4_uca1400_ai_ci` collation (MySQL 8.0.30+ only — **not compatible** with XAMPP's bundled MariaDB 10.4).
- `local_laravel_backup_20261002.sql` — older/partial dump (39 tables vs. 64), not used.

## Login credentials (local only)

All 10 existing users had unknown password hashes (not Laravel's factory default), so every password was reset to `password` for local testing:

| Role | Email | Password |
|---|---|---|
| Superadmin | alex.superadmin@testschool.example | password |
| School admin | sam.schooladmin@testschool.example | password |
| School admin | pat.admin@otherschool.example | password |
| Tutor | jordan.tutor@testschool.example | password |
| Tutor | robin.tutor@testschool.example | password |
| Tutor | terry.new@testschool.example | password |
| Tutor | samt@testschool.example | password |
| Student | maxi.test@testschool.example | password |
| Student | riley.test@testschool.example | password |
| Student | quinn.test@testschool.example | password |

Log in at http://zedville.local/login — admin accounts redirect to `/admin/dashboard`.

**This password reset only applies to this local `laravel` database.** Never do this against the live/production database.

## Other local setup steps already done

- `php artisan storage:link` — created `public/storage` → `storage/app/public`
- `php artisan config:clear`
- Apache and MySQL services were already running via the XAMPP control panel (not installed as Windows services — restart with `C:\xampp\apache_stop.bat` / `apache_start.bat` if you change vhost config)

## Restarting Apache after config changes

```
C:\xampp\apache_stop.bat
C:\xampp\apache_start.bat
```

(`httpd.exe -k restart` won't work here since Apache isn't running as a Windows service in this setup.)

## Running the test suite

Feature tests use `RefreshDatabase`, which runs `migrate:fresh` against whatever
database `DB_DATABASE` resolves to. Without an override, that's the real `laravel`
dev database (manual-testing data, the 10 seeded accounts above) — running tests
would wipe it. A `.env.testing` file (gitignored, not committed — recreate it
locally if missing) points tests at a separate `laravel_testing` database instead:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_testing
DB_USERNAME=root
DB_PASSWORD=
```
(plus the same `APP_KEY` as `.env`, `CACHE_DRIVER=array`, `SESSION_DRIVER=array`, `MAIL_MAILER=array`.)

Create the database once with:
```
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Then run tests with `php artisan test` — Laravel automatically loads `.env.testing`
over `.env` when `APP_ENV=testing` (set in `phpunit.xml`).
