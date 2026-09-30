<?php
/*
 * config.php — Melamart business settings.
 *
 * Put machine-specific values and secrets (SMS API key, KRA PIN, VAT)
 * in config.local.php (gitignored) using the same constant names —
 * anything defined there wins over the defaults below.
 */

if(file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

function mel_define($name, $value) {
    if(!defined($name)) {
        define($name, $value);
    }
}

// ─── Company ───────────────────────────────────────────────
mel_define('MEL_COMPANY_NAME', 'Melamart Enterprises Limited');
mel_define('MEL_COMPANY_EMAIL', 'info@melamartscaffolding.com');
mel_define('MEL_KRA_PIN', '');            // e.g. 'P051234567X' — printed on invoices when set

// ─── Tax ───────────────────────────────────────────────────
// Kenyan standard VAT is 16%. Leave at 0 unless the company is VAT-registered.
mel_define('MEL_VAT_RATE', 0);

// ─── SMS (Africa's Talking) ────────────────────────────────
mel_define('MEL_SMS_USERNAME', '');       // AT username ('sandbox' for testing)
mel_define('MEL_SMS_API_KEY', '');
mel_define('MEL_SMS_SENDER_ID', '');      // approved alphanumeric sender ID, or '' for default
mel_define('MEL_SMS_ADMIN_PHONE', '');    // receives the daily overdue digest, e.g. '+254758502216'

// ─── Public demo ───────────────────────────────────────────
// Username of a shared demo login whose username/password cannot be changed
// from My Account (so visitors cannot lock each other out). '' = off.
mel_define('MEL_LOCKED_DEMO_USER', '');

// ─── Invoice terms (printed at the bottom of every invoice) ─
// Review these with management; override in config.local.php via $MEL_INVOICE_TERMS.
if(!isset($MEL_INVOICE_TERMS)) {
    $MEL_INVOICE_TERMS = array(
        'Hired equipment remains the property of ' . MEL_COMPANY_NAME . '.',
        'Equipment returned after the expected return date is charged at the daily hire rate for each extra day.',
        'The hirer is responsible for loss of, or damage to, equipment while on hire.',
        'E. & O.E.',
    );
}

// ─── Branches (id => details) — stored in orders.payment_place ─
$MEL_BRANCHES = array(
    1 => array('name' => 'Ruiru',  'address' => 'Off Eastern Bypass, Ruiru',                     'phone' => '+254 758 502 216'),
    2 => array('name' => 'Kikuyu', 'address' => 'Thogoto - Mutarakwa Road, Opp. Gikambura Primary School, Kikuyu', 'phone' => '+254 758 445 822'),
);

// ─── Lookups (ids are what is stored in the DB) ────────────
$MEL_PAYMENT_TYPES = array(
    4 => 'M-Pesa',
    2 => 'Cash',
    5 => 'Bank Transfer',
    1 => 'Cheque',
    3 => 'Card',
);

$MEL_PAYMENT_STATUSES = array(
    1 => 'Fully Paid',
    2 => 'Partially Paid',
    3 => 'Not Paid',
);

$MEL_ORDER_STATUSES = array(
    0 => 'On Hire',
    1 => 'Completed',
    2 => 'Cancelled',
);

date_default_timezone_set('Africa/Nairobi');
