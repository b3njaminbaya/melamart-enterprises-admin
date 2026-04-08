# Cron Job Setup for Late Fee Calculation

This system requires a daily cron job to automatically calculate and update late fees for overdue rental items.

## Setup Instructions

### 1. Create the Cron Job

Add the following line to your crontab (run `crontab -e`):

```bash
# Run daily at 2:00 AM to update late fees
0 2 * * * /usr/bin/php /path/to/your/project/php_action/updateLateFees.php >> /path/to/your/project/logs/late_fees.log 2>&1
```

### 2. Alternative: Using wget/curl (if PHP CLI is not available)

```bash
# Run daily at 2:00 AM
0 2 * * * /usr/bin/wget -q -O - http://yourdomain.com/php_action/updateLateFees.php >> /path/to/logs/late_fees.log 2>&1
```

Or with curl:

```bash
0 2 * * * /usr/bin/curl -s http://yourdomain.com/php_action/updateLateFees.php >> /path/to/logs/late_fees.log 2>&1
```

### 3. Windows Task Scheduler (for Windows servers)

1. Open Task Scheduler
2. Create Basic Task
3. Set trigger to "Daily" at 2:00 AM
4. Set action to "Start a program"
5. Program: `C:\path\to\php.exe`
6. Arguments: `C:\path\to\your\project\php_action\updateLateFees.php`
7. Save the task

### 4. Verify the Cron Job

To test if the cron job is working:

```bash
# Manually run the script
php /path/to/your/project/php_action/updateLateFees.php

# Check the log file
tail -f /path/to/logs/late_fees.log
```

## What the Script Does

1. Finds all orders where:
   - Expected return date has passed
   - Items have not been returned (returned_date is NULL or '0000-00-00')
   - Order status is not cancelled

2. Calculates additional charges:
   - For each overdue day since last calculation
   - Based on: daily_rate × quantity × days_overdue
   - Updates the order's grand_total and due amount

3. Updates the database:
   - Increments grand_total with late fees
   - Increments due amount with late fees
   - Records the last calculation timestamp

## Important Notes

- The script is idempotent - it can be run multiple times safely
- It only calculates fees for days that have passed since the last calculation
- Make sure the web server user has write permissions to the logs directory
- Monitor the log file regularly to ensure the cron job is running

## Troubleshooting

### Cron job not running?
- Check cron service is running: `service cron status`
- Check cron logs: `grep CRON /var/log/syslog`
- Verify file permissions: `chmod +x php_action/updateLateFees.php`

### Script errors?
- Check PHP error logs
- Verify database connection in `php_action/db_connect.php`
- Ensure all required database fields exist (run `database/store_updated.sql`)

### Late fees not updating?
- Verify the cron job is actually running
- Check if orders have valid expect_return_date
- Ensure daily_rate is set for all products
