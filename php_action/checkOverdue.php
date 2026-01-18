<?php
require_once 'core.php';

function checkOverdueRentals($connect) {
    $today = date('Y-m-d');
    
    $sql = "SELECT o.order_id, o.client_name, o.client_contact, o.expect_return_date, 
                   o.grand_total, o.paid, o.due, oi.product_id, oi.quantity, p.daily_rate
            FROM orders o
            JOIN order_item oi ON o.order_id = oi.order_id
            JOIN product p ON oi.product_id = p.product_id
            WHERE o.returned_date = '0000-00-00' 
            AND o.expect_return_date < '$today'
            AND o.order_status != 2";
    
    $result = $connect->query($sql);
    $overdueOrders = array();
    
    while($row = $result->fetch_assoc()) {
        $expectReturn = new DateTime($row['expect_return_date']);
        $currentDate = new DateTime($today);
        $overdueDays = $currentDate->diff($expectReturn)->days;
        
        $additionalCharges = $row['daily_rate'] * $overdueDays * $row['quantity'];
        
        $overdueOrders[] = array(
            'order_id' => $row['order_id'],
            'client_name' => $row['client_name'],
            'overdue_days' => $overdueDays,
            'additional_charges' => $additionalCharges,
            'total_due' => $row['due'] + $additionalCharges
        );
    }
    
    return $overdueOrders;
}
?>