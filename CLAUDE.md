# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

JAXPAY — school digital wallet, PHP native (no framework) + MySQL. Two front ends over one DB: a mobile-styled web app for users (`jaxpay/halaman/`) and an admin dashboard (`jaxpay/admin/`). All app code lives under `jaxpay/`; the repo root only holds Composer files for PHPMailer.

## Running it

XAMPP-style local dev, no build step:

- Serve `jaxpay/` via Apache/PHP (e.g. XAMPP htdocs), or `php -S localhost:8000` from inside `jaxpay/`.
- Import `jaxpay/database/jaxpay.sql` into MySQL as `jaxpay_db`.
- DB and SMTP config in `jaxpay/koneksi.php` reads from env vars (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `SMTP_*`) with sane localhost defaults (`root`/no password/`jaxpay_db`) if unset — no `.env` required for local, but `.env.example` documents the vars if you want to override.
- PHPMailer is installed via Composer at the **repo root** (`composer install` in `/`, not inside `jaxpay/`). `jaxpay/composer.json` also exists but its `vendor/` is not what gets loaded — `proses/*.php` require `../../vendor/autoload.php`, i.e. the root `vendor/`. Don't get confused by the two composer.json files.
- Without SMTP configured (or on send failure being tolerated), OTP flows run in "demo mode": the generated code is returned in the JSON response under `demo_otp` instead of emailed.

### Local setup (no Docker), step by step

1. Install XAMPP (or standalone Apache/PHP + MySQL + Composer). PHP 8.x, MySQL/MariaDB.
2. Clone/extract this repo. If `vendor/` at repo root is already present (bundled in the zip), skip step 3.
3. From the **repo root** (not `jaxpay/`): `composer install` — installs PHPMailer into root `vendor/`.
4. Start MySQL, create a database named `jaxpay_db` (any client, e.g. phpMyAdmin or `mysql -u root -e "CREATE DATABASE jaxpay_db"`).
5. Import the schema: `mysql -u root jaxpay_db < jaxpay/database/jaxpay.sql`.
6. Point your web server's document root at `jaxpay/` — e.g. copy/symlink the whole repo into XAMPP's `htdocs/jaxpay` and browse `http://localhost/jaxpay/`. Or, quicker for testing: `cd jaxpay && php -S localhost:8000`, then browse `http://localhost:8000/`.
7. Defaults in `koneksi.php` (`DB_HOST=localhost`, `DB_USER=root`, `DB_PASS=` empty) match a stock XAMPP MySQL — no config edit needed. If your MySQL user/password differs, either export env vars before starting PHP (`DB_USER=... DB_PASS=... php -S localhost:8000`) or edit the `getenv(...) ?: 'default'` fallback values directly in `koneksi.php`.
8. SMTP is optional. Without it, OTP codes appear in the API JSON response as `demo_otp` instead of being emailed — fine for local testing. To send real email, set `SMTP_USER`/`SMTP_PASS`/etc. as env vars (see `.env.example`) before starting PHP, using a Gmail App Password (not your normal password).
9. Demo login accounts are listed in `jaxpay/README.md`.

## Security note (known issue)

`jaxpay/koneksi.php` has a real Gmail address and app password committed in plaintext (tracked in git history since the initial commit). Don't add new secrets the same way — if touching this file, prefer pulling credentials from environment variables instead of hardcoding new ones.

## Tests

`jaxpay/tests/api_test.php` is a standalone script (`php tests/api_test.php`) that POSTs to the OTP endpoints and asserts on the JSON response. It's not a framework/suite — just a runnable smoke check, run manually, no assertions library.

## Architecture

**No router, no framework, no classes/PSR-4 for app code.** Every `.php` file under `halaman/`, `admin/`, `auth/`, `proses/` is a directly-hit endpoint or page — URLs map 1:1 to file paths.

- `koneksi.php` — single global bootstrap: opens the `mysqli` connection as `$koneksi`, defines config constants, and holds shared helpers (`sanitize()`, `formatRupiah()`, `generateKode()`, `generateMemberID()`). Nearly every page/endpoint `require`s this first.
- `auth/session.php` — session helpers (`requireLogin()`, `requireAdmin()`, `isLoggedIn()`, `getUser()`, `getAdmin()`). User auth uses `$_SESSION['user_id']`/`$_SESSION['user']`; admin auth is a **separate** session namespace (`$_SESSION['admin_id']`/`$_SESSION['admin']`) — a logged-in user is not a logged-in admin and vice versa.
- `auth/` — login + OTP flow pages (`login.php`, `otp.php`, `verify_otp.php`, `logout.php`). OTP codes live in the `otp_codes` table, 5-minute expiry, single-use (`is_used` flag), invalidated on each new send.
- `halaman/` — user-facing pages (mobile app UI): dashboard, transfer, top-up, QR pay, mutasi (transaction history), merchant list, notifications, profile.
- `admin/` — admin dashboard pages, separate auth, Chart.js-based reporting.
- `proses/` — the actual backend logic, hit via POST from JS in `assets/js/`. Each file does DB work directly with `mysqli` prepared statements and returns JSON (`jsonResponse()` pattern in `kirim_otp.php`) or redirects, depending on the endpoint. There is no shared controller layer — each `proses/*.php` file is self-contained.
- `assets/js/` — one JS file roughly per feature (`transfer.js`, `qr.js`, `otp.js`, `merchant.js`, `admin.js`, `chart.js`), talking to `proses/*.php` via fetch/AJAX.
- `assets/uploads/` — user-submitted files (top-up proof images, profile photos); must stay writable by the web server.
- `database/jaxpay.sql` — full schema + demo/dummy data (demo user accounts are listed in `jaxpay/README.md`).

**Data access pattern:** raw `mysqli` throughout, prepared statements for user input (`$stmt->bind_param`), but some queries still interpolate server-derived integers directly (e.g. `$user['id']` in `kirim_otp.php`) — check each query rather than assuming a uniform pattern.

**Role model:** `role` on `users` table drives member-ID prefixing and UI (`student`, `teacher`, `parent`, `merchant`), independent from the admin/user session split above.

## Changelog (since `4e1ed62`)

- **803d9d4 — Docker/Dokploy deployment + secret fix**: added `Dockerfile`, `docker-compose.yml` (app/phpmyadmin/db on external `dokploy-network`), `.env.example`, `.gitignore`. Moved DB/SMTP config in `koneksi.php` to env vars, removed the hardcoded Gmail app password. Activated `.htaccess` (was missing its leading dot, previously inert as `htaccess_backup`).
- **2763d76 — Docker build/runtime fixes**: `Dockerfile` installs `unzip` (composer needs it for PHPMailer), `AllowOverride None→All` so `.htaccess` is read. `.htaccess` dropped an illegal `<Directory>` block and Apache-2.2-only `Order/Deny` syntax for `Require all denied`. `jaxpay.sql` dropped a phantom 4th merchant seed row with no matching user that broke the FK constraint on import.
- **f63ba5e — Admin headers-sent + login contrast**: `merchant.php`, `laporan.php`, `settings.php`, `topup.php`, `transaksi.php`, `users.php` under `admin/` were emitting HTML before `session_start()`/auth check, breaking the `admin_id` redirect guard everywhere but `dashboard.php` — moved PHP block to the top of each file. Added `data-theme="light"` to `admin/index.php`'s `<html>` tag to fix unreadable white-on-light login input text.
- **cec39f2 — Admin dark mode fix**: `theme.css` only defined `--admin-*` tokens in `:root` (comment literally said "Permanently Light") so the admin dark-mode toggle changed `data-theme` but nothing responded to it. Added a `[data-theme="dark"]` block with dark values for every `--admin-*` token `admin.css` uses. Bumped `theme.css?v=3→v=4` on all 23 pages that load it to bust the cache.
- **d92034f — User-facing headers-sent fix**: same bug as f63ba5e but across all `halaman/` pages (`detail_transaksi.php`, `home.php`, `merchant.php`, `mutasi.php`, `notifikasi.php`, `pembayaran.php`, `qr.php`, `scan.php`, `settings.php`, `topup.php`, `transfer.php`) — moved PHP logic to the top before any HTML. Also fixed a fatal syntax error in `detail_transaksi.php:112`: a malformed `<?= ?>` tag had literal `" style="color:` text bleeding into the PHP expression, causing a hard parse error (500) on that page.
