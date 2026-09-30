# Melamart Enterprises Admin Panel

Internal admin system for **Melamart Enterprises Limited** — a scaffolding and construction equipment hire and sale business operating in Ruiru and Kikuyu, Kenya.

---

## Features

- **Equipment management** — add, edit, remove products with photos, daily hire rates, stock in store vs. on hire
- **Brand & category management** — add/edit/remove with status control
- **Orders** — hire orders with automatic stock tracking (out on hire → back on return/cancel), server-side pricing, late-return charges, printable invoices
- **Payments** — M-Pesa, cash, bank transfer, cheque, card; reference codes; full payment history per order
- **Overdue tracking** — overdue list with accrued late charges and one-click SMS reminders (Africa's Talking)
- **User management** — administrator (user #1) vs. staff, bcrypt passwords
- **Reports** — filter by date, branch, client, status; view, print or export to Excel (CSV)
- **Dashboard** — on hire, overdue, monthly billing, payments received, outstanding balances, low stock

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.2 |
| Database | MySQL (MySQLi, prepared statements) |
| Frontend | Bootstrap 3, jQuery, DataTables, Font Awesome |
| Fonts | Montserrat + Open Sans (Google Fonts) |
| File uploads | Krajee Bootstrap FileInput plugin |
| Invoices | Printable HTML page (`php_action/printOrder.php`) — use the browser's "Save as PDF" for a PDF |
| SMS | Africa's Talking REST API via cURL (`php_action/sms.php`) |

---

## Requirements

- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled, or PHP built-in server for local development

---

## Setup

### 1. Clone the repo

```bash
git clone https://github.com/your-username/melamart-enterprises-admin.git
cd melamart-enterprises-admin
```

### 2. Create the database

Import the schema from `_sql/COMPLETE_SETUP.sql`:

```bash
mysql -u root -p < _sql/COMPLETE_SETUP.sql
```

See `_sql/QUICK_START.md` for detailed setup options.

### 3. Configure database credentials

```bash
cp php_action/db_connect.example.php php_action/db_connect.php
```

Edit `php_action/db_connect.php` with your local database credentials. This file is gitignored and will never be committed.

**Upgrading an existing database?** Run the migration once:

```bash
mysql -u root -p store < _sql/2026-10-01_audit_fixes.sql
```

### 3b. Business settings (optional)

Company details, branches and payment methods live in `php_action/config.php`.
Secrets and per-install values (KRA PIN, VAT rate, SMS API key) go in `php_action/config.local.php` (gitignored):

```bash
cp php_action/config.local.example.php php_action/config.local.php
```

- `MEL_VAT_RATE` — leave at `0` unless the company is VAT-registered (then `16`).
- `MEL_KRA_PIN` — printed on invoices when set.
- `MEL_SMS_*` — Africa's Talking username/API key; enables SMS reminders.
- `$MEL_INVOICE_TERMS` — the terms printed on invoices (review the defaults with management).

### 4. Run locally

```bash
php -S localhost:8000
```

Then open [http://localhost:8000](http://localhost:8000).

---

## Project Structure

```
melamart-enterprises-admin/
├── _sql/                    # Database schemas and setup instructions
├── assets/                  # Third-party plugins (Bootstrap, jQuery, DataTables, etc.)
│   └── images/stock/        # Uploaded product images (gitignored)
├── custom/
│   ├── css/
│   │   ├── melamart-theme.css   # Brand design system (Navy + Yellow)
│   │   └── custom.css           # Utility overrides
│   └── js/                  # app.js (shared: toasts, CSRF, errors, date pickers) + one file per page
├── docs/                    # Developer documentation (web-blocked via .htaccess)
├── images/                  # System images (logo, favicon)
├── includes/
│   ├── header.php           # Global HTML head, navbar, CSRF token, jQuery setup
│   └── footer.php           # Global scripts
├── php_action/              # All AJAX handlers and backend utilities
│   ├── core.php             # Session guard, DB + CSRF bootstrap (loaded by every page)
│   ├── config.php           # Business settings (branches, payment methods, VAT, SMS)
│   ├── helpers.php          # Shared helpers: dates (dd/mm/yyyy), phones (+254), money, stock
│   ├── order_service.php    # The one place orders are validated, priced and saved
│   ├── csrf.php             # CSRF token generation and verification
│   ├── db_connect.php       # Database credentials — gitignored, see db_connect.example.php
│   └── *.php                # Feature handlers (create/edit/remove/fetch per entity)
├── storage/
│   ├── logs/                # SMS cron log (gitignored)
│   └── receipts/            # (unused, gitignored)
├── brand.php
├── categories.php
├── cron_sms_reminder.php    # Run via server cron — see docs/CRON_SETUP.md
├── dashboard.php
├── index.php                # Login page
├── logout.php
├── orders.php
├── overdue_reminders.php
├── product.php
├── report.php
├── setting.php
└── user.php
```

---

## Security

| Area | Implementation |
|---|---|
| Passwords | `password_hash()` with `PASSWORD_BCRYPT`. Legacy MD5 accounts auto-upgrade on next login. |
| SQL injection | All queries use MySQLi prepared statements with `bind_param()`. |
| CSRF | Per-session token (`random_bytes(32)`). Verified server-side via `csrf_verify()`. All jQuery AJAX POSTs covered globally via `$.ajaxSetup`. |
| Session fixation | `session_regenerate_id(true)` on every successful login. |
| XSS | All output through `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`. |
| Sensitive files | `docs/` is blocked from web access via `.htaccess`. `db_connect.php` is gitignored. |
| Auth guard | Every page boots through `php_action/core.php` which checks `isset($_SESSION['userId'])`. Expired sessions: pages redirect to login, AJAX gets a 401 and the UI redirects. |
| Admin-only | `require_admin()` on admin pages *and* their handlers (not just hidden menu links). |

---

## Default Login

After importing the SQL schema, log in with the credentials defined in the `users` table. See `_sql/QUICK_START.md` for details.

> **Never use the default password in production.** Change it immediately after first login via My Account → Password.

---

## Cron Job

A daily overdue digest SMS to the administrator runs via cron (CLI only). See `docs/CRON_SETUP.md` for the exact crontab entry and configuration.

---

## License

See `docs/LICENSE`.
