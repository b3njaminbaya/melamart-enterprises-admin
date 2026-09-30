<?php
/**
 * Sends an overdue-return SMS reminder to the client of one order.
 */
require_once 'core.php';
require_once 'sms.php';

require_admin();
require_post();
csrf_verify();

$orderId = (int)($_POST['orderId'] ?? 0);
$order = null;
foreach(fetch_overdue_orders($connect) as $row) {
    if((int)$row['order_id'] === $orderId) {
        $order = $row;
    }
}
if(!$order) {
    json_out(array('success' => false, 'messages' => 'This order is not overdue (it may have just been returned).'));
}

list($ok, $message) = send_sms($connect, $order['client_contact'], client_overdue_message($order), $orderId);
if($ok) {
    $stmt = $connect->prepare("UPDATE orders SET reminder_sent = 1, last_reminder_date = NOW() WHERE order_id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $stmt->close();
}

json_out(array('success' => $ok, 'messages' => $message));
