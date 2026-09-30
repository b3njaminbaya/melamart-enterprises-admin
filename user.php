<?php require_once 'includes/header.php'; ?>
<?php require_admin(); ?>

<div class="row">
	<div class="col-md-12">

		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Home</a></li>
		  <li class="active">Users</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-user"></i> Manage Users</div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">

				<div class="div-action pull pull-right" style="padding-bottom:20px;">
					<button class="btn btn-primary" id="addUserModalBtn"> <i class="glyphicon glyphicon-plus-sign"></i> Add User </button>
				</div> <!-- /div-action -->

				<table class="table" id="manageUserTable" style="width:100%;">
					<thead>
						<tr>
							<th>Username</th>
							<th>Email</th>
							<th class="text-right">Orders created</th>
							<th style="width:90px;"></th>
						</tr>
					</thead>
				</table>

			</div> <!-- /panel-body -->
		</div> <!-- /panel -->
	</div> <!-- /col-md-12 -->
</div> <!-- /row -->


<!-- add user -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    	<form class="form-horizontal" id="submitUserForm" action="php_action/createUser.php" method="POST" novalidate autocomplete="off">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title"><i class="fa fa-plus"></i> Add User</h4>
	      </div>
	      <div class="modal-body">
	      	<div id="add-user-messages"></div>

	        <div class="form-group">
	        	<label for="userName" class="col-sm-3 control-label">Username *</label>
			    <div class="col-sm-9">
			      <input type="text" class="form-control" id="userName" name="userName" autocomplete="off">
			    </div>
	        </div>
	        <div class="form-group">
	        	<label for="uemail" class="col-sm-3 control-label">Email *</label>
			    <div class="col-sm-9">
			      <input type="email" class="form-control" id="uemail" name="uemail" autocomplete="off">
			    </div>
	        </div>
	        <div class="form-group">
	        	<label for="upassword" class="col-sm-3 control-label">Password *</label>
			    <div class="col-sm-9">
			      <input type="password" class="form-control" id="upassword" name="upassword" autocomplete="new-password">
			      <p class="help-block small">At least 8 characters. Share it with the user privately; they can change it under My Account.</p>
			    </div>
	        </div>
	      </div> <!-- /modal-body -->

	      <div class="modal-footer">
	        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
	        <button type="submit" class="btn btn-primary" id="createUserBtn" data-loading-text="Saving…"> <i class="glyphicon glyphicon-ok-sign"></i> Add user</button>
	      </div>
     	</form>
    </div>
  </div>
</div>

<!-- edit user -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    	<form class="form-horizontal" id="editUserForm" action="php_action/editUser.php" method="POST" novalidate autocomplete="off">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title"><i class="fa fa-edit"></i> Edit User</h4>
	      </div>
	      <div class="modal-body">
	      	<div id="edit-user-messages"></div>
	      	<input type="hidden" name="userid" id="userid">

	    	<div class="form-group">
        		<label for="edituserName" class="col-sm-3 control-label">Username *</label>
			    <div class="col-sm-9">
			      <input type="text" class="form-control" id="edituserName" name="edituserName" autocomplete="off">
			    </div>
        	</div>
	    	<div class="form-group">
        		<label for="editEmail" class="col-sm-3 control-label">Email *</label>
			    <div class="col-sm-9">
			      <input type="email" class="form-control" id="editEmail" name="editEmail" autocomplete="off">
			    </div>
        	</div>
	        <div class="form-group">
	        	<label for="editPassword" class="col-sm-3 control-label">New password</label>
			    <div class="col-sm-9">
			      <input type="password" class="form-control" id="editPassword" name="editPassword" placeholder="Leave blank to keep the current password" autocomplete="new-password">
			    </div>
	        </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
	        <button type="submit" class="btn btn-primary" id="editUserBtn" data-loading-text="Saving…"> <i class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
	      </div>
        </form>
    </div>
  </div>
</div>

<!-- remove user -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeUserModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove user</h4>
      </div>
      <div class="modal-body">
      	<div class="removeUserMessages"></div>
        <p>Remove <strong id="removeUserName"></strong>? They will no longer be able to log in. Orders they created are kept.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Keep user</button>
        <button type="button" class="btn btn-danger" id="removeUserBtn" data-loading-text="Removing…"> <i class="glyphicon glyphicon-trash"></i> Remove</button>
      </div>
    </div>
  </div>
</div>

<script src="custom/js/user.js"></script>

<?php require_once 'includes/footer.php'; ?>
