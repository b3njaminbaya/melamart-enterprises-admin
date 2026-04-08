<?php 
require_once 'core.php';

if($_POST) {
	$startDate = $_POST['startDate'];
	$date = DateTime::createFromFormat('m/d/Y', $startDate);
	$start_date = $date->format("Y-m-d");

	$endDate = $_POST['endDate'];
	$format = DateTime::createFromFormat('m/d/Y', $endDate);
	$end_date = $format->format("Y-m-d");

	$searchClient = isset($_POST['searchClient']) ? $_POST['searchClient'] : '';
	$paymentStatusFilter = isset($_POST['paymentStatusFilter']) ? $_POST['paymentStatusFilter'] : '';
	$orderStatusFilter = isset($_POST['orderStatusFilter']) ? $_POST['orderStatusFilter'] : '';
	$paymentTypeFilter = isset($_POST['paymentTypeFilter']) ? $_POST['paymentTypeFilter'] : '';

	$sql = "SELECT orders.*, 
					(CASE 
						WHEN orders.payment_status = 1 THEN 'Full Payment'
						WHEN orders.payment_status = 2 THEN 'Advance Payment'
						WHEN orders.payment_status = 3 THEN 'No Payment'
						ELSE 'Unknown'
					END) as payment_status_text,
					(CASE 
						WHEN orders.order_status = 0 THEN 'Pending'
						WHEN orders.order_status = 1 THEN 'Completed'
						WHEN orders.order_status = 2 THEN 'Cancelled'
						ELSE 'Unknown'
					END) as order_status_text
			FROM orders 
			WHERE order_date >= '$start_date' 
			AND order_date <= '$end_date' 
			AND order_status != 2";

	// Apply filters
	if(!empty($searchClient)) {
		$sql .= " AND client_name LIKE '%" . $connect->real_escape_string($searchClient) . "%'";
	}
	if(!empty($paymentStatusFilter)) {
		$sql .= " AND payment_status = " . intval($paymentStatusFilter);
	}
	if(!empty($orderStatusFilter)) {
		$sql .= " AND order_status = " . intval($orderStatusFilter);
	}
	if(!empty($paymentTypeFilter)) {
		$sql .= " AND payment_type = " . intval($paymentTypeFilter);
	}

	$sql .= " ORDER BY order_date DESC";

	$query = $connect->query($sql);
	$data = [];
	$totalAmount = 0;
	$totalPaid = 0;
	$totalDue = 0;
	$rowNumber = 0;
	
	while ($result = $query->fetch_assoc()) {
		$orderDate = date("d/m/Y", strtotime($result['order_date']));
		$expectReturnDate = ($result['expect_return_date'] != '0000-00-00' && $result['expect_return_date'] != NULL) ? date("d/m/Y", strtotime($result['expect_return_date'])) : 'Not Set';
		$returnedDate = ($result['returned_date'] != '0000-00-00' && $result['returned_date'] != NULL) ? date("d/m/Y", strtotime($result['returned_date'])) : 'Not Returned';
		
		$data[] = [
			'order_id' => $result['order_id'],
			'order_date' => $orderDate,
			'expect_return_date' => $expectReturnDate,
			'returned_date' => $returnedDate,
			'site_location' => $result['site_location'],
			'client_name' => $result['client_name'],
			'client_contact' => $result['client_contact'],
			'driver_name' => $result['driver_name'],
			'grand_total' => number_format($result['grand_total'], 2),
			'paid' => number_format($result['paid'], 2),
			'due' => number_format($result['due'], 2),
			'payment_status' => $result['payment_status_text'],
			'order_status' => $result['order_status_text']
		];
		
		$totalAmount += $result['grand_total'];
		$totalPaid += $result['paid'];
		$totalDue += $result['due'];
		$rowNumber++;
	}
	
	$summary = [
		'total_orders' => $rowNumber,
		'total_revenue' => number_format($totalAmount, 2),
		'total_paid' => number_format($totalPaid, 2),
		'total_due' => number_format($totalDue, 2)
	];

	echo json_encode([
		'success' => true,
		'data' => $data,
		'summary' => $summary
	]);
} else {
	echo json_encode([
		'success' => false,
		'message' => 'Invalid request'
	]);
}

$connect->close();
?>
