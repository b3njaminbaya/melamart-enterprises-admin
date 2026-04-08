<?php 	

require_once 'core.php';

$sql = "SELECT order_id, order_date, expect_return_date, returned_date, site_location, client_name, client_contact, driver_name, driver_contact, returned_by, returned_by_contact, approved_by, grand_total, paid, due, payment_type, payment_status, order_status FROM orders WHERE order_status != 2";
$result = $connect->query($sql);

$output = array('data' => array());

if($result->num_rows > 0) { 
 
 $paymentStatus = ""; 
 $orderStatus = "";
 $x = 1;

 while($row = $result->fetch_array()) {
 	$orderId = $row[0];

 	$countOrderItemSql = "SELECT count(*) FROM order_item WHERE order_id = $orderId";
 	$itemCountResult = $connect->query($countOrderItemSql);
 	$itemCountRow = $itemCountResult->fetch_row();

 	// Format dates for display
 	$orderDate = date("d/m/Y", strtotime($row[1]));
 	$expectReturnDate = ($row[2] != '0000-00-00' && $row[2] != NULL) ? date("d/m/Y", strtotime($row[2])) : 'Not Set';
 	$returnedDate = ($row[3] != '0000-00-00' && $row[3] != NULL) ? date("d/m/Y", strtotime($row[3])) : 'Not Returned';

 	// payment type
 	$paymentType = "";
 	if($row[15] == 1) { 		
 		$paymentType = "<label class='label label-info'>Cheque</label>";
 	} else if($row[15] == 2) { 		
 		$paymentType = "<label class='label label-success'>Cash</label>";
 	} else if($row[15] == 3) { 		
 		$paymentType = "<label class='label label-primary'>Credit Card</label>";
 	} else { 		
 		$paymentType = "<label class='label label-default'>N/A</label>";
 	} // /else

 	// payment status 
 	if($row[16] == 1) { 		
 		$paymentStatus = "<label class='label label-success'>Full Payment</label>";
 	} else if($row[16] == 2) { 		
 		$paymentStatus = "<label class='label label-info'>Advance Payment</label>";
 	} else { 		
 		$paymentStatus = "<label class='label label-warning'>No Payment</label>";
 	} // /else

 	// order status
 	if($row[17] == 0) { 		
 		$orderStatus = "<label class='label label-default'>Pending</label>";
 	} else if($row[17] == 1) { 		
 		$orderStatus = "<label class='label label-primary'>Completed</label>";
 	} else if($row[17] == 2) { 		
 		$orderStatus = "<label class='label label-danger'>Cancelled</label>";
 	} else {
 		$orderStatus = "<label class='label label-default'>Unknown</label>";
 	} // /else

 	$button = '<!-- Single button -->
	<div class="btn-group">
	  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    Action <span class="caret"></span>
	  </button>
	  <ul class="dropdown-menu">
	    <li><a href="orders.php?o=editOrd&i='.$orderId.'" id="editOrderModalBtn"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
	    
	    <li><a type="button" data-toggle="modal" id="paymentOrderModalBtn" data-target="#paymentOrderModal" onclick="paymentOrder('.$orderId.')"> <i class="glyphicon glyphicon-save"></i> Payment</a></li>

	    <li><a type="button" onclick="printOrder('.$orderId.')"> <i class="glyphicon glyphicon-print"></i> Print </a></li>';
	    
	// Only show remove option for pending orders
	if($row[17] == 0) {
		$button .= '<li><a type="button" data-toggle="modal" data-target="#removeOrderModal" id="removeOrderModalBtn" onclick="removeOrder('.$orderId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>';
	}
	
	$button .= '</ul>
	</div>';		

 	$output['data'][] = array( 		
 		// serial number
 		$x,
 		// order date
 		$orderDate,
 		// expected return date
 		$expectReturnDate,
 		// returned date
 		$returnedDate,
 		// site location
 		$row[4],
 		// client name
 		$row[5], 
 		// client contact
 		$row[6], 
		// driver name
 		$row[7], 
 		// driver contact
 		$row[8],
 		// returned by
 		$row[9] ?: 'N/A',
 		// returned by contact
 		$row[10] ?: 'N/A',
 		// approved by
 		$row[11] ?: 'N/A',
 		// grand total
 		'Ksh ' . number_format($row[12], 2),
 		// paid amount
 		'Ksh ' . number_format($row[13], 2),
 		// due amount
 		'Ksh ' . number_format($row[14], 2),
 		// payment type
 		$paymentType,
 		// payment status
 		$paymentStatus,
 		// order status
 		$orderStatus,
 		// button
 		$button 		
 		); 	
 	$x++;
 } // /while 

}// if num_rows

$connect->close();

echo json_encode($output);