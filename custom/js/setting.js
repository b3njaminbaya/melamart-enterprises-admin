$(document).ready(function() {
	$("#navSetting, #topNavSetting").addClass('active');

	$("#changeUsernameForm").on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		clearFieldErrors($form);
		$('.changeUsernameMessages').empty();

		if($.trim($("#username").val()).length < 3) {
			fieldError($("#username"), 'At least 3 characters.');
			return false;
		}

		var $btn = $("#changeUsernameBtn").button('loading');
		$.ajax({
			url: $form.attr('action'),
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			success: function(response) {
				$btn.button('reset');
				showAlert('.changeUsernameMessages', response.success ? 'success' : 'error', response.messages);
			}
		});
		return false;
	});

	$("#changePasswordForm").on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		clearFieldErrors($form);
		$('.changePasswordMessages').empty();

		var ok = true;
		if($("#password").val() === '') { fieldError($("#password"), 'Enter your current password.'); ok = false; }
		if($("#npassword").val().length < 8) { fieldError($("#npassword"), 'At least 8 characters.'); ok = false; }
		if($("#cpassword").val() !== $("#npassword").val()) { fieldError($("#cpassword"), 'Does not match the new password.'); ok = false; }
		if(!ok) { return false; }

		var $btn = $("#changePasswordBtn").button('loading');
		$.ajax({
			url: $form.attr('action'),
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			success: function(response) {
				$btn.button('reset');
				showAlert('.changePasswordMessages', response.success ? 'success' : 'error', response.messages);
				if(response.success) { $form[0].reset(); }
			}
		});
		return false;
	});
});
