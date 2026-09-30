<?php require_once 'includes/header.php'; ?>
<?php require_admin(); ?>

<ol class="breadcrumb">
  <li><a href="dashboard.php">Home</a></li>
  <li class="active">Overdue returns</li>
</ol>

<div class="panel panel-default">
	<div class="panel-heading">
		<i class="glyphicon glyphicon-bell"></i> Equipment not returned on time
	</div>
	<div class="panel-body">
		<div id="overdue-messages"></div>
		<p class="text-muted small">
			The late charge is an estimate up to today (extra days × daily rate × quantity). It is added to the order when you record the return.
		</p>

		<div class="table-responsive">
			<table class="table table-bordered table-striped table-condensed" id="overdueOrdersTable">
				<thead>
					<tr>
						<th>Order</th>
						<th>Expected return</th>
						<th>Overdue</th>
						<th>Client</th>
						<th>Site</th>
						<th>Equipment</th>
						<th class="text-right">Balance (KSh)</th>
						<th class="text-right">Late charge so far</th>
						<th>Last SMS</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr><td colspan="10" class="text-center text-muted">Loading…</td></tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- confirm SMS -->
<div class="modal fade" tabindex="-1" role="dialog" id="smsModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-phone"></i> Send SMS reminder</h4>
      </div>
      <div class="modal-body">
        <p>Send this message to <strong id="smsTo"></strong>?</p>
        <blockquote id="smsText" style="font-size:13px;"></blockquote>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="sendSmsBtn" data-loading-text="Sending…"><i class="glyphicon glyphicon-send"></i> Send SMS</button>
      </div>
    </div>
  </div>
</div>

<script>
var overdueSmsReady = false;

$(document).ready(function() {
	$('#navOverdue').addClass('active');
	loadOverdueOrders();

	// Refresh every 5 minutes
	setInterval(function() { loadOverdueOrders(true); }, 300000);
});

function loadOverdueOrders(silent) {
	$.ajax({
		url: 'php_action/getOverdueOrders.php',
		type: 'GET',
		dataType: 'json',
		global: !silent,
		success: function(response) {
			if(!response.success) { return; }
			overdueSmsReady = response.sms_configured;
			var tbody = $('#overdueOrdersTable tbody');
			tbody.empty();

			if(response.count === 0) {
				tbody.append('<tr><td colspan="10" class="text-center">No overdue orders.</td></tr>');
				showAlert('#overdue-messages', 'success', 'All equipment on hire is within its return date.');
				return;
			}

			$.each(response.orders, function(index, order) {
				var smsButton = overdueSmsReady
					? '<button type="button" class="btn btn-default btn-sm send-sms" data-index="' + index + '"><i class="glyphicon glyphicon-phone"></i> SMS client</button>'
					: '';
				tbody.append('<tr>' +
					'<td><strong>#' + Number(order.order_id) + '</strong><span class="sub">' + escapeHtml(order.order_date) + '</span></td>' +
					'<td>' + escapeHtml(order.expect_return_date) + '</td>' +
					'<td><span class="label label-danger">' + Number(order.overdue_days) + ' day' + (order.overdue_days === 1 ? '' : 's') + '</span></td>' +
					'<td>' + escapeHtml(order.client_name) + '<span class="sub"><a href="tel:' + escapeHtml(order.client_contact) + '">' + escapeHtml(order.client_phone) + '</a></span></td>' +
					'<td>' + escapeHtml(order.site_location) + '<span class="sub">Driver: ' + escapeHtml(order.driver_name) + ' ' + escapeHtml(order.driver_phone) + '</span></td>' +
					'<td>' + escapeHtml(order.items) + '</td>' +
					'<td class="text-right">' + escapeHtml(order.due) + '</td>' +
					'<td class="text-right"><strong>' + escapeHtml(order.late_fee) + '</strong></td>' +
					'<td>' + (order.last_reminder ? escapeHtml(order.last_reminder) : '<span class="text-muted">—</span>') + '</td>' +
					'<td class="text-nowrap">' +
						'<a href="orders.php?o=editOrd&i=' + Number(order.order_id) + '" class="btn btn-primary btn-sm"><i class="glyphicon glyphicon-edit"></i> Record return</a> ' +
						smsButton +
					'</td>' +
					'</tr>');
			});

			var msg = response.count + ' order' + (response.count === 1 ? ' is' : 's are') + ' overdue.';
			if(!overdueSmsReady) {
				msg += ' SMS reminders are not set up yet — add the Africa\'s Talking details to php_action/config.local.php to enable them.';
			}
			showAlert('#overdue-messages', 'warning', msg);

			$('.send-sms').on('click', function() {
				confirmSms(response.orders[$(this).data('index')]);
			});
		}
	});
}

function confirmSms(order) {
	$('#smsTo').text(order.client_name + ' (' + order.client_phone + ')');
	$('#smsText').text(order.sms_message);
	$('#smsModal').modal('show');

	$('#sendSmsBtn').off('click').on('click', function() {
		var $btn = $(this).button('loading');
		$.ajax({
			url: 'php_action/sendSmsReminder.php',
			type: 'POST',
			data: { orderId: order.order_id },
			dataType: 'json',
			success: function(response) {
				$btn.button('reset');
				$('#smsModal').modal('hide');
				showToast(response.messages, response.success ? 'success' : 'error');
				if(response.success) { loadOverdueOrders(); }
			}
		});
	});
}
</script>

<?php require_once 'includes/footer.php'; ?>
