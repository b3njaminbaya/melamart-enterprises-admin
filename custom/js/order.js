/*
 * order.js — New Order, Edit Order and Manage Orders.
 * Totals shown here are a preview; the server recalculates everything.
 */
var manageOrderTable;

$(document).ready(function() {
	var divRequest = $(".div-request").text();

	$("#navOrder").addClass('active');

	if(divRequest === 'add' || divRequest === 'editOrd') {
		$(divRequest === 'add' ? '#topNavAddOrder' : '#topNavManageOrder').addClass('active');
		initOrderForm(window.ORDER_FORM);
	} else if(divRequest === 'manord') {
		$('#topNavManageOrder').addClass('active');
		initManageOrders();
	}

	$('#updatePaymentOrderBtn').on('click', submitPayment);
});

// ─────────────────────────────────────────────────────────
// Order form (add + edit)
// ─────────────────────────────────────────────────────────

var orderForm = { products: {}, productList: [], originalRates: {}, isEdit: false, alreadyPaid: 0, vatRate: 0 };

function initOrderForm(config) {
	orderForm.isEdit = config.isEdit;
	orderForm.productList = config.products;
	orderForm.alreadyPaid = Number(config.alreadyPaid) || 0;
	orderForm.vatRate = Number(config.vatRate) || 0;
	$.each(config.products, function(i, p) { orderForm.products[p.id] = p; });
	$.each(config.items, function(i, item) { orderForm.originalRates[item.product_id] = item.rate; });

	$('#orderDate, #expectReturnDate').datepicker({ onSelect: function() { $(this).trigger('change'); } });
	$('#returnedDate').datepicker({ maxDate: 0, onSelect: function() { $(this).trigger('change'); } });

	var scheduled = scheduledDays();
	if(config.items.length) {
		$.each(config.items, function(i, item) {
			addRow(item, item.rental_days !== scheduled);
		});
	} else {
		addRow(null, false);
	}

	$('#addRowBtn').on('click', function() { addRow(null, false); });

	$('#orderDate, #expectReturnDate').on('change', function() {
		var days = scheduledDays();
		$('#productTable tbody tr').each(function() {
			if(!$(this).data('manualDays')) {
				$(this).find('.rental-days').val(days);
			}
		});
		recalcOrder();
	});

	$('#returnedDate').on('change', function() {
		var hasDate = $.trim($(this).val()) !== '';
		var $status = $('#orderStatus');
		if(hasDate && $status.val() === '0') { $status.val('1'); }
		if(!hasDate && $status.val() === '1') { $status.val('0'); }
		recalcOrder();
	});

	$('#orderStatus').on('change', function() {
		if($(this).val() === '1' && $.trim($('#returnedDate').val()) === '') {
			$('#returnedDate').datepicker('setDate', new Date()).trigger('change');
			showToast('Returned date set to today. Change it if the equipment came back on another day.', 'info');
		}
	});

	$('#discount, #paid').on('input change', recalcOrder);
	$('#orderForm').on('submit', submitOrderForm);

	recalcOrder();
}

function scheduledDays() {
	return hireDays(parseDMY($('#orderDate').val()), parseDMY($('#expectReturnDate').val()));
}

function productOptions(selectedId) {
	var html = '<option value="">— Select product —</option>';
	$.each(orderForm.productList, function(i, p) {
		var label = p.name + ' (' + p.available + ' available)';
		html += '<option value="' + p.id + '"' + (p.id === selectedId ? ' selected' : '') + '>' + escapeHtml(label) + '</option>';
	});
	return html;
}

function addRow(item, manualDays) {
	var days = item ? item.rental_days : scheduledDays();
	var $tr = $('<tr>' +
		'<td data-label="Product"><div class="form-group" style="margin:0;"><select class="form-control product-select" name="productName[]" aria-label="Product">' + productOptions(item ? item.product_id : null) + '</select></div></td>' +
		'<td data-label="Daily rate (KSh)" class="text-right rate-cell">—</td>' +
		'<td data-label="Quantity"><div class="form-group" style="margin:0;"><input type="number" class="form-control quantity" name="quantity[]" min="1" step="1" aria-label="Quantity" value="' + (item ? item.quantity : 1) + '"></div></td>' +
		'<td data-label="Rental days"><div class="form-group" style="margin:0;"><input type="number" class="form-control rental-days" name="rentalDays[]" min="1" step="1" aria-label="Rental days" value="' + days + '"></div></td>' +
		'<td data-label="Available" class="text-right available">—</td>' +
		'<td data-label="Line total (KSh)" class="text-right line-total">0.00</td>' +
		'<td><button type="button" class="btn btn-default btn-sm remove-row" title="Remove this product" aria-label="Remove this product"><i class="glyphicon glyphicon-trash"></i></button></td>' +
		'</tr>');
	$tr.data('manualDays', !!manualDays);

	$tr.find('.product-select').on('change', function() { recalcOrder(); });
	$tr.find('.quantity').on('input change', recalcOrder);
	$tr.find('.rental-days').on('input change', function() {
		$tr.data('manualDays', true);
		recalcOrder();
	});
	$tr.find('.remove-row').on('click', function() {
		if($('#productTable tbody tr').length === 1) {
			$tr.find('.product-select').val('');
			$tr.find('.quantity').val(1);
		} else {
			$tr.remove();
		}
		recalcOrder();
	});

	$('#productTable tbody').append($tr);
	recalcOrder();
}

function rowRate(productId) {
	if(orderForm.originalRates[productId] !== undefined) { return Number(orderForm.originalRates[productId]); }
	var p = orderForm.products[productId];
	return p ? Number(p.rate) : 0;
}

// Recalculates the preview totals (same rules as php_action/order_service.php).
function recalcOrder() {
	var sub = 0;
	var lines = [];
	$('#productTable tbody tr').each(function() {
		var $tr = $(this);
		var pid = Number($tr.find('.product-select').val()) || 0;
		var p = orderForm.products[pid];
		var qty = Math.max(0, parseInt($tr.find('.quantity').val(), 10) || 0);
		var days = Math.max(0, parseInt($tr.find('.rental-days').val(), 10) || 0);
		var rate = pid ? rowRate(pid) : 0;
		var line = Math.round(rate * days * qty * 100) / 100;

		$tr.find('.rate-cell').text(pid ? formatMoney(rate) : '—');
		$tr.find('.available').text(p ? p.available : '—');
		$tr.find('.quantity').attr('max', p ? p.available : null);
		$tr.find('.line-total').text(formatMoney(line));
		sub += line;
		if(pid) { lines.push({ rate: rate, qty: qty, days: days }); }
	});

	var vat = Math.round(sub * orderForm.vatRate) / 100;
	var lateFee = 0;
	var orderDate = parseDMY($('#orderDate').val());
	var returned = parseDMY($('#returnedDate').val());
	if(orderForm.isEdit && orderDate && returned) {
		var actual = hireDays(orderDate, returned);
		$.each(lines, function(i, l) {
			if(actual > l.days) { lateFee += (actual - l.days) * l.rate * l.qty; }
		});
	}
	var discount = Math.max(0, Number($('#discount').val()) || 0);
	var grand = sub + vat + lateFee - discount;
	var paid = orderForm.isEdit ? orderForm.alreadyPaid : Math.max(0, Number($('#paid').val()) || 0);

	$('#subTotal').val(formatMoney(sub));
	$('#vat').val(formatMoney(vat));
	$('#lateFee').val(formatMoney(lateFee));
	$('#grandTotal').val(formatMoney(grand));
	$('#due').val(formatMoney(grand - paid));

	var days = scheduledDays();
	$('#hirePeriodHint').text(parseDMY($('#expectReturnDate').val()) ? days + ' day' + (days === 1 ? '' : 's') + ' hire (both dates included)' : '');

	return { grand: grand, paid: paid, discount: discount, sub: sub + vat + lateFee };
}

var KE_PHONE = /^(?:\+?254|0)?[17]\d{8}$/;

function validPhone(value) {
	return KE_PHONE.test(String(value).replace(/[\s\-().]/g, ''));
}

function validateOrderForm() {
	var $form = $('#orderForm');
	clearFieldErrors($form);
	var ok = true;
	function fail($field, message) { fieldError($field, message); ok = false; }

	var orderDate = parseDMY($('#orderDate').val());
	var expected = parseDMY($('#expectReturnDate').val());
	if(!orderDate) { fail($('#orderDate'), 'Enter the order date as dd/mm/yyyy.'); }
	if(!expected) { fail($('#expectReturnDate'), 'Enter the expected return date as dd/mm/yyyy.'); }
	else if(orderDate && expected < orderDate) { fail($('#expectReturnDate'), 'Cannot be before the order date.'); }

	if(!$('#branch').val()) { fail($('#branch'), 'Select a branch.'); }
	$.each(['#siteLocation', '#clientName', '#driverName'], function(i, sel) {
		if($.trim($(sel).val()) === '') { fail($(sel), 'This field is required.'); }
	});
	$.each(['#clientContact', '#driverContact'], function(i, sel) {
		if(!validPhone($(sel).val())) { fail($(sel), 'Enter a Kenyan phone number, e.g. 0712 345 678.'); }
	});
	var returnedBy = $('#returnedByContact');
	if(returnedBy.length && $.trim(returnedBy.val()) !== '' && !validPhone(returnedBy.val())) {
		fail(returnedBy, 'Enter a Kenyan phone number, e.g. 0712 345 678.');
	}
	var pin = $.trim($('#clientKraPin').val()).toUpperCase();
	if(pin !== '' && !/^[AP]\d{9}[A-Z]$/.test(pin)) { fail($('#clientKraPin'), 'Format: P051234567X'); }

	if($('#returnedDate').length && $.trim($('#returnedDate').val()) !== '') {
		var returned = parseDMY($('#returnedDate').val());
		if(!returned) { fail($('#returnedDate'), 'Enter the date as dd/mm/yyyy.'); }
		else if(orderDate && returned < orderDate) { fail($('#returnedDate'), 'Cannot be before the order date.'); }
	}

	// Equipment rows
	var stillOut = !$('#returnedDate').length || $.trim($('#returnedDate').val()) === '';
	var cancelled = $('#orderStatus').val() === '2';
	var seen = {};
	var productCount = 0;
	$('#productTable tbody tr').each(function() {
		var $tr = $(this);
		var $select = $tr.find('.product-select');
		var pid = Number($select.val()) || 0;
		var qty = parseInt($tr.find('.quantity').val(), 10) || 0;
		var days = parseInt($tr.find('.rental-days').val(), 10) || 0;
		if(!pid) { fail($select, 'Select a product or remove this row.'); return; }
		productCount++;
		if(seen[pid]) { fail($select, 'Already listed above — combine the quantities.'); }
		seen[pid] = true;
		if(qty < 1) { fail($tr.find('.quantity'), 'At least 1.'); }
		else if(stillOut && !cancelled && orderForm.products[pid] && qty > orderForm.products[pid].available) {
			fail($tr.find('.quantity'), 'Only ' + orderForm.products[pid].available + ' available.');
		}
		if(days < 1) { fail($tr.find('.rental-days'), 'At least 1.'); }
	});
	if(productCount === 0 && ok) {
		fail($('#productTable tbody tr:first .product-select'), 'Add at least one product.');
	}

	var totals = recalcOrder();
	if($('#discount').val() !== '' && (isNaN(Number($('#discount').val())) || Number($('#discount').val()) < 0)) {
		fail($('#discount'), 'Enter 0 or more.');
	} else if(totals.discount > totals.sub + 0.001) {
		fail($('#discount'), 'Cannot be more than the order total.');
	}
	if(!orderForm.isEdit) {
		var paid = Number($('#paid').val());
		if(isNaN(paid) || paid < 0) { fail($('#paid'), 'Enter 0 or more.'); }
		else if(paid > totals.grand + 0.001) { fail($('#paid'), 'Cannot be more than the grand total.'); }
		else if(paid > 0 && !$('#paymentType').val()) { fail($('#paymentType'), 'Select how the client paid.'); }
	}
	return ok;
}

function submitOrderForm(e) {
	e.preventDefault();
	var $form = $(this);
	var $btn = $('#saveOrderBtn');
	if($btn.prop('disabled')) { return false; }
	$('.order-messages').empty();

	if(!validateOrderForm()) {
		showToast('Please correct the highlighted fields.', 'warning');
		var $first = $form.find('.has-error:first');
		if($first.length) { $('html, body').animate({ scrollTop: $first.offset().top - 90 }, 200); }
		return false;
	}

	$btn.button('loading');
	$.ajax({
		url: $form.attr('action'),
		type: 'POST',
		data: $form.serialize(),
		dataType: 'json',
		success: function(response) {
			if(response.success) {
				// The next page shows the confirmation message.
				window.location.href = 'orders.php?o=editOrd&i=' + response.order_id;
				return;
			}
			$btn.button('reset');
			showAlert('.order-messages', 'error', response.messages);
			showToast(response.messages, 'error');
			$('html, body').animate({ scrollTop: 0 }, 200);
		}
	});
	return false;
}

// ─────────────────────────────────────────────────────────
// Manage orders
// ─────────────────────────────────────────────────────────

function initManageOrders() {
	var url = function() { return 'php_action/fetchOrder.php?status=' + encodeURIComponent($('#orderStatusFilter').val()); };
	var sortable = function(key) { return { data: { _: key + '.display', sort: key + '.sort' } }; };

	manageOrderTable = $('#manageOrderTable').DataTable({
		ajax: url(),
		order: [],
		pageLength: 25,
		lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
		columns: [
			sortable('id'),
			sortable('date'),
			{ data: 'client' },
			{ data: 'site' },
			sortable('expected'),
			sortable('returned'),
			$.extend(sortable('total'), { className: 'text-right text-nowrap' }),
			$.extend(sortable('paid'), { className: 'text-right text-nowrap' }),
			$.extend(sortable('due'), { className: 'text-right text-nowrap' }),
			{ data: 'payment' },
			{ data: 'status' },
			{ data: 'action', orderable: false, searchable: false }
		]
	});

	$('#orderStatusFilter').on('change', function() {
		manageOrderTable.ajax.url(url()).load();
	});
}

function reloadOrders() {
	if(manageOrderTable) {
		manageOrderTable.ajax.reload(null, false);
	} else {
		window.location.reload();
	}
}

// ─────────────────────────────────────────────────────────
// Print, cancel, payments
// ─────────────────────────────────────────────────────────

function printOrder(orderId) {
	var win = window.open('php_action/printOrder.php?id=' + encodeURIComponent(orderId), '_blank');
	if(!win) {
		showToast('Your browser blocked the invoice window. Allow pop-ups for this site and try again.', 'warning');
	}
}

function removeOrder(orderId) {
	$('.removeOrderMessages').empty();
	$('#removeOrderTitle').text('#' + orderId);
	$('#removeOrderModal').modal('show');

	$('#removeOrderBtn').off('click').on('click', function() {
		var $btn = $(this).button('loading');
		$.ajax({
			url: 'php_action/removeOrder.php',
			type: 'post',
			data: { orderId: orderId },
			dataType: 'json',
			success: function(response) {
				$btn.button('reset');
				if(response.success) {
					$('#removeOrderModal').modal('hide');
					showToast(response.messages, 'success');
					reloadOrders();
				} else {
					showAlert('.removeOrderMessages', 'error', response.messages);
				}
			}
		});
	});
}

var paymentOrderId = null;

function paymentOrder(orderId) {
	paymentOrderId = orderId;
	var $modal = $('#paymentOrderModal');
	$('.paymentOrderMessages, #paymentHistory').empty();
	clearFieldErrors($modal);
	$('#paymentForm')[0].reset();
	$('#paymentOrderTitle').text('Order #' + orderId);
	$('#payGrandTotal, #payPaid, #payDue').text('…');
	$('#paymentForm, #updatePaymentOrderBtn').hide();
	$('#paymentFullyPaid').hide();
	$modal.modal('show');

	$.ajax({
		url: 'php_action/fetchOrderPaymentData.php',
		type: 'post',
		data: { orderId: orderId },
		dataType: 'json',
		success: function(response) {
			if(!response.success) {
				showAlert('.paymentOrderMessages', 'error', response.messages);
				return;
			}
			var order = response.order;
			$('#paymentOrderTitle').text('Order #' + order.order_id + ' · ' + order.client_name);
			$('#payGrandTotal').text('KSh ' + formatMoney(order.grand_total));
			$('#payPaid').text('KSh ' + formatMoney(order.paid));
			$('#payDue').text('KSh ' + formatMoney(order.due));

			if(response.history.length) {
				var rows = '';
				$.each(response.history, function(i, p) {
					rows += '<tr><td>' + escapeHtml(p.date) + '</td><td>' + escapeHtml(p.method) + '</td><td>' + escapeHtml(p.reference) +
						'</td><td class="text-right">' + escapeHtml(p.amount) + '</td><td>' + escapeHtml(p.received_by) + '</td></tr>';
				});
				$('#paymentHistory').html('<h4 class="form-section-title">Payment history</h4><div class="table-responsive"><table class="table table-condensed">' +
					'<thead><tr><th>Date</th><th>Method</th><th>Reference</th><th class="text-right">KSh</th><th>By</th></tr></thead><tbody>' + rows + '</tbody></table></div>');
			} else if(order.paid > 0) {
				$('#paymentHistory').html('<p class="text-muted small">KSh ' + formatMoney(order.paid) + ' was paid before payment history was kept.</p>');
			}

			if(order.due > 0 && order.order_status !== 2) {
				$('#payAmount').val(order.due.toFixed(2)).attr('max', order.due.toFixed(2)).data('due', order.due);
				if(order.payment_type) { $('#payPaymentType').val(order.payment_type); }
				$('#paymentForm, #updatePaymentOrderBtn').show();
			} else if(order.order_status !== 2) {
				$('#paymentFullyPaid').show();
			}
		}
	});
}

function submitPayment() {
	var $modal = $('#paymentOrderModal');
	clearFieldErrors($modal);
	$('.paymentOrderMessages').empty();

	var amount = Number($('#payAmount').val());
	var due = Number($('#payAmount').data('due')) || 0;
	var ok = true;
	if(!amount || amount <= 0) { fieldError($('#payAmount'), 'Enter an amount greater than 0.'); ok = false; }
	else if(amount > due + 0.001) { fieldError($('#payAmount'), 'Cannot be more than the balance (KSh ' + formatMoney(due) + ').'); ok = false; }
	if(!$('#payPaymentType').val()) { fieldError($('#payPaymentType'), 'Select a method.'); ok = false; }
	if(!ok) { return; }

	var $btn = $('#updatePaymentOrderBtn').button('loading');
	$.ajax({
		url: 'php_action/editPayment.php',
		type: 'post',
		data: {
			orderId: paymentOrderId,
			payAmount: amount.toFixed(2),
			paymentType: $('#payPaymentType').val(),
			paymentReference: $('#payReference').val()
		},
		dataType: 'json',
		success: function(response) {
			$btn.button('reset');
			if(response.success) {
				$modal.modal('hide');
				showToast(response.messages, 'success');
				reloadOrders();
			} else {
				showAlert('.paymentOrderMessages', 'error', response.messages);
			}
		}
	});
}
