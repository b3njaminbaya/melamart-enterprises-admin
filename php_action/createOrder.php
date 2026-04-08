<?php 	
require_once 'core.php';

// Set header for JSON response
header('Content-Type: application/json');

// Initialize response
$valid = array('success' => false, 'messages' => '', 'order_id' => '', 'debug' => array());

// Start output buffering to catch any errors
ob_start();

try {
	// Check if POST data exists
	if(!$_POST || empty($_POST)) {
		$valid['messages'] = "No data received. Please fill in the form and try again.";
		$valid['debug']['post_empty'] = true;
		echo json_encode($valid);
		exit();
	}

	// Check session
	if(!isset($_SESSION['userId']) || empty($_SESSION['userId'])) {
		$valid['messages'] = "Session expired. Please login again.";
		$valid['debug']['session'] = 'NOT SET';
		echo json_encode($valid);
		exit();
	}

	$userid = intval($_SESSION['userId']);
	$valid['debug']['user_id'] = $userid;

	// Get form values with defaults
	$orderDate = isset($_POST['orderDate']) ? trim($_POST['orderDate']) : '';
	$expectReturnDate = isset($_POST['expectReturnDate']) ? trim($_POST['expectReturnDate']) : '';
	$siteLocation = isset($_POST['siteLocation']) ? trim($_POST['siteLocation']) : '';
	$clientName = isset($_POST['clientName']) ? trim($_POST['clientName']) : '';
	$clientContact = isset($_POST['clientContact']) ? trim($_POST['clientContact']) : '';
	$driverName = isset($_POST['driverName']) ? trim($_POST['driverName']) : '';
	$driverContact = isset($_POST['driverContact']) ? trim($_POST['driverContact']) : '';
	$subTotalValue = isset($_POST['subTotalValue']) ? trim($_POST['subTotalValue']) : '0';
	$vatValue = isset($_POST['vatValue']) ? trim($_POST['vatValue']) : '0.00';
	$totalAmountValue = isset($_POST['totalAmountValue']) ? trim($_POST['totalAmountValue']) : '0';
	$discount = isset($_POST['discount']) ? trim($_POST['discount']) : '0';
	$grandTotalValue = isset($_POST['grandTotalValue']) ? trim($_POST['grandTotalValue']) : '0';
	$paid = isset($_POST['paid']) ? trim($_POST['paid']) : '0';
	$dueValue = isset($_POST['dueValue']) ? trim($_POST['dueValue']) : '0';
	$paymentType = isset($_POST['paymentType']) ? intval($_POST['paymentType']) : 0;
	$paymentStatus = isset($_POST['paymentStatus']) ? intval($_POST['paymentStatus']) : 0;
	$paymentPlace = isset($_POST['paymentPlace']) ? intval($_POST['paymentPlace']) : 0;
	$gstn = isset($_POST['gstn']) ? trim($_POST['gstn']) : '';
	$orderStatusValue = isset($_POST['orderStatus']) ? intval($_POST['orderStatus']) : 0;

	// Validate critical fields
	if(empty($orderDate)) {
		$valid['messages'] = "Order Date is required.";
		echo json_encode($valid);
		exit();
	}

	if(empty($expectReturnDate)) {
		$valid['messages'] = "Expected Return Date is required.";
		echo json_encode($valid);
		exit();
	}

	if(empty($clientName)) {
		$valid['messages'] = "Client Name is required.";
		echo json_encode($valid);
		exit();
	}

	// Format dates
	$orderDateFormatted = date('Y-m-d', strtotime($orderDate));
	$expectReturnDateFormatted = date('Y-m-d', strtotime($expectReturnDate));

	if($orderDateFormatted == '1970-01-01' || $orderDateFormatted === false) {
		$valid['messages'] = "Invalid order date: " . $orderDate;
		echo json_encode($valid);
		exit();
	}

	if($expectReturnDateFormatted == '1970-01-01' || $expectReturnDateFormatted === false) {
		$valid['messages'] = "Invalid expected return date: " . $expectReturnDate;
		echo json_encode($valid);
		exit();
	}

	// Check for products
	$hasProducts = false;
	if(isset($_POST['productName']) && is_array($_POST['productName'])) {
		foreach($_POST['productName'] as $product) {
			if(!empty($product)) {
				$hasProducts = true;
				break;
			}
		}
	}

	if(!$hasProducts) {
		$valid['messages'] = "Please select at least one product.";
		echo json_encode($valid);
		exit();
	}

	// Escape string values
	$siteLocation = $connect->real_escape_string($siteLocation);
	$clientName = $connect->real_escape_string($clientName);
	$clientContact = $connect->real_escape_string($clientContact);
	$driverName = $connect->real_escape_string($driverName);
	$driverContact = $connect->real_escape_string($driverContact);
	$gstn = $connect->real_escape_string($gstn);

	// Build and execute INSERT query
	$sql = "INSERT INTO orders (
		order_date, 
		expect_return_date, 
		returned_date, 
		site_location, 
		client_name, 
		client_contact, 
		driver_name, 
		driver_contact, 
		returned_by, 
		returned_by_contact, 
		approved_by, 
		sub_total, 
		vat, 
		total_amount, 
		discount, 
		grand_total, 
		paid, 
		due, 
		payment_type, 
		payment_status, 
		payment_place, 
		gstn, 
		order_status, 
		user_id
	) VALUES (
		'$orderDateFormatted', 
		'$expectReturnDateFormatted', 
		NULL, 
		'$siteLocation', 
		'$clientName', 
		'$clientContact', 
		'$driverName', 
		'$driverContact', 
		'', 
		'', 
		'', 
		'$subTotalValue', 
		'$vatValue', 
		'$totalAmountValue', 
		'$discount', 
		'$grandTotalValue', 
		'$paid', 
		'$dueValue', 
		$paymentType, 
		$paymentStatus, 
		$paymentPlace, 
		'$gstn', 
		$orderStatusValue, 
		$userid
	)";

	// Execute order insert
	$order_id = 0;
	if($connect->query($sql) === true) {
		$order_id = $connect->insert_id;
		if($order_id > 0) {
			$valid['debug']['order_inserted'] = true;
			$valid['debug']['order_id'] = $order_id;
		} else {
			$valid['messages'] = "Order was created but no order ID was returned.";
			$valid['debug']['sql'] = $sql;
			$valid['debug']['error'] = $connect->error;
			echo json_encode($valid);
			exit();
		}
	} else {
		$valid['messages'] = "Database error: " . $connect->error;
		$valid['debug']['sql'] = $sql;
		$valid['debug']['error'] = $connect->error;
		echo json_encode($valid);
		exit();
	}

	// Process order items
	$itemsAdded = 0;
	if(isset($_POST['productName']) && is_array($_POST['productName']) && $order_id > 0) {
		$productCount = count($_POST['productName']);
		
		for($x = 0; $x < $productCount; $x++) {
			// Skip empty product selections
			if(empty($_POST['productName'][$x])) {
				continue;
			}
			
			$productId = intval($_POST['productName'][$x]);
			$quantity = isset($_POST['quantity'][$x]) ? intval($_POST['quantity'][$x]) : 0;
			$totalValue = isset($_POST['totalValue'][$x]) ? floatval($_POST['totalValue'][$x]) : 0;
			
			// Get daily rate
			$dailyRateValue = 0;
			if(isset($_POST['dailyRateValue'][$x]) && !empty($_POST['dailyRateValue'][$x])) {
				$dailyRateValue = floatval($_POST['dailyRateValue'][$x]);
			} else if(isset($_POST['rateValue'][$x]) && !empty($_POST['rateValue'][$x])) {
				$dailyRateValue = floatval($_POST['rateValue'][$x]);
			}
			
			if($productId <= 0 || $quantity <= 0) {
				continue;
			}
			
			// Get and update product quantity
			$productSql = "SELECT quantity FROM product WHERE product_id = $productId";
			$productResult = $connect->query($productSql);
			
			if($productResult && $productResult->num_rows > 0) {
				$productRow = $productResult->fetch_row();
				$currentQuantity = intval($productRow[0]);
				$newQuantity = max(0, $currentQuantity - $quantity);
				
				// Update product quantity
				$updateSql = "UPDATE product SET quantity = '$newQuantity' WHERE product_id = $productId";
				$connect->query($updateSql);
				
				// Insert order item
				$orderItemSql = "INSERT INTO order_item (order_id, product_id, quantity, rate, total, order_item_status) 
					VALUES ('$order_id', '$productId', '$quantity', '$dailyRateValue', '$totalValue', 1)";
				
				if($connect->query($orderItemSql)) {
					$itemsAdded++;
				}
			}
		}
	}

	// Final response
	if($order_id > 0 && $itemsAdded > 0) {
		$valid['success'] = true;
		$valid['messages'] = "Order #" . $order_id . " has been successfully created!";
		$valid['order_id'] = $order_id;
		$valid['debug']['items_added'] = $itemsAdded;
	} else if($order_id > 0) {
		$valid['success'] = false;
		$valid['messages'] = "Order #" . $order_id . " was created but no items were added. Please contact administrator.";
		$valid['order_id'] = $order_id;
		$valid['debug']['items_added'] = $itemsAdded;
	} else {
		$valid['success'] = false;
		$valid['messages'] = "Failed to create order. Please try again.";
	}

} catch(Exception $e) {
	$valid['messages'] = "Error: " . $e->getMessage();
	$valid['debug']['exception'] = $e->getMessage();
	$valid['debug']['trace'] = $e->getTraceAsString();
}

// Clear any output buffer
ob_clean();

echo json_encode($valid);
$connect->close();

?>
