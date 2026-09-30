var manageUserTable;

$(document).ready(function() {
	$('#navSetting, #topNavUser').addClass('active');

	manageUserTable = $('#manageUserTable').DataTable({
		ajax: 'php_action/fetchUser.php',
		order: [],
		columns: [null, null, { className: 'text-right' }, { orderable: false, searchable: false }]
	});

	$('#addUserModalBtn').on('click', function() {
		var $form = $('#submitUserForm');
		$form[0].reset();
		clearFieldErrors($form);
		$('#add-user-messages').empty();
		$('#addUserModal').modal('show');
	});

	$('#submitUserForm').on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		clearFieldErrors($form);
		$('#add-user-messages').empty();

		var ok = true;
		if($.trim($('#userName').val()).length < 3) { fieldError($('#userName'), 'At least 3 characters.'); ok = false; }
		if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($.trim($('#uemail').val()))) { fieldError($('#uemail'), 'Enter a valid email address.'); ok = false; }
		if($('#upassword').val().length < 8) { fieldError($('#upassword'), 'At least 8 characters.'); ok = false; }
		if(!ok) { return false; }

		var $btn = $('#createUserBtn').button('loading');
		$.ajax({
			url: $form.attr('action'),
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			success: function(response) {
				$btn.button('reset');
				if(response.success) {
					$('#addUserModal').modal('hide');
					showToast(response.messages, 'success');
					manageUserTable.ajax.reload(null, false);
				} else {
					showAlert('#add-user-messages', 'error', response.messages);
				}
			}
		});
		return false;
	});

	$('#editUserForm').on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		clearFieldErrors($form);
		$('#edit-user-messages').empty();

		var ok = true;
		if($.trim($('#edituserName').val()).length < 3) { fieldError($('#edituserName'), 'At least 3 characters.'); ok = false; }
		if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($.trim($('#editEmail').val()))) { fieldError($('#editEmail'), 'Enter a valid email address.'); ok = false; }
		var pw = $('#editPassword').val();
		if(pw !== '' && pw.length < 8) { fieldError($('#editPassword'), 'At least 8 characters, or leave blank.'); ok = false; }
		if(!ok) { return false; }

		var $btn = $('#editUserBtn').button('loading');
		$.ajax({
			url: $form.attr('action'),
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			success: function(response) {
				$btn.button('reset');
				if(response.success) {
					$('#editUserModal').modal('hide');
					showToast(response.messages, 'success');
					manageUserTable.ajax.reload(null, false);
				} else {
					showAlert('#edit-user-messages', 'error', response.messages);
				}
			}
		});
		return false;
	});
});

function editUser(userid) {
	$.ajax({
		url: 'php_action/fetchSelectedUser.php',
		type: 'post',
		data: { userid: userid },
		dataType: 'json',
		success: function(response) {
			var $form = $('#editUserForm');
			$form[0].reset();
			clearFieldErrors($form);
			$('#edit-user-messages').empty();
			$('#userid').val(response.user_id);
			$('#edituserName').val(response.username);
			$('#editEmail').val(response.email);
			$('#editUserModal').modal('show');
		}
	});
}

function removeUser(userid) {
	var row = manageUserTable.rows().data().toArray().filter(function(r) { return String(r[3]).indexOf('editUser(' + userid + ')') !== -1; })[0];
	$('#removeUserName').html(row ? row[0] : 'this user');
	$('.removeUserMessages').empty();
	$('#removeUserModal').modal('show');

	$('#removeUserBtn').off('click').on('click', function() {
		var $btn = $(this).button('loading');
		$.ajax({
			url: 'php_action/removeUser.php',
			type: 'post',
			data: { userid: userid },
			dataType: 'json',
			success: function(response) {
				$btn.button('reset');
				if(response.success) {
					$('#removeUserModal').modal('hide');
					showToast(response.messages, 'success');
					manageUserTable.ajax.reload(null, false);
				} else {
					showAlert('.removeUserMessages', 'error', response.messages);
				}
			}
		});
	});
}
