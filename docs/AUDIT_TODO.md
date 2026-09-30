# Admin Audit — Fix List (2026-10-01)

Status: `[ ]` todo · `[x]` done

## Tier 1 — Blockers
- [x] T1.1 Date pickers: remove `.ui-datepicker-calendar {display:none}` hack; global dd/mm/yyyy, Monday-first defaults
- [x] T1.2 Edit Order: stop writing `'0000-00-00'` (NULL instead); Returned Date always editable
- [x] T1.3 Define global `showToast()` + global AJAX error handling (no more stuck "Loading…" buttons)
- [x] T1.4 Action dropdowns clipped by `.panel{overflow:hidden}` / `.table-responsive`
- [x] T1.5 Products with 0 stock disappear from Product page
- [x] T1.6 Edit Product: daily rate not loaded; image upload button; plugin re-init
- [x] T1.7 Reports: dd/mm/yyyy parsing, "Pending" filter ignored, print window closes early
- [x] T1.8 Export CSV endpoint missing
- [x] T1.9 Session-expiry redirect goes to site root; AJAX gets HTML instead of 401 JSON
- [x] T1.10 Users: email validation, optional password on edit, error display, wrong button ids
- [x] T1.11 Categories: wrong validation text, error shown as success

## Tier 2 — Order / money / stock integrity
- [x] T2.1 Server-side recalculation of all totals (never trust browser totals)
- [x] T2.2 Stock: check availability, deduct on hire, return stock on return/cancel, adjust on edit (transactional)
- [x] T2.3 Edit Order saves line items (add/remove/change rows); shows real qty & rental days
- [x] T2.4 Late-return charge = extra days × daily rate × qty (single, transparent rule, stored as `late_fee`)
- [x] T2.5 Payment status derived automatically from paid vs grand total
- [x] T2.6 Payments recorded server-side from DB values + payment history
- [x] T2.7 Remove broken/double-charging `updateLateFees.php`, dead handlers
- [x] T2.8 Dashboard stats labelled and computed correctly
- [x] T2.9 Timezone Africa/Nairobi (PHP + MySQL session)

## Tier 3 — Kenya localisation
- [x] T3.1 Invoice rewritten: Melamart details, branches, KRA PIN (config), VAT only when configured, "Kenya Shillings" words
- [x] T3.2 "Payment Place: Gujarat" → Branch (Ruiru / Kikuyu)
- [x] T3.3 Payment methods: M-Pesa, Bank Transfer, Cash, Cheque, Card + reference/M-Pesa code
- [x] T3.4 Remove ₹, GST, lakh/crore, Surat references
- [x] T3.5 Kenyan phone validation/normalisation (+254)
- [x] T3.6 Client KRA PIN (optional) replaces hidden GSTN field

## Tier 4 — Security
- [x] T4.1 Prepared statements for all remaining raw SQL
- [x] T4.2 `csrf_verify()` on every state-changing handler
- [x] T4.3 Server-side admin checks on admin pages + handlers; users edit only their own settings
- [x] T4.4 Escape output (XSS) in DataTables / JS-rendered tables / forms
- [x] T4.5 No password hash / SQL / debug info returned to the browser
- [x] T4.6 Hardened session cookie (HttpOnly, SameSite)

## Tier 5 — UX & incomplete features
- [x] T5.1 New-order form: 1 starting row, auto rental days from dates, availability limits, clear validation
- [x] T5.2 Manage Orders: fewer columns, status filter, overdue badge, order no. = invoice no.
- [x] T5.3 Delete confirmations are red "Delete/Cancel" buttons with clear wording
- [x] T5.4 Printing opens a proper page (no auto-close race)
- [x] T5.5 SMS reminders (Africa's Talking via cURL, config-driven, CLI cron + "Send SMS" on Overdue page)
- [x] T5.6 Overdue page: accrued late fee, tap-to-call, SMS button
- [x] T5.7 Settings available to all users (own username/password)
- [x] T5.8 Cleanup: debug logs, unused dashboard libs, leftover template files, label typos
- [x] T5.9 DB migration script + updated COMPLETE_SETUP.sql

## Verification
- Scripted API tests: create/edit/cancel orders, stock in/out, late fee, payments, over-payment/over-stock rejection, CSRF, 401/403.
- Browser tests (Chrome, desktop + phone width): every page, all CRUD flows, date pickers, dropdowns, printing, CSV export, staff vs admin access, session expiry. No JS errors.

## Needs a decision from management
- [ ] Invoice terms & conditions (`$MEL_INVOICE_TERMS` in config.php) — defaults are placeholders.
- [ ] VAT: set `MEL_VAT_RATE` to 16 only if the company is VAT-registered; set `MEL_KRA_PIN`.
- [ ] Africa's Talking account + API key for SMS (`config.local.php`), then schedule the cron.
- [ ] Early returns are still charged for the agreed rental days (no refund) — confirm this is the policy.
- [ ] Orders created before this update never returned stock on completion — do a one-off stock count.
- [ ] Change the default `admin`/`admin` password on every install.
