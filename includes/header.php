<?php require_once __DIR__ . '/../php_action/core.php'; ?>
<?php
$navUserStmt = $connect->prepare("SELECT username FROM users WHERE user_id = ?");
$navUserId = current_user_id();
$navUserStmt->bind_param('i', $navUserId);
$navUserStmt->execute();
$navUser = $navUserStmt->get_result()->fetch_assoc();
$navUserStmt->close();
$navUsername = $navUser ? $navUser['username'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Melamart Admin — Enterprises Limited</title>
  <link rel="shortcut icon" href="images/logo.jpeg">

  <!-- CSRF token — attached to every AJAX POST by custom/js/app.js -->
  <meta name="csrf-token" content="<?php echo h(csrf_token()); ?>">

  <!-- Melamart brand fonts — Montserrat (headings) + Open Sans (body) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- 1. Bootstrap 3 (base) -->
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <!-- 2. Font Awesome icons -->
  <link rel="stylesheet" href="assets/font-awesome/css/font-awesome.min.css">
  <!-- 3. DataTables -->
  <link rel="stylesheet" href="assets/plugins/datatables/jquery.dataTables.min.css">
  <!-- 4. File input plugin -->
  <link rel="stylesheet" href="assets/plugins/fileinput/css/fileinput.min.css">
  <!-- 5. jQuery UI -->
  <link rel="stylesheet" href="assets/jquery-ui/jquery-ui.min.css">

  <!-- 6. Melamart brand theme — MUST load after Bootstrap so it overrides correctly -->
  <link rel="stylesheet" href="custom/css/melamart-theme.css">
  <!-- 7. Page-specific utilities -->
  <link rel="stylesheet" href="custom/css/custom.css">

  <!-- JS: jQuery first, then plugins -->
  <script src="assets/jquery/jquery.min.js"></script>
  <script src="assets/jquery-ui/jquery-ui.min.js"></script>
  <script src="assets/bootstrap/js/bootstrap.min.js"></script>
  <!-- File input plugin -->
  <script src="assets/plugins/fileinput/js/plugins/canvas-to-blob.min.js"></script>
  <script src="assets/plugins/fileinput/js/plugins/sortable.min.js"></script>
  <script src="assets/plugins/fileinput/js/plugins/purify.min.js"></script>
  <script src="assets/plugins/fileinput/js/fileinput.min.js"></script>
  <!-- DataTables -->
  <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
  <!-- Shared helpers: CSRF, toasts, error handling, date picker defaults -->
  <script src="custom/js/app.js"></script>

  <?php if(is_admin()) { ?>
  <!-- Overdue badge counter — admin only -->
  <script src="custom/js/overdueBadge.js"></script>
  <?php } ?>
</head>
<body>


	<nav class="navbar navbar-default navbar-static-top">
		<div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header" >
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-navbar-collapse" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="dashboard.php">
          <img src="images/logo.jpeg" alt="Melamart Enterprises Limited">
      </a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="main-navbar-collapse">

      <ul class="nav navbar-nav navbar-right">

      	<li id="navDashboard"><a href="dashboard.php"><i class="glyphicon glyphicon-home"></i>  Dashboard</a></li>
        <?php if(is_admin()) { ?>
        <li id="navBrand"><a href="brand.php"><i class="glyphicon glyphicon-bookmark"></i>  Brands</a></li>
        <li id="navCategories"><a href="categories.php"> <i class="glyphicon glyphicon-th-list"></i> Categories</a></li>
        <li id="navProduct"><a href="product.php"> <i class="glyphicon glyphicon-wrench"></i> Equipment </a></li>
		<?php } ?>

        <li class="dropdown" id="navOrder">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-shopping-cart"></i> Orders <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li id="topNavAddOrder"><a href="orders.php?o=add"> <i class="glyphicon glyphicon-plus"></i> New Order</a></li>
            <li id="topNavManageOrder"><a href="orders.php?o=manord"> <i class="glyphicon glyphicon-list"></i> Manage Orders</a></li>
          </ul>
        </li>
        <?php if(is_admin()) { ?>
        <li id="navOverdue"><a href="overdue_reminders.php"> <i class="glyphicon glyphicon-exclamation-sign"></i> Overdue <span id="overdue-badge" class="badge" style="display:none;"></span></a></li>
        <li id="navReport"><a href="report.php"> <i class="glyphicon glyphicon-stats"></i> Reports </a></li>
        <?php } ?>
        <li class="dropdown" id="navSetting">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-user"></i> <?php echo h($navUsername); ?> <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li id="topNavSetting"><a href="setting.php"> <i class="glyphicon glyphicon-cog"></i> My Account</a></li>
            <?php if(is_admin()) { ?>
            <li id="topNavUser"><a href="user.php"> <i class="glyphicon glyphicon-user"></i> Manage Users</a></li>
            <?php } ?>
            <li role="separator" class="divider"></li>
            <li id="topNavLogout"><a href="logout.php"> <i class="glyphicon glyphicon-log-out"></i> Logout</a></li>
          </ul>
        </li>

      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
	</nav>

	<div class="container">

