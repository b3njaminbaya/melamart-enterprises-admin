<?php
/**
 * Get Overdue Orders for Admin Reminders
 */
require_once 'core.php';

$today = date('Y-m-d');

$stmt = $connect->prepare(
    "SELECT o.order_id, o.order_date, o.expect_return_date, o.site_location,
            o.client_name, o.client_contact, o.driver_name, o.driver_contact,
            o.grand_total, o.paid, o.due, o.order_status,
            GROUP_CONCAT(CONCAT(p.product_name, ' (Qty: ', oi.quantity, ')') SEPARATOR ', ') as items
     FROM orders o
     LEFT JOIN order_item oi ON o.order_id = oi.order_id
     LEFT JOIN product p ON oi.product_id = p.product_id
     WHERE (o.returned_date IS NULL OR o.returned_date = 0)
     AND o.expect_return_date != 0
     AND o.expect_return_date < ?
     AND o.order_status != 2
     GROUP BY o.order_id
     ORDER BY o.expect_return_date ASC"
);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$overdueOrders = [];

while($row = $result->fetch_assoc()) {
    $expectReturn = new DateTime($row['expect_return_date']);
    $currentDate = new DateTime($today);
    $overdueDays = $currentDate->diff($expectReturn)->days;
    
    $overdueOrders[] = [
        'order_id' => $row['order_id'],
        'order_date' => date('d/m/Y', strtotime($row['order_date'])),
        'expect_return_date' => date('d/m/Y', strtotime($row['expect_return_date'])),
        'overdue_days' => $overdueDays,
        'site_location' => $row['site_location'],
        'client_name' => $row['client_name'],
        'client_contact' => $row['client_contact'],
        'driver_name' => $row['driver_name'],
        'driver_contact' => $row['driver_contact'],
        'items' => $row['items'],
        'grand_total' => number_format($row['grand_total'], 2),
        'paid' => number_format($row['paid'], 2),
        'due' => number_format($row['due'], 2)
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'count' => count($overdueOrders),
    'orders' => $overdueOrders
]);
?>
