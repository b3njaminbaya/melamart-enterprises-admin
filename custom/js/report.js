$(document).ready(function() {
	$('#navReport').addClass('active');

	$("#startDate, #endDate").datepicker({ onSelect: function() { $(this).trigger('change'); } });

	function validDates() {
		var $form = $('#getOrderReportForm');
		clearFieldErrors($form);
		$('#report-messages').empty();
		var start = parseDMY($('#startDate').val());
		var end = parseDMY($('#endDate').val());
		var ok = true;
		if(!start) { fieldError($('#startDate'), 'Enter a date as dd/mm/yyyy.'); ok = false; }
		if(!end) { fieldError($('#endDate'), 'Enter a date as dd/mm/yyyy.'); ok = false; }
		if(ok && end < start) { fieldError($('#endDate'), 'Cannot be before the start date.'); ok = false; }
		return ok;
	}

	function queryString() {
		return $('#getOrderReportForm').serialize();
	}

	// View report in page
	$('#getOrderReportForm').on('submit', function(e) {
		e.preventDefault();
		if(!validDates()) { return false; }

		var $btn = $('#viewReportBtn').button('loading');
		$.ajax({
			url: 'php_action/getOrderReportData.php',
			type: 'POST',
			data: queryString(),
			dataType: 'json',
			success: function(response) {
				$btn.button('reset');
				if(response.success) {
					displayReportData(response.data, response.summary);
					$('#reportResults').show();
				} else {
					showAlert('#report-messages', 'error', response.messages);
				}
			}
		});
		return false;
	});

	$('#printReportBtn').on('click', function() {
		if(!validDates()) { return; }
		var win = window.open('php_action/getOrderReport.php?' + queryString(), '_blank');
		if(!win) { showToast('Your browser blocked the report window. Allow pop-ups for this site and try again.', 'warning'); }
	});

	$('#exportReportBtn').on('click', function() {
		if(!validDates()) { return; }
		window.location.href = 'php_action/exportReportCSV.php?' + queryString();
	});

	function displayReportData(data, summary) {
		var tbody = $('#reportTableBody');
		tbody.empty();

		if(!data.length) {
			tbody.append('<tr><td colspan="11" class="text-center text-muted" style="padding:20px;">No orders match these filters.</td></tr>');
		}
		$.each(data, function(index, order) {
			tbody.append('<tr>' +
				'<td><a href="orders.php?o=editOrd&i=' + Number(order.order_id) + '">#' + Number(order.order_id) + '</a></td>' +
				'<td>' + escapeHtml(order.order_date) + '</td>' +
				'<td>' + escapeHtml(order.expect_return_date) + '</td>' +
				'<td>' + escapeHtml(order.returned_date) + '</td>' +
				'<td>' + escapeHtml(order.site_location) + '<span class="sub">' + escapeHtml(order.branch) + '</span></td>' +
				'<td>' + escapeHtml(order.client_name) + '<span class="sub">' + escapeHtml(order.client_contact) + '</span></td>' +
				'<td class="text-right">' + escapeHtml(order.grand_total) + '</td>' +
				'<td class="text-right">' + escapeHtml(order.paid) + '</td>' +
				'<td class="text-right">' + escapeHtml(order.due) + '</td>' +
				'<td>' + escapeHtml(order.payment_status) + '</td>' +
				'<td>' + escapeHtml(order.order_status) + '</td>' +
				'</tr>');
		});

		var card = function(label, value, cls) {
			return '<div class="col-xs-6 col-md-3"><div class="stat-card ' + (cls || '') + '"><div class="stat-label">' + label +
				'</div><div class="stat-value">' + escapeHtml(value) + '</div></div></div>';
		};
		$('#reportSummary').html(
			card('Orders', summary.total_orders) +
			card('Billed (KSh)', summary.total_revenue) +
			card('Paid (KSh)', summary.total_paid) +
			card('Balance (KSh)', summary.total_due, 'stat-warning')
		);
	}
});
