# Quick Start Guide - Database Setup

## You're Getting This Error Because...

The error `Table 'store.orders' doesn't exist` means the base database tables haven't been created yet.

## Quick Fix - Choose One Option:

### ✅ Option A: Complete Fresh Setup (Easiest)

1. Open phpMyAdmin or your MySQL client
2. Select or create the `store` database
3. Import: `database/COMPLETE_SETUP.sql`
4. Done! All tables and updates are included

### ✅ Option B: Two-Step Setup (If You Have Existing Data)

**Step 1:** Import `database/store.sql` (creates base tables)
**Step 2:** Import `database/store_updated_simple.sql` (applies updates)

**Note:** If you get "Duplicate column" or "Duplicate key" errors in Step 2, that's OK - it means those updates were already applied.

## Verify Setup

After importing, run this SQL to verify:

```sql
-- Check orders table structure
DESCRIBE orders;

-- Should show returned_date as NULL-able
-- Should show last_late_fee_calc, reminder_sent, last_reminder_date columns

-- Check product table
DESCRIBE product;

-- Should show daily_rate column
```

## Default Login

After setup, login with:
- Username: `admin`
- Password: `admin`

**⚠️ Change the password immediately after first login!**

## Need Help?

See `database/SETUP_INSTRUCTIONS.md` for detailed troubleshooting.
