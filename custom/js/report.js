$(document).ready(function() {
	// order date picker
	$("#startDate").datepicker();
	$("#endDate").datepicker();

	// Generate PDF/Print Report
	$("#getOrderReportForm").unbind('submit').bind('submit', function() {
		var startDate = $("#startDate").val();
		var endDate = $("#endDate").val();

		if(startDate == "" || endDate == "") {
			if(startDate == "") {
				$("#startDate").closest('.form-group').addClass('has-error');
				$("#startDate").after('<p class="text-danger">The Start Date is required</p>');
			} else {
				$(".form-group").removeClass('has-error');
				$(".text-danger").remove();
			}

			if(endDate == "") {
				$("#endDate").closest('.form-group').addClass('has-error');
				$("#endDate").after('<p class="text-danger">The End Date is required</p>');
			} else {
				$(".form-group").removeClass('has-error');
				$(".text-danger").remove();
			}
		} else {
			$(".form-group").removeClass('has-error');
			$(".text-danger").remove();

			var form = $(this);

			$.ajax({
				url: form.attr('action'),
				type: form.attr('method'),
				data: form.serialize(),
				dataType: 'text',
				success:function(response) {
					var mywindow = window.open('', 'Order Report', 'height=400,width=800');
	        mywindow.document.write('<html><head><title>Order Report</title>');        
	        mywindow.document.write('<style>table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}</style>');
	        mywindow.document.write('</head><body>');
	        mywindow.document.write(response);
	        mywindow.document.write('</body></html>');

	        mywindow.document.close();
	        mywindow.focus();
	        mywindow.print();
	        mywindow.close();
				}
			});
		}
		return false;
	});

	// View Report in Page
	$("#viewReportBtn").on('click', function() {
		var startDate = $("#startDate").val();
		var endDate = $("#endDate").val();

		if(startDate == "" || endDate == "") {
			$("#report-messages").html('<div class="alert alert-danger">Please select start and end dates.</div>');
			return;
		}

		loadReportData();
	});

	// Export CSV
	$("#exportReportBtn").on('click', function() {
		var startDate = $("#startDate").val();
		var endDate = $("#endDate").val();

		if(startDate == "" || endDate == "") {
			$("#report-messages").html('<div class="alert alert-danger">Please select start and end dates.</div>');
			return;
		}

		exportToCSV();
	});

	function loadReportData() {
		$("#generateReportBtn").button('loading');
		
		$.ajax({
			url: 'php_action/getOrderReportData.php',
			type: 'POST',
			data: {
				startDate: $("#startDate").val(),
				endDate: $("#endDate").val(),
				searchClient: $("#searchClient").val(),
				paymentStatusFilter: $("#paymentStatusFilter").val(),
				orderStatusFilter: $("#orderStatusFilter").val(),
				paymentTypeFilter: $("#paymentTypeFilter").val()
			},
			dataType: 'json',
			success: function(response) {
				$("#generateReportBtn").button('reset');
				
				if(response.success) {
					displayReportData(response.data, response.summary);
					$("#reportResults").show();
				} else {
					$("#report-messages").html('<div class="alert alert-danger">' + response.message + '</div>');
				}
			},
			error: function() {
				$("#generateReportBtn").button('reset');
				$("#report-messages").html('<div class="alert alert-danger">Error loading report data.</div>');
			}
		});
	}

	function displayReportData(data, summary) {
		var tbody = $("#reportTableBody");
		tbody.empty();

		$.each(data, function(index, order) {
			var row = '<tr>';
			row += '<td>' + (index + 1) + '</td>';
			row += '<td>' + order.order_date + '</td>';
			row += '<td>' + order.expect_return_date + '</td>';
			row += '<td>' + order.returned_date + '</td>';
			row += '<td>' + order.site_location + '</td>';
			row += '<td>' + order.client_name + '</td>';
			row += '<td>' + order.client_contact + '</td>';
			row += '<td>' + order.driver_name + '</td>';
			row += '<td>Ksh ' + order.grand_total + '</td>';
			row += '<td>Ksh ' + order.paid + '</td>';
			row += '<td>Ksh ' + order.due + '</td>';
			row += '<td>' + order.payment_status + '</td>';
			row += '<td>' + order.order_status + '</td>';
			row += '</tr>';
			tbody.append(row);
		});

		// Display summary
		var summaryHtml = '<div class="col-md-3">' +
			'<div class="panel panel-primary">' +
			'<div class="panel-heading">Total Orders</div>' +
			'<div class="panel-body"><h2>' + summary.total_orders + '</h2></div>' +
			'</div></div>' +
			'<div class="col-md-3">' +
			'<div class="panel panel-success">' +
			'<div class="panel-heading">Total Revenue</div>' +
			'<div class="panel-body"><h2>Ksh ' + summary.total_revenue + '</h2></div>' +
			'</div></div>' +
			'<div class="col-md-3">' +
			'<div class="panel panel-info">' +
			'<div class="panel-heading">Total Paid</div>' +
			'<div class="panel-body"><h2>Ksh ' + summary.total_paid + '</h2></div>' +
			'</div></div>' +
			'<div class="col-md-3">' +
			'<div class="panel panel-warning">' +
			'<div class="panel-heading">Total Due</div>' +
			'<div class="panel-body"><h2>Ksh ' + summary.total_due + '</h2></div>' +
			'</div></div>';
		
		$("#reportSummary").html(summaryHtml);
	}

	function exportToCSV() {
		window.location.href = 'php_action/exportReportCSV.php?startDate=' + $("#startDate").val() + 
			'&endDate=' + $("#endDate").val() +
			'&searchClient=' + $("#searchClient").val() +
			'&paymentStatusFilter=' + $("#paymentStatusFilter").val() +
			'&orderStatusFilter=' + $("#orderStatusFilter").val() +
			'&paymentTypeFilter=' + $("#paymentTypeFilter").val();
	}
});