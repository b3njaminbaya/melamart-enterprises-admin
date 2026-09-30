<?php
/*
 * Order balance + payment history for the "Record payment" modal.
 */
require_once 'core.php';

$orderId = (int)($_POST['orderId'] ?? 0);

$stmt = $connect->prepare("SELECT order_id, client_name, grand_total, paid, due, payment_type, payment_status, order_status FROM orders WHERE order_id = ?");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$order) {
    json_out(array('success' => false, 'messages' => 'Order not found.'));
}

$stmt = $connect->prepare(
    "SELECT ph.amount, ph.payment_type, ph.reference, ph.payment_date, u.username
     FROM payment_history ph
     LEFT JOIN users u ON u.user_id = ph.received_by
     WHERE ph.order_id = ?
     ORDER BY ph.payment_date, ph.payment_id"
);
$stmt->bind_param('i', $orderId);
$stmt->execute();
$res = $stmt->get_result();
$history = array();
while($row = $res->fetch_assoc()) {
    $history[] = array(
        'date'        => date('d/m/Y H:i', strtotime($row['payment_date'])),
        'amount'      => money($row['amount']),
        'method'      => payment_type_label($row['payment_type']),
        'reference'   => $row['reference'],
        'received_by' => $row['username'] ?? '',
    );
}
$stmt->close();

json_out(array(
    'success' => true,
    'order' => array(
        'order_id'       => (int)$order['order_id'],
        'client_name'    => $order['client_name'],
        'grand_total'    => (float)$order['grand_total'],
        'paid'           => (float)$order['paid'],
        'due'            => (float)$order['due'],
        'payment_type'   => (int)$order['payment_type'],
        'payment_status' => payment_status_label($order['payment_status']),
        'order_status'   => (int)$order['order_status'],
    ),
    'history' => $history,
));
