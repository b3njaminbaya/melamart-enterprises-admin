<?php 
require_once 'php_action/db_connect.php'; 
require_once 'includes/header.php'; 
?>

<ol class="breadcrumb">
  <li><a href="dashboard.php">Home</a></li>
  <li class="active">Overdue Reminders</li>
</ol>

<h4>
	<i class='glyphicon glyphicon-exclamation-sign'></i>
	Overdue Items Reminder
</h4>

<div class="panel panel-default">
	<div class="panel-heading">
		<i class="glyphicon glyphicon-bell"></i> Delayed Return Items
	</div>
	<div class="panel-body">
		<div id="overdue-messages"></div>
		
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-condensed" id="overdueOrdersTable">
				<thead>
					<tr>
						<th>#</th>
						<th>Order ID</th>
						<th>Order Date</th>
						<th>Expected Return</th>
						<th>Overdue Days</th>
						<th>Site Location</th>
						<th>Client Name</th>
						<th>Client Contact</th>
						<th>Driver Name</th>
						<th>Driver Contact</th>
						<th>Items</th>
						<th>Grand Total</th>
						<th>Paid</th>
						<th>Due</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<!-- Data will be loaded via AJAX -->
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	loadOverdueOrders();
	
	// Refresh every 5 minutes
	setInterval(function() {
		loadOverdueOrders();
	}, 300000);
});

function loadOverdueOrders() {
	$.ajax({
		url: 'php_action/getOverdueOrders.php',
		type: 'GET',
		dataType: 'json',
		success: function(response) {
			if(response.success) {
				var tbody = $('#overdueOrdersTable tbody');
				tbody.empty();
				
				if(response.count > 0) {
					$.each(response.orders, function(index, order) {
						var row = '<tr>';
						row += '<td>' + (index + 1) + '</td>';
						row += '<td>#' + order.order_id + '</td>';
						row += '<td>' + order.order_date + '</td>';
						row += '<td>' + order.expect_return_date + '</td>';
						row += '<td><span class="label label-danger">' + order.overdue_days + ' days</span></td>';
						row += '<td>' + order.site_location + '</td>';
						row += '<td>' + order.client_name + '</td>';
						row += '<td>' + order.client_contact + '</td>';
						row += '<td>' + order.driver_name + '</td>';
						row += '<td>' + order.driver_contact + '</td>';
						row += '<td>' + order.items + '</td>';
						row += '<td>Ksh ' + order.grand_total + '</td>';
						row += '<td>Ksh ' + order.paid + '</td>';
						row += '<td><strong>Ksh ' + order.due + '</strong></td>';
						row += '<td><a href="orders.php?o=editOrd&i=' + order.order_id + '" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-edit"></i> View</a></td>';
						row += '</tr>';
						tbody.append(row);
					});
					
					$("#overdue-messages").html('<div class="alert alert-warning">' +
						'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> ' +
						'Found ' + response.count + ' overdue order(s) requiring attention.' +
						'</div>');
				} else {
					tbody.append('<tr><td colspan="15" class="text-center">No overdue orders found.</td></tr>');
					$("#overdue-messages").html('<div class="alert alert-success">' +
						'<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' +
						'All orders are up to date!' +
						'</div>');
				}
			}
		},
		error: function() {
			$("#overdue-messages").html('<div class="alert alert-danger">' +
				'<strong>Error!</strong> Failed to load overdue orders.' +
				'</div>');
		}
	});
}
</script>

<?php require_once 'includes/footer.php'; ?>
