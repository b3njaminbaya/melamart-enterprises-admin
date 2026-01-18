<?php	
require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {	
    $orderId = $_POST['orderId'];

    // Get all form data
    $orderDate = date('Y-m-d', strtotime($_POST['orderDate']));
    $expectReturnDate = date('Y-m-d', strtotime($_POST['expectReturnDate']));
    
    // FIX: Properly handle returned date
    $returnedDate = '';
    if(isset($_POST['returnedDate']) && !empty($_POST['returnedDate']) && $_POST['returnedDate'] != '0000-00-00') {
        $returnedDate = date('Y-m-d', strtotime($_POST['returnedDate']));
    } else {
        $returnedDate = '0000-00-00';
    }
    
    $siteLocation = $_POST['siteLocation'];
    $clientName = $_POST['clientName'];
    $clientContact = $_POST['clientContact'];
    $driverName = $_POST['driverName'];
    $driverContact = $_POST['driverContact'];
    $returnedBy = $_POST['returnedBy'];
    $returnedByContact = $_POST['returnedByContact'];
    $approvedBy = $_POST['approvedBy'];
    $subTotalValue = $_POST['subTotalValue'];
    $vatValue = $_POST['vatValue'];
    $totalAmountValue = $_POST['totalAmountValue'];
    $discount = $_POST['discount'];
    $grandTotalValue = $_POST['grandTotalValue'];
    $paid = $_POST['paid'];
    $dueValue = $_POST['dueValue'];
    $paymentType = $_POST['paymentType'];
    $paymentStatus = $_POST['paymentStatus'];
    $paymentPlace = $_POST['paymentPlace'];
    $gstn = $_POST['gstn'];
    $orderStatusValue = $_POST['orderStatus'];
    $userid = $_SESSION['userId'];

    // Debug: Log the received data
    error_log("Edit Order - Order ID: $orderId");
    error_log("Returned Date from POST: " . ($_POST['returnedDate'] ?? 'NOT SET'));
    error_log("Formatted Returned Date: $returnedDate");

    // Calculate final charges if returned date is set
    function calculateFinalCharges($orderId, $connect) {
        $sql = "SELECT o.order_date, oi.product_id, oi.quantity, p.daily_rate
                FROM orders o
                JOIN order_item oi ON o.order_id = oi.order_id
                JOIN product p ON oi.product_id = p.product_id
                WHERE o.order_id = $orderId";
        
        $result = $connect->query($sql);
        $totalCharges = 0;
        
        while($row = $result->fetch_assoc()) {
            $orderDate = new DateTime($row['order_date']);
            $returnDate = new DateTime(); // Use current date
            
            // Calculate actual rental days
            $interval = $orderDate->diff($returnDate);
            $actualDays = $interval->days + 1; // Include start day
            
            $dailyRate = $row['daily_rate'];
            $quantity = $row['quantity'];
            
            $itemTotal = $dailyRate * $actualDays * $quantity;
            $totalCharges += $itemTotal;
        }
        
        return $totalCharges;
    }

    // Update the order
    $sql = "UPDATE orders SET 
                order_date = '$orderDate', 
                expect_return_date = '$expectReturnDate', 
                returned_date = '$returnedDate', 
                site_location = '$siteLocation', 
                client_name = '$clientName', 
                client_contact = '$clientContact', 
                driver_name = '$driverName', 
                driver_contact = '$driverContact', 
                returned_by = '$returnedBy', 
                returned_by_contact = '$returnedByContact', 
                approved_by = '$approvedBy', 
                sub_total = '$subTotalValue', 
                vat = '$vatValue', 
                total_amount = '$totalAmountValue', 
                discount = '$discount', 
                grand_total = '$grandTotalValue', 
                paid = '$paid', 
                due = '$dueValue', 
                payment_type = '$paymentType', 
                payment_status = '$paymentStatus', 
                payment_place = '$paymentPlace', 
                gstn = '$gstn', 
                order_status = '$orderStatusValue',
                user_id = '$userid' 
            WHERE order_id = {$orderId}";
    
    error_log("Update SQL: $sql");
    
    if($connect->query($sql) === TRUE) {
        // Recalculate charges if returned date is set
        if($returnedDate != '0000-00-00') {
            $finalCharges = calculateFinalCharges($orderId, $connect);
            
            // Update the order with final charges
            $updateChargesSql = "UPDATE orders SET grand_total = '$finalCharges' WHERE order_id = $orderId";
            if($connect->query($updateChargesSql) !== TRUE) {
                error_log("Error updating charges: " . $connect->error);
            }
            
            // Also update due amount
            $newDue = $finalCharges - $paid;
            if($newDue > 0) {
                $updateDueSql = "UPDATE orders SET due = '$newDue' WHERE order_id = $orderId";
                $connect->query($updateDueSql);
            }
        }
        
        $valid['success'] = true;
        $valid['messages'] = "Successfully Updated";
        
        // Debug: Verify update
        $checkSql = "SELECT returned_date FROM orders WHERE order_id = $orderId";
        $checkResult = $connect->query($checkSql);
        $checkData = $checkResult->fetch_assoc();
        error_log("Verified Returned Date in DB: " . $checkData['returned_date']);
        
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Error updating order: " . $connect->error;
        error_log("SQL Error: " . $connect->error);
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>