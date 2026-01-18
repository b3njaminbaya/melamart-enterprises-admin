<?php 	
//ALTER TABLE `orders` ADD `payment_place` INT NOT NULL AFTER `payment_status`;
//ALTER TABLE `orders` ADD `gstn` VARCHAR(255) NOT NULL AFTER `payment_place`;
require_once 'core.php';

// Add this function after line 1
function calculateFinalCharges($orderId, $connect) {
    $sql = "SELECT o.order_date, o.expect_return_date, o.returned_date, 
                   oi.product_id, oi.quantity, p.daily_rate
            FROM orders o
            JOIN order_item oi ON o.order_id = oi.order_id
            JOIN product p ON oi.product_id = p.product_id
            WHERE o.order_id = $orderId";
    
    $result = $connect->query($sql);
    $totalCharges = 0;
    
    while($row = $result->fetch_assoc()) {
        $orderDate = new DateTime($row['order_date']);
        $returnDate = new DateTime($row['returned_date'] != '0000-00-00' ? $row['returned_date'] : date('Y-m-d'));
        
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

$valid['success'] = array('success' => false, 'messages' => array(), 'order_id' => '');
// print_r($valid);
if($_POST) {	

	$orderDate 						= date('Y-m-d', strtotime($_POST['orderDate']));	
	$expectReturnDate 			= date('Y-m-d', strtotime($_POST['expectReturnDate']));	
	$siteLocation 					= $_POST['siteLocation'];
	$clientName 					= $_POST['clientName'];
	$clientContact 				= $_POST['clientContact'];
	$driverName 					= $_POST['driverName'];
	$driverContact 				= $_POST['driverContact'];
	$subTotalValue 				= $_POST['subTotalValue'];
	$vatValue 						= $_POST['vatValue'];
	$totalAmountValue     	= $_POST['totalAmountValue'];
	$discount 						= $_POST['discount'];
	$grandTotalValue 			= $_POST['grandTotalValue'];
	$paid 								= $_POST['paid'];
	$dueValue 						= $_POST['dueValue'];
	$paymentType 					= $_POST['paymentType'];
	$paymentStatus 				= $_POST['paymentStatus'];
	$paymentPlace 				= $_POST['paymentPlace'];
	$gstn 								= $_POST['gstn'];
	$orderStatusValue			= $_POST['orderStatus'];
	$userid 							= $_SESSION['userId'];

	// Set default values for return fields (they will be updated later when items are returned)
	$returnedDate = '0000-00-00'; // Default date
	$returnedBy = '';
	$returnedByContact = '';
	$approvedBy = '';
				
	$sql = "INSERT INTO orders (order_date, expect_return_date, returned_date, site_location, client_name, client_contact, driver_name, driver_contact, returned_by, returned_by_contact, approved_by, sub_total, vat, total_amount, discount, grand_total, paid, due, payment_type, payment_status, payment_place, gstn, order_status, user_id) 
			VALUES ('$orderDate', '$expectReturnDate', '$returnedDate', '$siteLocation', '$clientName', '$clientContact', '$driverName', '$driverContact', '$returnedBy', '$returnedByContact', '$approvedBy', '$subTotalValue', '$vatValue', '$totalAmountValue', '$discount', '$grandTotalValue', '$paid', '$dueValue', $paymentType, $paymentStatus, $paymentPlace, '$gstn', $orderStatusValue, $userid)";
	
	$order_id;
	$orderStatus = false;
	if($connect->query($sql) === true) {
		$order_id = $connect->insert_id;
		$valid['order_id'] = $order_id;	
		$orderStatus = true;
	} else {
		$valid['success'] = false;
		$valid['messages'] = "Error while creating order: " . $connect->error;
		echo json_encode($valid);
		exit();
	}

	$orderItemStatus = false;

	for($x = 0; $x < count($_POST['productName']); $x++) {			
		$updateProductQuantitySql = "SELECT product.quantity FROM product WHERE product.product_id = ".$_POST['productName'][$x]."";
		$updateProductQuantityData = $connect->query($updateProductQuantitySql);
		
		while ($updateProductQuantityResult = $updateProductQuantityData->fetch_row()) {
			$updateQuantity[$x] = $updateProductQuantityResult[0] - $_POST['quantity'][$x];							
				// update product table
				$updateProductTable = "UPDATE product SET quantity = '".$updateQuantity[$x]."' WHERE product_id = ".$_POST['productName'][$x]."";
				$connect->query($updateProductTable);

				// add into order_item
				$orderItemSql = "INSERT INTO order_item (order_id, product_id, quantity, rate, total, order_item_status) 
				VALUES ('$order_id', '".$_POST['productName'][$x]."', '".$_POST['quantity'][$x]."', '".$_POST['rateValue'][$x]."', '".$_POST['totalValue'][$x]."', 1)";

				$connect->query($orderItemSql);		

				if($x == (count($_POST['productName']) - 1)) {
					$orderItemStatus = true;
				}		
		} // while	
	} // /for quantity

	if($orderStatus && $orderItemStatus) {
		$valid['success'] = true;
		$valid['messages'] = "Successfully Added";
	} else {
		$valid['success'] = false;
		$valid['messages'] = "Error while adding order items";
	}
	
	$connect->close();

	echo json_encode($valid);
 
} // /if $_POST
// echo json_encode($valid);