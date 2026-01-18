<?php 

require_once 'core.php';

if($_POST) {

	$startDate = $_POST['startDate'];
	$date = DateTime::createFromFormat('m/d/Y',$startDate);
	$start_date = $date->format("Y-m-d");

	$endDate = $_POST['endDate'];
	$format = DateTime::createFromFormat('m/d/Y',$endDate);
	$end_date = $format->format("Y-m-d");

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
			AND order_status != 2"; // Exclude cancelled orders
	$query = $connect->query($sql);

	$table = '
	<table border="1" cellspacing="0" cellpadding="5" style="width:100%; font-size: 12px;">
		<tr style="background-color: #f2f2f2;">
			<th>#</th>
			<th>Order Date</th>
			<th>Expected Return</th>
			<th>Returned Date</th>
			<th>Site Location</th>
			<th>Client Name</th>
			<th>Client Contact</th>
			<th>Driver Name</th>
			<th>Driver Contact</th>
			<th>Sub Total</th>
			<th>VAT</th>
			<th>Discount</th>
			<th>Grand Total</th>
			<th>Paid</th>
			<th>Due</th>
			<th>Payment Type</th>
			<th>Payment Status</th>
			<th>Payment Place</th>
			<th>Order Status</th>
		</tr>';

	$totalAmount = 0;
	$totalPaid = 0;
	$totalDue = 0;
	$totalDiscount = 0;
	$totalVat = 0;
	$rowNumber = 1;
	
	while ($result = $query->fetch_assoc()) {
		// Format payment type
		$paymentType = '';
		switch($result['payment_type']) {
			case 1: $paymentType = 'Cheque'; break;
			case 2: $paymentType = 'Cash'; break;
			case 3: $paymentType = 'Credit Card'; break;
			default: $paymentType = 'N/A';
		}
		
		// Format payment place
		$paymentPlace = '';
		switch($result['payment_place']) {
			case 1: $paymentPlace = 'In Gujarat'; break;
			case 2: $paymentPlace = 'Out of Gujarat'; break;
			default: $paymentPlace = 'N/A';
		}
		
		// Format dates
		$orderDate = date("d/m/Y", strtotime($result['order_date']));
		$expectReturnDate = ($result['expect_return_date'] != '0000-00-00') ? date("d/m/Y", strtotime($result['expect_return_date'])) : 'Not Set';
		$returnedDate = ($result['returned_date'] != '0000-00-00') ? date("d/m/Y", strtotime($result['returned_date'])) : 'Not Returned';
		
		$table .= '<tr>
			<td align="center">'.$rowNumber.'</td>
			<td align="center">'.$orderDate.'</td>
			<td align="center">'.$expectReturnDate.'</td>
			<td align="center">'.$returnedDate.'</td>
			<td>'.$result['site_location'].'</td>
			<td>'.$result['client_name'].'</td>
			<td align="center">'.$result['client_contact'].'</td>
			<td>'.$result['driver_name'].'</td>
			<td align="center">'.$result['driver_contact'].'</td>
			<td align="right">'.number_format($result['sub_total'], 2).'</td>
			<td align="right">'.number_format($result['vat'], 2).'</td>
			<td align="right">'.number_format($result['discount'], 2).'</td>
			<td align="right">'.number_format($result['grand_total'], 2).'</td>
			<td align="right">'.number_format($result['paid'], 2).'</td>
			<td align="right">'.number_format($result['due'], 2).'</td>
			<td align="center">'.$paymentType.'</td>
			<td align="center">'.$result['payment_status_text'].'</td>
			<td align="center">'.$paymentPlace.'</td>
			<td align="center">'.$result['order_status_text'].'</td>
		</tr>';	
		
		$totalAmount += $result['grand_total'];
		$totalPaid += $result['paid'];
		$totalDue += $result['due'];
		$totalDiscount += $result['discount'];
		$totalVat += $result['vat'];
		$rowNumber++;
	}
	
	// Summary row
	$table .= '<tr style="background-color: #e8f4f8; font-weight: bold;">
		<td colspan="9" align="right"><strong>Totals:</strong></td>
		<td align="right"><strong>'.number_format($totalAmount - $totalVat + $totalDiscount, 2).'</strong></td>
		<td align="right"><strong>'.number_format($totalVat, 2).'</strong></td>
		<td align="right"><strong>'.number_format($totalDiscount, 2).'</strong></td>
		<td align="right"><strong>'.number_format($totalAmount, 2).'</strong></td>
		<td align="right"><strong>'.number_format($totalPaid, 2).'</strong></td>
		<td align="right"><strong>'.number_format($totalDue, 2).'</strong></td>
		<td colspan="4"></td>
	</tr>';
	
	$table .= '</table>';
	
	// Add report header
	$reportHeader = '
	<div style="text-align: center; margin-bottom: 20px;">
		<h2>Order Report</h2>
		<p>Period: '.date("d/m/Y", strtotime($start_date)).' to '.date("d/m/Y", strtotime($end_date)).'</p>
		<p>Generated on: '.date("d/m/Y H:i:s").'</p>
	</div>
	';
	
	$summary = '
	<div style="margin-top: 20px; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd;">
		<h3>Summary</h3>
		<p><strong>Total Orders:</strong> '.($rowNumber - 1).'</p>
		<p><strong>Total Grand Total:</strong> '.number_format($totalAmount, 2).'</p>
		<p><strong>Total Paid:</strong> '.number_format($totalPaid, 2).'</p>
		<p><strong>Total Due:</strong> '.number_format($totalDue, 2).'</p>
		<p><strong>Total Discount:</strong> '.number_format($totalDiscount, 2).'</p>
		<p><strong>Total VAT:</strong> '.number_format($totalVat, 2).'</p>
	</div>
	';

	echo $reportHeader . $table . $summary;

}

?>