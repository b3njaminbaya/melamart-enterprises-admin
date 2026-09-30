# Construction Equipment Rental System - Update Summary

> **Superseded (2026-10-01):** `updateLateFees.php`, `checkOverdue.php` and `sms_reminder.php` described below were removed — they double-charged late fees and could not run. Late charges are now applied when a return is recorded, and SMS works as described in `CRON_SETUP.md`. See `AUDIT_TODO.md` for the full list of changes.

## Overview
This system has been updated from a general inventory management system to a specialized construction equipment rental management system.

## Key Changes

### 1. Database Updates
- **File**: `database/store_updated.sql`
- Fixed `returned_date` to be nullable
- Added indexes for better performance
- Added fields for late fee tracking (`last_late_fee_calc`, `reminder_sent`, `last_reminder_date`)

**To apply**: Run the SQL commands in `database/store_updated.sql` on your database.

### 2. Order System Changes
- **Removed VAT**: VAT is now set to 0.00 and not included in calculations
- **GSTN Made Optional**: GSTN field is hidden but kept for backward compatibility
- **Rental Pricing**: Orders now calculate based on:
  - Daily Rate × Rental Days × Quantity
  - Auto-populates when product is selected
  - Multi-product support with automatic subtotal calculation

### 3. Orders Management Table
- **Enhanced Display**: Now shows all required fields:
  - Order Date, Expected Return Date, Returned Date
  - Site Location, Client Name, Client Contact
  - Driver Name, Driver Contact
  - Returned By, Returned By Contact, Approved By
  - Grand Total, Paid, Due
  - Payment Type, Payment Status, Order Status

### 4. Late Return Tracking
- **Automatic Late Fee Calculation**: 
  - File: `php_action/updateLateFees.php`
  - Calculates daily charges for overdue items
  - Increments grand_total and due amounts automatically
  - Should be run daily via cron job (see CRON_SETUP.md)

### 5. Admin Reminder System
- **Overdue Reminders Page**: `overdue_reminders.php`
- Shows all delayed items with details
- Displays overdue days, client info, and items
- Badge in navigation shows count of overdue orders
- Auto-refreshes every 5 minutes

### 6. Enhanced Reports
- **Date Range Filtering**: Select start and end dates
- **Search Functionality**: Search by client name
- **Advanced Filters**: 
  - Payment Status (Full/Advance/No Payment)
  - Order Status (Pending/Completed/Cancelled)
  - Payment Type (Cheque/Cash/Credit Card)
- **Data Visualization**: Summary cards showing totals
- **Export Options**: Print and CSV export

### 7. Modernized UI/UX
- **Responsive Design**: Works on mobile, tablet, and desktop
- **90% Width**: Tables and menus use 90% width as requested
- **Modern Styling**: 
  - Gradient buttons
  - Smooth transitions
  - Modern color scheme
  - Improved typography
- **Better UX**: 
  - Clear visual feedback
  - Improved form layouts
  - Better spacing and padding

### 8. Product Management
- **Daily Rate Field**: Added to product creation and editing
- **Clear Labels**: "Purchase Rate" vs "Daily Rate (Rental)"
- **Required Field**: Daily rate is now required for rental calculations

## Files Modified

### PHP Files
- `orders.php` - Order management interface
- `product.php` - Product management (added daily_rate field)
- `report.php` - Enhanced reports page
- `overdue_reminders.php` - New overdue items page
- `includes/header.php` - Added overdue badge and report link

### JavaScript Files
- `custom/js/order.js` - Removed VAT/GSTN validation, fixed calculations
- `custom/js/report.js` - Enhanced with filtering and visualization
- `custom/js/overdueBadge.js` - New file for overdue count badge

### PHP Action Files
- `php_action/createOrder.php` - Updated to use daily_rate
- `php_action/editOrder.php` - Fixed returned_date handling
- `php_action/fetchOrder.php` - Enhanced to show all fields
- `php_action/getOrderReportData.php` - New file for AJAX report data
- `php_action/updateLateFees.php` - New file for late fee calculation
- `php_action/getOverdueOrders.php` - New file for overdue items

### CSS Files
- `custom/css/custom.css` - Modern responsive styling, 90% width

### Database Files
- `database/store_updated.sql` - Database schema updates

## Setup Instructions

1. **Run Database Updates**:
   ```sql
   -- Execute database/store_updated.sql
   ```

2. **Setup Cron Job** (see CRON_SETUP.md):
   - Daily execution of `php_action/updateLateFees.php`
   - Recommended time: 2:00 AM

3. **Verify Product Daily Rates**:
   - Ensure all products have `daily_rate` set
   - Update existing products if needed

4. **Test the System**:
   - Create a test order
   - Verify calculations (daily_rate × days × quantity)
   - Test overdue reminders
   - Test report filtering

## Important Notes

- **VAT is Disabled**: All VAT calculations are set to 0.00
- **GSTN is Optional**: Field exists but is not required
- **Late Fees**: Automatically calculated daily for overdue items
- **Responsive**: System works on all device sizes
- **90% Width**: Tables and menus use 90% width as requested

## Support

For issues or questions:
1. Check CRON_SETUP.md for cron job setup
2. Verify database schema matches store_updated.sql
3. Check browser console for JavaScript errors
4. Review PHP error logs
