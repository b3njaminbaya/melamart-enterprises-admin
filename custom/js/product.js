var manageProductTable;

var photoInputOptions = {
	overwriteInitial: true,
	maxFileSize: 2500,
	showClose: false,
	showCaption: false,
	showUpload: false,
	browseLabel: ' Choose photo',
	removeLabel: '',
	browseIcon: '<i class="glyphicon glyphicon-folder-open"></i>',
	removeIcon: '<i class="glyphicon glyphicon-remove"></i>',
	removeTitle: 'Clear',
	msgErrorClass: 'alert alert-block alert-danger',
	layoutTemplates: { main2: '{preview} {remove} {browse}' },
	allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif']
};

$(document).ready(function() {
	$('#navProduct').addClass('active');

	manageProductTable = $('#manageProductTable').DataTable({
		ajax: 'php_action/fetchProduct.php',
		order: [],
		columns: [
			{ orderable: false, searchable: false },
			null,
			{ data: { _: '2.display', sort: '2.sort' }, className: 'text-right' },
			{ data: { _: '3.display', sort: '3.sort' }, className: 'text-right' },
			{ className: 'text-right' },
			null,
			null,
			null,
			{ orderable: false, searchable: false }
		]
	});

	// File inputs are initialised once (re-initialising on every open broke them).
	$('#productImage').fileinput($.extend({}, photoInputOptions, {
		elErrorContainer: '#kv-avatar-errors-1',
		defaultPreviewContent: '<img src="assets/images/photo_default.png" alt="" style="width:100%;">'
	}));
	$('#editProductImage').fileinput($.extend({}, photoInputOptions, {
		elErrorContainer: '#kv-avatar-errors-2'
	}));

	$('#addProductModalBtn').on('click', function() {
		var $form = $('#submitProductForm');
		$form[0].reset();
		$('#productImage').fileinput('clear');
		$('#add-product-messages').empty();
		clearFieldErrors($form);
		$('#addProductModal').modal('show');
	});

	$('#submitProductForm').on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		if(!validateProductForm($form, '')) { return false; }

		var $btn = $('#createProductBtn').button('loading');
		$.ajax({
			url: $form.attr('action'),
			type: 'POST',
			data: new FormData(this),
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
			success: function(response) {
				$btn.button('reset');
				if(response.success) {
					$('#addProductModal').modal('hide');
					showToast(response.messages, 'success');
					manageProductTable.ajax.reload(null, false);
				} else {
					showAlert('#add-product-messages', 'error', response.messages);
				}
			}
		});
		return false;
	});

	$('#editProductForm').on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		if(!validateProductForm($form, 'edit')) { return false; }

		var $btn = $('#editProductBtn').button('loading');
		$.ajax({
			url: $form.attr('action'),
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			success: function(response) {
				$btn.button('reset');
				if(response.success) {
					$('#editProductModal').modal('hide');
					showToast(response.messages, 'success');
					manageProductTable.ajax.reload(null, false);
				} else {
					showAlert('#edit-product-messages', 'error', response.messages);
				}
			}
		});
		return false;
	});

	$('#updateProductImageForm').on('submit', function(e) {
		e.preventDefault();
		$('#edit-productPhoto-messages').empty();
		if(!$('#editProductImage').val()) {
			showAlert('#edit-productPhoto-messages', 'warning', 'Choose a photo first.');
			return false;
		}
		var $btn = $('#editProductImageBtn').button('loading');
		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			data: new FormData(this),
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
			success: function(response) {
				$btn.button('reset');
				if(response.success) {
					$('#getProductImage').attr('src', response.image_url);
					$('#editProductImage').fileinput('clear');
					showAlert('#edit-productPhoto-messages', 'success', response.messages);
					manageProductTable.ajax.reload(null, false);
				} else {
					showAlert('#edit-productPhoto-messages', 'error', response.messages);
				}
			}
		});
		return false;
	});
});

// prefix '' (add form) or 'edit' (edit form)
function validateProductForm($form, prefix) {
	var id = function(name) { return '#' + (prefix ? prefix + name.charAt(0).toUpperCase() + name.slice(1) : name); };
	clearFieldErrors($form);
	var ok = true;
	function fail(sel, msg) { fieldError($(sel), msg); ok = false; }

	if($.trim($(id('productName')).val()) === '') { fail(id('productName'), 'Enter the product name.'); }
	var rate = Number($(id('dailyRate')).val());
	if(!rate || rate <= 0) { fail(id('dailyRate'), 'Enter the daily hire rate (more than 0).'); }
	var qty = $(id('quantity')).val();
	if(!/^\d+$/.test(qty)) { fail(id('quantity'), 'Enter a whole number (0 or more).'); }
	var cost = $(id('rate')).val();
	if(cost !== '' && (isNaN(Number(cost)) || Number(cost) < 0)) { fail(id('rate'), 'Enter 0 or more.'); }
	if(!$(id('brandName')).val()) { fail(id('brandName'), 'Select a brand.'); }
	if(!$(id('categoryName')).val()) { fail(id('categoryName'), 'Select a category.'); }
	return ok;
}

function editProduct(productId) {
	var $modal = $('#editProductModal');
	clearFieldErrors($modal);
	$('#edit-product-messages, #edit-productPhoto-messages').empty();
	$('.div-loading').removeClass('div-hide');
	$('.div-result').addClass('div-hide');
	$modal.find('.nav-tabs a[href="#productInfo"]').tab('show');
	$('#editProductImage').fileinput('clear');
	$modal.modal('show');

	$.ajax({
		url: 'php_action/fetchSelectedProduct.php',
		type: 'post',
		data: { productId: productId },
		dataType: 'json',
		success: function(response) {
			$('.div-loading').addClass('div-hide');
			$('.div-result').removeClass('div-hide');

			$('#editProductId, #editPhotoProductId').val(response.product_id);
			$('#getProductImage').attr('src', String(response.product_image || '').replace(/^\.\.\//, '') || 'assets/images/photo_default.png');
			$('#editProductName').val(response.product_name);
			$('#editQuantity').val(response.quantity);
			$('#editRate').val(response.rate);
			$('#editDailyRate').val(response.daily_rate);
			$('#editBrandName').val(String(response.brand_id));
			$('#editCategoryName').val(String(response.categories_id));
			$('#editProductStatus').val(String(response.active === 1 ? 1 : 2));

			if(!$('#editBrandName').val()) {
				showAlert('#edit-product-messages', 'warning', 'This product\'s brand has been removed or made unavailable. Pick another brand.');
			} else if(!$('#editCategoryName').val()) {
				showAlert('#edit-product-messages', 'warning', 'This product\'s category has been removed or made unavailable. Pick another category.');
			}
		},
		error: function() {
			$modal.modal('hide');
		}
	});
}

function removeProduct(productId) {
	$('.removeProductMessages').empty();
	var rowData = manageProductTable.rows().data().toArray().filter(function(r) { return String(r[8]).indexOf('editProduct(' + productId + ')') !== -1; })[0];
	$('#removeProductName').html(rowData ? rowData[1] : 'this product');
	$('#removeProductModal').modal('show');

	$('#removeProductBtn').off('click').on('click', function() {
		var $btn = $(this).button('loading');
		$.ajax({
			url: 'php_action/removeProduct.php',
			type: 'post',
			data: { productId: productId },
			dataType: 'json',
			success: function(response) {
				$btn.button('reset');
				if(response.success) {
					$('#removeProductModal').modal('hide');
					showToast(response.messages, 'success');
					manageProductTable.ajax.reload(null, false);
				} else {
					showAlert('.removeProductMessages', 'error', response.messages);
				}
			}
		});
	});
}
