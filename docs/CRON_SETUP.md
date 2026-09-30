# Cron setup — daily overdue SMS digest

`cron_sms_reminder.php` sends **one SMS to the administrator** each day listing
orders whose equipment is past its expected return date. It only runs from the
command line (browsers get a 403).

SMS reminders **to clients** are sent by staff from the **Overdue** page
("SMS client" button, with a confirmation step) — never automatically.

Late-return charges are no longer added by a cron job. They are calculated when
the return is recorded on the order (extra days × daily rate × quantity). The
Overdue page shows the estimated charge so far.

## 1. Configure SMS

```bash
cp php_action/config.local.example.php php_action/config.local.php
```

Set, from your Africa's Talking account:

```php
define('MEL_SMS_USERNAME', 'your_at_username');   // 'sandbox' for testing
define('MEL_SMS_API_KEY', 'atsk_...');
define('MEL_SMS_SENDER_ID', '');                  // approved sender ID, or '' for the default
define('MEL_SMS_ADMIN_PHONE', '+2547XXXXXXXX');   // receives the daily digest
```

PHP needs the cURL extension.

## 2. Test by hand

```bash
php /path/to/apps/admin/cron_sms_reminder.php
```

Output is also appended to `storage/logs/sms_cron.log`. Every SMS sent is logged
in the `sms_logs` table.

## 3. Schedule it (08:00 every day, server time)

```cron
0 8 * * * /usr/bin/php /path/to/apps/admin/cron_sms_reminder.php >/dev/null 2>&1
```

The script sets the Kenyan timezone (Africa/Nairobi) itself.
