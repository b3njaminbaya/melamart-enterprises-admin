/*
 * simpleCrud.js — Brands and Categories pages (name + status lists).
 * Configured by the page through window.CRUD.
 */
(function(cfg) {
	var table;
	var $modal = $('#' + cfg.prefix + 'Modal');
	var $form = $('#' + cfg.prefix + 'Form');
	var $name = $('#' + cfg.prefix + 'Name');
	var $status = $('#' + cfg.prefix + 'Status');
	var $id = $('#' + cfg.prefix + 'Id');
	var $messages = $('#' + cfg.prefix + '-messages');

	function openModal(title) {
		$form[0].reset();
		clearFieldErrors($form);
		$messages.empty();
		$('#' + cfg.prefix + 'ModalTitle').text(title);
		$modal.modal('show');
	}

	$(document).ready(function() {
		$(cfg.nav).addClass('active');

		table = $('#manage' + cfg.label + 'Table').DataTable({
			ajax: cfg.fetchUrl,
			order: [],
			columns: [null, { className: 'text-right' }, null, { orderable: false, searchable: false }]
		});

		$('#add' + cfg.label + 'Btn').on('click', function() {
			$id.val('');
			openModal('Add ' + cfg.label.toLowerCase());
			setTimeout(function() { $name.trigger('focus'); }, 300);
		});

		$form.on('submit', function(e) {
			e.preventDefault();
			clearFieldErrors($form);
			$messages.empty();
			if($.trim($name.val()) === '') {
				fieldError($name, 'Enter a name.');
				return false;
			}

			var isEdit = $id.val() !== '';
			var fields = isEdit ? cfg.editFields : cfg.createFields;
			var data = {};
			data[fields.name] = $.trim($name.val());
			data[fields.status] = $status.val();
			if(isEdit) { data[fields.id] = $id.val(); }

			var $btn = $('#save' + cfg.label + 'Btn').button('loading');
			$.ajax({
				url: isEdit ? cfg.editUrl : cfg.createUrl,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(response) {
					$btn.button('reset');
					if(response.success) {
						$modal.modal('hide');
						showToast(response.messages, 'success');
						table.ajax.reload(null, false);
					} else {
						showAlert($messages, 'error', response.messages);
					}
				}
			});
			return false;
		});
	});

	window['edit' + cfg.label] = function(id) {
		var data = {};
		data[cfg.fetchOneParam] = id;
		$.ajax({
			url: cfg.fetchOneUrl,
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function(response) {
				if(!response.success) { showToast(response.messages, 'error'); return; }
				openModal('Edit ' + cfg.label.toLowerCase());
				$id.val(response.id);
				$name.val(response.name);
				$status.val(String(response.active === 1 ? 1 : 2));
			}
		});
	};

	window['remove' + cfg.label] = function(id) {
		var row = table.rows().data().toArray().filter(function(r) { return String(r[3]).indexOf('(' + id + ')') !== -1; })[0];
		$('#remove' + cfg.label + 'Name').html(row ? row[0] : '');
		$('.remove' + cfg.label + 'Messages').empty();
		$('#remove' + cfg.label + 'Modal').modal('show');

		$('#remove' + cfg.label + 'Btn').off('click').on('click', function() {
			var $btn = $(this).button('loading');
			var data = {};
			data[cfg.removeParam] = id;
			$.ajax({
				url: cfg.removeUrl,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(response) {
					$btn.button('reset');
					if(response.success) {
						$('#remove' + cfg.label + 'Modal').modal('hide');
						showToast(response.messages, 'success');
						table.ajax.reload(null, false);
					} else {
						showAlert('.remove' + cfg.label + 'Messages', 'error', response.messages);
					}
				}
			});
		});
	};
})(window.CRUD);
