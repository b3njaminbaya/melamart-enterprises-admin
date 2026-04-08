<?php
/**
 * Update Late Fees for Overdue Orders
 * This script should be run daily via cron job to increment charges for overdue items
 */
require_once 'core.php';

function updateLateFees($connect) {
    $today = date('Y-m-d');
    $updatedCount = 0;
    $totalLateFees = 0;
    
    // Find all overdue orders that haven't been returned
    $sql = "SELECT o.order_id, o.order_date, o.expect_return_date, o.grand_total, o.due, o.last_late_fee_calc,
                   oi.order_item_id, oi.product_id, oi.quantity, oi.rate as daily_rate
            FROM orders o
            JOIN order_item oi ON o.order_id = oi.order_id
            WHERE (o.returned_date IS NULL OR o.returned_date = '0000-00-00')
            AND o.expect_return_date < '$today'
            AND o.order_status != 2
            ORDER BY o.order_id";
    
    $result = $connect->query($sql);
    $orders = [];
    
    // Group items by order
    while($row = $result->fetch_assoc()) {
        $orderId = $row['order_id'];
        if(!isset($orders[$orderId])) {
            $orders[$orderId] = [
                'order_id' => $orderId,
                'order_date' => $row['order_date'],
                'expect_return_date' => $row['expect_return_date'],
                'grand_total' => $row['grand_total'],
                'due' => $row['due'],
                'last_late_fee_calc' => $row['last_late_fee_calc'],
                'items' => []
            ];
        }
        $orders[$orderId]['items'][] = [
            'order_item_id' => $row['order_item_id'],
            'product_id' => $row['product_id'],
            'quantity' => $row['quantity'],
            'daily_rate' => $row['daily_rate']
        ];
    }
    
    // Calculate and update late fees for each order
    foreach($orders as $order) {
        $orderId = $order['order_id'];
        $expectReturn = new DateTime($order['expect_return_date']);
        $currentDate = new DateTime($today);
        $overdueDays = $currentDate->diff($expectReturn)->days;
        
        // Only process if overdue
        if($overdueDays > 0) {
            // Calculate last calculation date
            $lastCalcDate = $order['last_late_fee_calc'] ? new DateTime($order['last_late_fee_calc']) : $expectReturn;
            $daysSinceLastCalc = $currentDate->diff($lastCalcDate)->days;
            
            // Only update if a day has passed since last calculation
            if($daysSinceLastCalc >= 1) {
                $additionalCharges = 0;
                
                // Calculate additional charges for each item
                foreach($order['items'] as $item) {
                    $dailyRate = floatval($item['daily_rate']);
                    $quantity = intval($item['quantity']);
                    // Charge for each overdue day
                    $itemLateFee = $dailyRate * $quantity * $daysSinceLastCalc;
                    $additionalCharges += $itemLateFee;
                }
                
                if($additionalCharges > 0) {
                    // Update order with new charges
                    $newGrandTotal = floatval($order['grand_total']) + $additionalCharges;
                    $newDue = floatval($order['due']) + $additionalCharges;
                    
                    $updateSql = "UPDATE orders SET 
                                  grand_total = '$newGrandTotal',
                                  due = '$newDue',
                                  last_late_fee_calc = NOW()
                                  WHERE order_id = $orderId";
                    
                    if($connect->query($updateSql)) {
                        $updatedCount++;
                        $totalLateFees += $additionalCharges;
                    }
                }
            }
        }
    }
    
    return [
        'success' => true,
        'updated_orders' => $updatedCount,
        'total_late_fees' => $totalLateFees
    ];
}

// Run the update
$result = updateLateFees($connect);
echo json_encode($result);

$connect->close();
?>
