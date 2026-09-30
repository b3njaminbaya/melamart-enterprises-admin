<?php
/**
 * Overdue orders (equipment still out past the expected return date).
 */
require_once 'core.php';
require_once 'sms.php';

require_admin();

$today = date('Y-m-d');
$orders = array();
foreach(fetch_overdue_orders($connect) as $row) {
    $orders[] = array(
        'order_id'           => (int)$row['order_id'],
        'order_date'         => format_date($row['order_date']),
        'expect_return_date' => format_date($row['expect_return_date']),
        'overdue_days'       => hire_days($row['expect_return_date'], $today) - 1,
        'site_location'      => $row['site_location'],
        'client_name'        => $row['client_name'],
        'client_contact'     => $row['client_contact'],
        'client_phone'       => format_phone($row['client_contact']),
        'driver_name'        => $row['driver_name'],
        'driver_phone'       => format_phone($row['driver_contact']),
        'items'              => (string)$row['items'],
        'grand_total'        => money($row['grand_total']),
        'due'                => money($row['due']),
        'late_fee'           => money(accrued_late_fee($connect, (int)$row['order_id'], $row['order_date'])),
        'sms_message'        => client_overdue_message($row),
        'last_reminder'      => $row['last_reminder_date'] ? date('d/m/Y H:i', strtotime($row['last_reminder_date'])) : '',
    );
}

json_out(array(
    'success'        => true,
    'count'          => count($orders),
    'orders'         => $orders,
    'sms_configured' => sms_configured(),
));
