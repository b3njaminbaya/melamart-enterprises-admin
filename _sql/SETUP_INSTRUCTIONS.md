# Database Setup Instructions

## Option 1: Fresh Database Setup (Recommended for New Installations)

If you're setting up a new database or want to start fresh:

1. **Create the database** (if it doesn't exist):
   ```sql
   CREATE DATABASE IF NOT EXISTS `store` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE `store`;
   ```

2. **Run the complete setup file**:
   - Import `database/COMPLETE_SETUP.sql`
   - This file contains all tables with all updates already applied

## Option 2: Update Existing Database

If you already have the database set up from the original `store.sql`:

1. **First, import the base schema** (if not already done):
   - Import `database/store.sql`

2. **Then, run the update script**:
   - Import `database/store_updated.sql`
   - This script checks if tables exist before modifying them

## Troubleshooting

### Error: "Table 'store.orders' doesn't exist"

**Solution**: You need to run the base schema first.

1. Import `database/store.sql` to create all base tables
2. Then import `database/store_updated.sql` to apply updates

OR

1. Import `database/COMPLETE_SETUP.sql` which includes everything

### Error: "Duplicate column name" or "Duplicate key name"

**Solution**: The column/index already exists. This is safe to ignore. The update script checks for existence before adding.

### Error: "Access denied"

**Solution**: Make sure you have proper database permissions:
- CREATE
- ALTER
- INDEX
- INSERT
- SELECT

## Verification

After setup, verify the database structure:

```sql
-- Check if orders table has the new columns
DESCRIBE orders;

-- Should show:
-- returned_date (date, NULL)
-- last_late_fee_calc (datetime, NULL)
-- reminder_sent (tinyint(1), default 0)
-- last_reminder_date (datetime, NULL)

-- Check if product table has daily_rate
DESCRIBE product;

-- Should show:
-- daily_rate (decimal(10,2), default 0.00)

-- Check indexes
SHOW INDEXES FROM orders;

-- Should show:
-- idx_expect_return_date
-- idx_returned_date
-- idx_order_status
```

## Default Login Credentials

After setup, you can login with:
- **Username**: `admin`
- **Password**: `admin`

**Important**: Change the default password after first login!
