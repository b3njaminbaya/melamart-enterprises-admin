# Melamart Admin — Deployment Checklist

Two supported ways to run the admin:

| | **A. Hosted on a server** (recommended) | **B. Offline desktop** |
|---|---|---|
| Branches sharing one stock count | Yes — Ruiru and Kikuyu see the same data | No — one PC only |
| Use from phones / other locations | Yes | Only on the office network (optional) |
| SMS reminders | Yes | Only when the PC has internet |
| Backups | Automatic (host) + weekly download | Manual script to an external drive |
| Running cost | ~KSh 3,000–10,000 / year hosting | Electricity + a reliable PC and UPS |

Tick each box as you go. Commands assume the repo's `apps/admin` folder is the app root.

---

## Before either option

- [ ] You have the latest code (`git pull` in `apps/admin`).
- [ ] You know which database you are deploying to:
  - **Fresh install** (no existing data) → you will import `_sql/COMPLETE_SETUP.sql`.
  - **Existing data** (an older version was already running) → you will run
    `_sql/2026-10-01_audit_fixes.sql`. It is safe to run more than once.
- [ ] Management has reviewed the invoice terms (`$MEL_INVOICE_TERMS` in `php_action/config.php`)
      and decided on VAT (`MEL_VAT_RATE` 0 or 16) and the KRA PIN.

---

## A. Hosted on a server (cPanel shared hosting)

Most Kenyan hosts (and the host of the public website) provide cPanel with PHP, MySQL,
phpMyAdmin, cron jobs and free SSL. Requirements: **PHP 8.1+** with `mysqli` and `curl`,
**MySQL 5.7+ or MariaDB 10.3+**, Apache (for the `.htaccess` protections).

### A1. If an older version is already live — back it up first
- [ ] cPanel → **phpMyAdmin** → select the database → **Export** → Quick → SQL → Go. Keep the file.
- [ ] cPanel → **File Manager** → select the admin folder → **Compress** (zip) → download it.
- [ ] Also download `assets/images/stock/` (product photos) if not inside the zip.

### A2. Subdomain
- [ ] cPanel → **Domains** (or Subdomains) → create `admin.melamartscaffolding.com`.
      Note its document root, e.g. `/home/CPANELUSER/admin.melamartscaffolding.com`.

### A3. Database and a restricted app user
cPanel prefixes names with your account name (e.g. `melamart_store`).

- [ ] cPanel → **MySQL Databases** → *Create New Database*: `store` → becomes `CPANELUSER_store`.
- [ ] *Add New User*: `app` → becomes `CPANELUSER_app`. Use the password generator (letters and
      numbers, 24+ characters). Save the password in the company password manager.
- [ ] *Add User To Database* → user `CPANELUSER_app`, database `CPANELUSER_store` → tick **only**:
      `SELECT`, `INSERT`, `UPDATE`, `DELETE` → Make Changes.
      *(The app never needs ALTER/DROP. Migrations are run through phpMyAdmin, which uses your
      cPanel account.)*

### A4. Load the database
- [ ] cPanel → **phpMyAdmin** → click `CPANELUSER_store` in the left panel.
- [ ] **Fresh install:** Import → `_sql/COMPLETE_SETUP.sql` → Go.
- [ ] **Existing data:** Import the backup from A1 (if moving databases), then Import
      `_sql/2026-10-01_audit_fixes.sql` → Go. You should see *“Melamart audit migration complete”*.
      Run the migration **before** uploading the new code; the old code keeps working with the new columns.

### A5. Upload the code
- [ ] Zip the contents of `apps/admin` on your computer (do **not** include `php_action/db_connect.php`
      or `php_action/config.local.php` from your laptop).
- [ ] File Manager → subdomain folder → **Upload** the zip → **Extract**.
      `index.php` must sit directly in the subdomain folder.
- [ ] Check these `.htaccess` files are present (they block private files):
      `docs/.htaccess`, `_sql/.htaccess`, `storage/.htaccess`, `php_action/.htaccess`.

### A6. Server configuration files
- [ ] In File Manager, copy `php_action/db_connect.example.php` → `php_action/db_connect.php` and edit:
      ```php
      $localhost = "localhost";
      $username  = "CPANELUSER_app";
      $password  = "the password from A3";
      $dbname    = "CPANELUSER_store";
      ```
- [ ] Copy `php_action/config.local.example.php` → `php_action/config.local.php` and set
      `MEL_KRA_PIN`, `MEL_VAT_RATE` (only if VAT-registered) and, once you have them, the `MEL_SMS_*` values.

### A7. Folder permissions
- [ ] Folders `755`, files `644` (cPanel default).
- [ ] `assets/images/stock/` and `storage/logs/` must be writable by PHP (`755` is normally enough on cPanel;
      use `775` only if photo uploads fail). Never use `777`.

### A8. PHP settings
- [ ] cPanel → **MultiPHP Manager** → set the subdomain to **PHP 8.2** (8.1 minimum).
- [ ] cPanel → **MultiPHP INI Editor** → for the subdomain:
      `display_errors = Off`, `log_errors = On`, `upload_max_filesize = 4M`, `post_max_size = 8M`.
      (The app sets the Kenyan timezone itself.)

### A9. HTTPS
- [ ] cPanel → **SSL/TLS Status** → Run AutoSSL (or Let's Encrypt) for `admin.melamartscaffolding.com`.
- [ ] Force HTTPS: File Manager → create/edit `.htaccess` in the subdomain root and add **at the top**:
      ```apache
      RewriteEngine On
      RewriteCond %{HTTPS} !=on
      RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
      ```
      *(Server only — do not add this to an offline/desktop install.)*

### A10. First login and accounts
- [ ] Open `https://admin.melamartscaffolding.com` → log in.
- [ ] **Fresh install:** the default login is `admin` / `admin` → immediately go to **My Account → Password**
      and set a strong password.
- [ ] **Manage Users** → create one account per staff member (no shared logins).
- [ ] **Brands**, **Categories**, **Equipment** → enter the real equipment, daily hire rates and quantities in the yard.
      If migrating old data, do a physical stock count and correct quantities (older versions never returned stock).

### A11. SMS and the daily overdue digest (when the Africa's Talking account is ready)
- [ ] Fill `MEL_SMS_USERNAME`, `MEL_SMS_API_KEY`, `MEL_SMS_SENDER_ID` (or blank), `MEL_SMS_ADMIN_PHONE`
      in `php_action/config.local.php`.
- [ ] cPanel → **Cron Jobs** → Common setting *Once per day* → set hour `8`, minute `0` → command:
      ```
      /usr/local/bin/php /home/CPANELUSER/admin.melamartscaffolding.com/cron_sms_reminder.php >/dev/null 2>&1
      ```
      (Check the PHP path under cPanel → Terminal with `which php` if unsure.)
- [ ] Test once from **Terminal** (if available) with the same command without `>/dev/null 2>&1`.
      See `docs/CRON_SETUP.md` for details.

### A12. Backups
- [ ] cPanel → **Backup** (or JetBackup) → confirm the host takes daily backups and how long they are kept.
- [ ] Every Monday: phpMyAdmin → Export the database and save it off the server
      (company Google Drive / external disk). Keep at least 4 weeks.
- [ ] Once a month: download `assets/images/stock/`.

### A13. Smoke test (10 minutes, before telling staff)
- [ ] Log in / log out; wrong password shows an error.
- [ ] New Order: date pickers open; dates show as dd/mm/yyyy; totals calculate; order saves.
- [ ] The equipment quantity in store dropped by the hired amount (Equipment page, *On hire* column).
- [ ] Record payment (M-Pesa with a code) → Payment history shows it.
- [ ] Print invoice → Melamart details, branch phone, “Kenya Shillings” words.
- [ ] Edit the order → set Returned date → stock goes back; order shows *Completed*.
- [ ] Reports → View, Print, Export to Excel.
- [ ] Log in as a staff user → Equipment/Users/Reports are not in the menu and are blocked if typed in the address bar.
- [ ] Visit `https://admin.melamartscaffolding.com/_sql/COMPLETE_SETUP.sql` → must be **403 Forbidden**.
- [ ] Delete the test order's data or cancel it.

### A14. Updating later
1. Back up (A1).
2. Import any new file in `_sql/` via phpMyAdmin (migrations are written to be safe to re-run).
3. Upload the changed files (never overwrite `db_connect.php`, `config.local.php`, `assets/images/stock/`).
4. Repeat the smoke test.

**Rollback:** re-upload the zip from A1 and import the SQL export from A1.

---

## B. Offline desktop (single Windows PC)

Use this only if one office, on one computer, uses the system and internet is unreliable.
Other branches cannot see this data. SMS works only while the PC is online.

### B1. The PC
- [ ] Windows 10/11, 8 GB RAM, SSD, **on a UPS** (power cuts can corrupt the database).
- [ ] A dedicated Windows user with a password; screen locks after 5 minutes.
- [ ] Windows Update and antivirus on.

### B2. Install XAMPP
- [ ] Download **XAMPP for Windows with PHP 8.2** from apachefriends.org and install to `C:\xampp`
      (components: Apache, MySQL, PHP, phpMyAdmin).
- [ ] Open **XAMPP Control Panel as Administrator** → tick the **Service** box for Apache and MySQL
      (they then start automatically with Windows) → Start both.

### B3. Secure MySQL
XAMPP's MySQL (MariaDB) `root` has **no password** by default.
- [ ] XAMPP Control Panel → **Shell**, then:
      ```
      mysqladmin -u root password
      ```
      Enter a strong root password. Store it safely (not on the PC desktop).
- [ ] Edit `C:\xampp\phpMyAdmin\config.inc.php` → set `$cfg['Servers'][$i]['auth_type'] = 'cookie';`
      so phpMyAdmin asks for the password.

### B4. Copy the app
- [ ] Copy the contents of `apps/admin` to `C:\xampp\htdocs\melamart-admin\`
      (`index.php` directly inside that folder).

### B5. Database and restricted app user
- [ ] XAMPP **Shell**:
      ```
      mysql -u root -p
      ```
      then (choose your own long password for the app user):
      ```sql
      CREATE DATABASE store CHARACTER SET utf8mb4;
      CREATE USER 'melamart_app'@'localhost' IDENTIFIED BY 'LONG_RANDOM_PASSWORD';
      GRANT SELECT, INSERT, UPDATE, DELETE ON store.* TO 'melamart_app'@'localhost';
      EXIT;
      ```
- [ ] **Fresh install:**
      ```
      mysql -u root -p store < C:\xampp\htdocs\melamart-admin\_sql\COMPLETE_SETUP.sql
      ```
- [ ] **Existing data:** import the old SQL export first, then:
      ```
      mysql -u root -p store < C:\xampp\htdocs\melamart-admin\_sql\2026-10-01_audit_fixes.sql
      ```

### B6. Configuration files
- [ ] Copy `php_action\db_connect.example.php` → `php_action\db_connect.php` and set
      `$username = "melamart_app";` and the password from B5.
- [ ] Copy `php_action\config.local.example.php` → `php_action\config.local.php` and set KRA PIN / VAT / SMS as needed.
- [ ] **Do not** add the HTTPS redirect from A9 on this PC.

### B7. PHP settings
- [ ] XAMPP Control Panel → Apache **Config** → `php.ini`: check `extension=mysqli` and `extension=curl` are not
      commented out; set `display_errors=Off`, `upload_max_filesize=4M`. Restart Apache.

### B8. Keep it local only
- [ ] Windows Firewall: do **not** allow inbound port 80/3306 from other networks.
- [ ] Optional — other PCs on the same office LAN: allow Apache on *Private* networks only, give this PC a fixed
      IP on the router, and open `http://THAT-IP/melamart-admin` on the other PCs. Never forward ports on the router.

### B9. Shortcut and first login
- [ ] Create a desktop shortcut to `http://localhost/melamart-admin`.
- [ ] Log in (`admin` / `admin` on a fresh install) → **My Account → Password** → set a strong password.
- [ ] Create one user per staff member; enter equipment, rates and quantities.

### B10. Daily backup (Task Scheduler)
- [ ] Plug in an external drive (e.g. `E:`) or use a Google Drive for desktop folder.
- [ ] Create `C:\xampp\melamart-backup.bat`:
      ```bat
      @echo off
      set BACKUP_DIR=E:\MelamartBackups
      if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"
      for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd"') do set TODAY=%%i
      "C:\xampp\mysql\bin\mysqldump.exe" -u root -pYOUR_ROOT_PASSWORD --single-transaction store > "%BACKUP_DIR%\store-%TODAY%.sql"
      xcopy "C:\xampp\htdocs\melamart-admin\assets\images\stock" "%BACKUP_DIR%\stock" /E /I /Y /Q
      forfiles /p "%BACKUP_DIR%" /m store-*.sql /d -30 /c "cmd /c del @path" 2>nul
      ```
- [ ] Right-click the file → Properties → Security: only the Windows admin user can read it (it contains the root password).
- [ ] **Task Scheduler** → Create Basic Task → *Daily* 18:00 → *Start a program* → `C:\xampp\melamart-backup.bat`
      → tick *Run whether user is logged on or not*.
- [ ] Next day, check a new `store-YYYY-MM-DD.sql` exists. Test a restore once a quarter on another PC.

### B11. SMS digest (optional, needs internet)
- [ ] Fill the `MEL_SMS_*` values in `config.local.php`.
- [ ] Task Scheduler → Daily 08:00 → program `C:\xampp\php\php.exe`,
      arguments `C:\xampp\htdocs\melamart-admin\cron_sms_reminder.php`.

### B12. Smoke test
- [ ] Run the checks in **A13** (skip the HTTPS and `/_sql/` checks; instead confirm
      `http://localhost/melamart-admin/_sql/COMPLETE_SETUP.sql` shows 403).
- [ ] Restart the PC → confirm Apache and MySQL start by themselves and the app opens.

### B13. Updating later
1. Run the backup `.bat` by hand.
2. Copy the new files over `C:\xampp\htdocs\melamart-admin\` (keep `db_connect.php`, `config.local.php`, `assets\images\stock\`).
3. Run any new `_sql/` migration with `mysql -u root -p store < ...`.
4. Smoke test.

### Moving from desktop to hosted later
Export the desktop database (`mysqldump` or phpMyAdmin → Export), follow **A1–A13**, importing that export in A4
(then the migration), and upload `assets\images\stock\` to the server.
