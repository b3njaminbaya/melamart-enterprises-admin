/*
 * app.js — shared front-end helpers for every admin page.
 * Loaded from includes/header.php after jQuery, jQuery UI and Bootstrap.
 */

// Escape text before inserting it into HTML.
function escapeHtml(value) {
	return String(value === null || value === undefined ? '' : value)
		.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

// 1234.5 → "1,234.50"
function formatMoney(value) {
	var n = Number(value) || 0;
	return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// "25/12/2026" → Date (or null)
function parseDMY(value) {
	var m = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/.exec($.trim(value || ''));
	if(!m) { return null; }
	var d = new Date(Number(m[3]), Number(m[2]) - 1, Number(m[1]));
	return (d.getMonth() === Number(m[2]) - 1) ? d : null;
}

// Days between two dates, counting both ends (matches the server's hire_days()).
function hireDays(from, to) {
	if(!from || !to || to < from) { return 1; }
	return Math.round((to - from) / 86400000) + 1;
}

// Toast notification. type: success | error | warning | info
function showToast(message, type, duration) {
	var cls = { success: 'alert-success', error: 'alert-danger', warning: 'alert-warning', info: 'alert-info' }[type] || 'alert-info';
	var $container = $('#toast-container');
	if(!$container.length) {
		$container = $('<div id="toast-container" role="status" aria-live="polite"></div>').appendTo('body');
	}
	var $toast = $('<div class="alert toast-notification ' + cls + '">' +
		'<button type="button" class="close" aria-label="Close">&times;</button>' +
		escapeHtml(message) + '</div>');
	$toast.find('.close').on('click', function() { $toast.remove(); });
	$container.append($toast);
	setTimeout(function() { $toast.fadeOut(300, function() { $toast.remove(); }); }, duration || (type === 'error' ? 8000 : 5000));
}

// Inline alert inside a form/modal. Replaces whatever was there.
function showAlert(target, type, message) {
	var cls = { success: 'alert-success', error: 'alert-danger', warning: 'alert-warning', info: 'alert-info' }[type] || 'alert-info';
	var icon = type === 'success' ? 'glyphicon-ok-sign' : 'glyphicon-exclamation-sign';
	$(target).html('<div class="alert ' + cls + '">' +
		'<button type="button" class="close" data-dismiss="alert">&times;</button>' +
		'<i class="glyphicon ' + icon + '"></i> ' + escapeHtml(message) + '</div>');
}

// Field-level validation message (Bootstrap 3 markup).
function fieldError($field, message) {
	var $group = $field.closest('.form-group');
	$group.addClass('has-error');
	var $anchor = $field.closest('.input-group').length ? $field.closest('.input-group') : $field;
	$anchor.after('<p class="text-danger">' + escapeHtml(message) + '</p>');
}

function clearFieldErrors($scope) {
	$scope = $scope || $(document);
	$scope.find('.text-danger').remove();
	$scope.find('.form-group').removeClass('has-error has-success');
}

// Reads the message out of a failed JSON response.
function responseMessage(xhr, fallback) {
	try {
		var data = JSON.parse(xhr.responseText);
		if(data && data.messages) { return data.messages; }
	} catch(e) {}
	return fallback;
}

$(function() {
	var csrfToken = $('meta[name="csrf-token"]').attr('content');

	// Attach the CSRF token to every jQuery POST.
	$.ajaxSetup({
		beforeSend: function(xhr, settings) {
			if(String(settings.type).toUpperCase() === 'POST') {
				if(typeof settings.data === 'string') {
					settings.data += (settings.data ? '&' : '') + 'csrf_token=' + encodeURIComponent(csrfToken);
				} else if(window.FormData && settings.data instanceof FormData) {
					settings.data.append('csrf_token', csrfToken);
				} else if(!settings.data) {
					settings.data = 'csrf_token=' + encodeURIComponent(csrfToken);
				}
			}
		}
	});

	// One place that handles every failed request: resets "Loading…" buttons
	// and tells the user what happened instead of failing silently.
	$(document).ajaxError(function(event, xhr, settings, thrown) {
		if(thrown === 'abort') { return; }

		$('.btn.disabled, .btn:disabled').each(function() {
			if($(this).data('resetText') !== undefined) { $(this).button('reset'); }
		});
		$('button[data-busy="1"]').prop('disabled', false).removeAttr('data-busy');

		if(xhr.status === 401) {
			showToast('Your session has expired. Taking you to the login page…', 'warning');
			setTimeout(function() { window.location.href = 'index.php'; }, 1800);
			return;
		}
		var fallback = xhr.status === 0
			? 'Could not reach the server. Check your connection and try again.'
			: (xhr.status === 403 ? 'You are not allowed to do this, or the page is out of date. Refresh and try again.'
			: 'Something went wrong. Please try again.');
		showToast(responseMessage(xhr, fallback), 'error');
	});

	// Date pickers: Kenyan format (dd/mm/yyyy), weeks start on Monday.
	if($.datepicker) {
		$.datepicker.setDefaults({
			dateFormat: 'dd/mm/yy',
			firstDay: 1,
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true,
			showButtonPanel: true,
			currentText: 'Today',
			closeText: 'Close',
			showAnim: ''
		});
		// jQuery UI's "Today" only moves the calendar; make it pick today's date.
		var gotoToday = $.datepicker._gotoToday;
		$.datepicker._gotoToday = function(id) {
			gotoToday.call(this, id);
			this._selectDate(id);
		};
	}

	// DataTables: route load errors through the handler above instead of alert().
	if($.fn.dataTable) {
		$.fn.dataTable.ext.errMode = 'none';
		$.extend(true, $.fn.dataTable.defaults, {
			language: {
				emptyTable: 'Nothing here yet.',
				zeroRecords: 'No matching records found.',
				search: 'Search:'
			}
		});
	}

	// Clear a field's validation message as soon as the user corrects it.
	$(document).on('input change', '.has-error input, .has-error select, .has-error textarea', function() {
		var $group = $(this).closest('.form-group');
		$group.removeClass('has-error').find('p.text-danger').remove();
	});

	// Action dropdowns inside scrollable tables must not be clipped.
	$(document).on('show.bs.dropdown', '.table-responsive', function() {
		$(this).css('overflow', 'visible');
	}).on('hide.bs.dropdown', '.table-responsive', function() {
		$(this).css('overflow', '');
	});
});
