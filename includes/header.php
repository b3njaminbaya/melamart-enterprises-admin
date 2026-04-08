<?php require_once 'php_action/core.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Melamart Admin — Enterprises Limited</title>
  <link rel="shortcut icon" href="images/logo.jpeg">

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

  <!-- Overdue badge counter — admin user only -->
  <?php if(isset($_SESSION['userId']) && $_SESSION['userId'] === 1) { ?>
  <script src="custom/js/overdueBadge.js"></script>
  <?php } ?>

  <!-- CSRF token — available to all AJAX calls via $.ajaxSetup below -->
  <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

  <script>
    // Automatically attach CSRF token to every jQuery AJAX POST request.
    // This means individual JS files don't need to be modified.
    $(document).ready(function() {
      var csrfToken = $('meta[name="csrf-token"]').attr('content');
      $.ajaxSetup({
        beforeSend: function(xhr, settings) {
          if(settings.type === 'POST' || settings.type === 'post') {
            if(typeof settings.data === 'string') {
              settings.data += (settings.data ? '&' : '') + 'csrf_token=' + encodeURIComponent(csrfToken);
            } else if(settings.data instanceof FormData) {
              settings.data.append('csrf_token', csrfToken);
            } else if(typeof settings.data === 'object' && settings.data !== null) {
              settings.data.csrf_token = csrfToken;
            }
          }
        }
      });
    });
  </script>
</head>
<body>


	<nav class="navbar navbar-default navbar-static-top">
		<div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header" >
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
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
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">      

      <ul class="nav navbar-nav navbar-right">        

      	<li id="navDashboard"><a href="index.php"><i class="glyphicon glyphicon-list-alt"></i>  Dashboard</a></li>        
        <?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navBrand"><a href="brand.php"><i class="glyphicon glyphicon-btc"></i>  Brand</a></li>        
		<?php } ?>
		<?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navCategories"><a href="categories.php"> <i class="glyphicon glyphicon-th-list"></i> Category</a></li>        
		<?php } ?>
		<?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navProduct"><a href="product.php"> <i class="glyphicon glyphicon-ruble"></i> Product </a></li> 
		<?php } ?>
		
        <li class="dropdown" id="navOrder">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-shopping-cart"></i> Orders <span class="caret"></span></a>
          <ul class="dropdown-menu">            
            <li id="topNavAddOrder"><a href="orders.php?o=add"> <i class="glyphicon glyphicon-plus"></i> Add Orders</a></li>            
            <li id="topNavManageOrder"><a href="orders.php?o=manord"> <i class="glyphicon glyphicon-edit"></i> Manage Orders</a></li>            
          </ul>
        </li>
        <?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navOverdue"><a href="overdue_reminders.php"> <i class="glyphicon glyphicon-exclamation-sign"></i> Overdue <span id="overdue-badge" class="badge"></span></a></li>
        <?php } ?> 
		
		<?php  if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navReport"><a href="report.php"> <i class="glyphicon glyphicon-check"></i> Report </a></li>
		<?php } ?>
        <li class="dropdown" id="navSetting">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-user"></i> <span class="caret"></span></a>
          <ul class="dropdown-menu">    
			<?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
            <li id="topNavSetting"><a href="setting.php"> <i class="glyphicon glyphicon-wrench"></i> Setting</a></li>
            <li id="topNavUser"><a href="user.php"> <i class="glyphicon glyphicon-wrench"></i> Add User</a></li>
<?php } ?>              
            <li id="topNavLogout"><a href="logout.php"> <i class="glyphicon glyphicon-log-out"></i> Logout</a></li>            
          </ul>
        </li>        
           
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
	</nav>

	<div class="container">

    