<?php	
require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {
    $orderId = $_POST['orderId'];
    $payAmount = (float)($_POST['payAmount'] ?? 0);
    $paymentType = (int)($_POST['paymentType'] ?? 0);
    $paymentStatus = (int)($_POST['paymentStatus'] ?? 0);
    $currentPaid = (float)($_POST['currentPaid'] ?? 0);
    $newPaid = (float)($_POST['newPaid'] ?? 0);
    $newDue = (float)($_POST['newDue'] ?? 0);
    $grandTotal = (float)($_POST['grandTotal'] ?? 0);
    
    // Debug log
    error_log("Edit Payment - Order: $orderId, Pay: $payAmount");
    error_log("Current Paid: $currentPaid, New Paid: $newPaid, New Due: $newDue, Grand Total: $grandTotal");
    
    // VALIDATION FIXED:
    // 1. Check if payment amount is positive
    // 2. Check if payment doesn't exceed the due amount (not grand total)
    // 3. Check if new paid doesn't exceed grand total
    
    if($payAmount <= 0) {
        $valid['success'] = false;
        $valid['messages'] = "Payment amount must be greater than 0";
    } 
    // Check if payment exceeds current due amount
    elseif($payAmount > ($grandTotal - $currentPaid)) {
        $valid['success'] = false;
        $valid['messages'] = "Payment cannot exceed due amount of ₹" . ($grandTotal - $currentPaid);
    }
    // Check if new paid exceeds grand total (shouldn't happen with above check)
    elseif($newPaid > $grandTotal) {
        $valid['success'] = false;
        $valid['messages'] = "Total paid cannot exceed grand total";
    } 
    else {
        // Update order payment
        $sql = "UPDATE orders SET 
                    paid = '$newPaid',
                    due = '$newDue',
                    payment_type = '$paymentType',
                    payment_status = '$paymentStatus'
                WHERE order_id = $orderId";
        
        if($connect->query($sql) === TRUE) {
            $valid['success'] = true;
            $valid['messages'] = "Payment updated successfully";
            
            // Insert payment record into payment history (optional)
            $paymentSql = "INSERT INTO payment_history (order_id, amount, payment_type, payment_date, received_by) 
                           VALUES ('$orderId', '$payAmount', '$paymentType', NOW(), '{$_SESSION['userId']}')";
            $connect->query($paymentSql);
            
        } else {
            $valid['success'] = false;
            $valid['messages'] = "Error while updating payment: " . $connect->error;
        }
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>