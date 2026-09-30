<?php require_once 'includes/header.php'; ?>

<?php
$userId = current_user_id();
$stmt = $connect->prepare("SELECT username, email FROM users WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<div class="row">
	<div class="col-md-12">
		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Home</a></li>
		  <li class="active">My Account</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-cog"></i> My Account</div>
			</div> <!-- /panel-heading -->

			<div class="panel-body">

				<form action="php_action/changeUsername.php" method="post" class="form-horizontal" id="changeUsernameForm" novalidate>
					<fieldset>
						<legend>Username</legend>

						<div class="changeUsernameMessages"></div>

						<div class="form-group">
					    <label for="username" class="col-sm-2 control-label">Username</label>
					    <div class="col-sm-10">
					      <input type="text" class="form-control" id="username" name="username" autocomplete="username" value="<?php echo h($result['username'] ?? ''); ?>"/>
					    </div>
					  </div>

					  <div class="form-group">
					    <div class="col-sm-offset-2 col-sm-10">
					      <button type="submit" class="btn btn-primary" data-loading-text="Saving…" id="changeUsernameBtn"> <i class="glyphicon glyphicon-ok-sign"></i> Save username </button>
					    </div>
					  </div>
					</fieldset>
				</form>

				<form action="php_action/changePassword.php" method="post" class="form-horizontal" id="changePasswordForm" novalidate>
					<fieldset>
						<legend>Password</legend>

						<div class="changePasswordMessages"></div>

						<div class="form-group">
					    <label for="password" class="col-sm-2 control-label">Current password</label>
					    <div class="col-sm-10">
					      <input type="password" class="form-control" id="password" name="password" autocomplete="current-password">
					    </div>
					  </div>

					  <div class="form-group">
					    <label for="npassword" class="col-sm-2 control-label">New password</label>
					    <div class="col-sm-10">
					      <input type="password" class="form-control" id="npassword" name="npassword" autocomplete="new-password">
					      <p class="help-block small">At least 8 characters.</p>
					    </div>
					  </div>

					  <div class="form-group">
					    <label for="cpassword" class="col-sm-2 control-label">Confirm new password</label>
					    <div class="col-sm-10">
					      <input type="password" class="form-control" id="cpassword" name="cpassword" autocomplete="new-password">
					    </div>
					  </div>

					  <div class="form-group">
					    <div class="col-sm-offset-2 col-sm-10">
					      <button type="submit" class="btn btn-primary" data-loading-text="Saving…" id="changePasswordBtn"> <i class="glyphicon glyphicon-ok-sign"></i> Change password </button>
					    </div>
					  </div>
					</fieldset>
				</form>

			</div> <!-- /panel-body -->
		</div> <!-- /panel -->
	</div> <!-- /col-md-12 -->
</div> <!-- /row-->


<script src="custom/js/setting.js"></script>
<?php require_once 'includes/footer.php'; ?>
