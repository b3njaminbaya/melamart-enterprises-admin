<?php
/*
 * Daily overdue digest — run from cron, NOT from the browser:
 *   0 8 * * * /usr/bin/php /path/to/apps/admin/cron_sms_reminder.php
 *
 * Sends one SMS to MEL_SMS_ADMIN_PHONE listing the overdue orders.
 * Client reminders are sent by staff from the Overdue page.
 */

if(PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

require_once __DIR__ . '/php_action/helpers.php';
require_once __DIR__ . '/php_action/db_connect.php';
require_once __DIR__ . '/php_action/sms.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connect->query("SET time_zone = '+03:00'");

$logFile = __DIR__ . '/storage/logs/sms_cron.log';
$log = function($line) use ($logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' ' . $line . "\n", FILE_APPEND);
    echo $line . "\n";
};

$overdue = fetch_overdue_orders($connect);
if(!$overdue) {
    $log('No overdue orders.');
    exit(0);
}
if(!sms_configured() || MEL_SMS_ADMIN_PHONE === '') {
    $log(count($overdue) . ' overdue order(s); SMS not configured (set MEL_SMS_* in php_action/config.local.php).');
    exit(0);
}

$lines = array();
foreach(array_slice($overdue, 0, 5) as $o) {
    $days = hire_days($o['expect_return_date'], date('Y-m-d')) - 1;
    $lines[] = '#' . $o['order_id'] . ' ' . $o['client_name'] . ' ' . format_phone($o['client_contact']) . ' (' . $days . 'd late)';
}
$message = 'Melamart: ' . count($overdue) . ' overdue hire(s). ' . implode('; ', $lines)
    . (count($overdue) > 5 ? '; +' . (count($overdue) - 5) . ' more' : '') . '. See the Overdue page.';

list($ok, $result) = send_sms($connect, MEL_SMS_ADMIN_PHONE, $message);
$log(($ok ? 'Sent' : 'FAILED') . ': ' . $result);
exit($ok ? 0 : 1);
