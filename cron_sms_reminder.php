<?php
// This file should be called by a cron job daily
require_once 'php_action/db_connect.php';
require_once 'sms_reminder.php';

$smsReminder = new SMSReminder($connect);
$result = $smsReminder->processReminders();

// Log results to a file
file_put_contents('sms_reminder_log.txt', date('Y-m-d H:i:s') . ' - ' . json_encode($result) . "\n", FILE_APPEND);
?>