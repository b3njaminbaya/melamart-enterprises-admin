<?php require_once 'includes/header.php'; ?>

<?php

$sql = "SELECT * FROM product WHERE status = 1";
$query = $connect->query($sql);
$countProduct = $query->num_rows;

$orderSql = "SELECT * FROM orders WHERE order_status = 1";
$orderQuery = $connect->query($orderSql);
$countOrder = $orderQuery->num_rows;

$totalRevenue = 0;
while ($orderResult = $orderQuery->fetch_assoc()) {
    $totalRevenue += $orderResult['paid'];
}

$lowStockSql = "SELECT * FROM product WHERE quantity <= 3 AND status = 1";
$lowStockQuery = $connect->query($lowStockSql);
$countLowStock = $lowStockQuery->num_rows;

$userwisesql = "SELECT users.username, SUM(orders.grand_total) as totalorder
                FROM orders
                INNER JOIN users ON orders.user_id = users.user_id
                WHERE orders.order_status = 1
                GROUP BY orders.user_id
                ORDER BY totalorder DESC";
$userwiseQuery  = $connect->query($userwisesql);
$userwieseOrder = $userwiseQuery->num_rows;
// Connection stays open — $userwiseQuery is iterated below in the template.

?>

<!-- fullCalendar CSS -->
<link rel="stylesheet" href="assets/plugins/fullcalendar/fullcalendar.min.css">
<link rel="stylesheet" href="assets/plugins/fullcalendar/fullcalendar.print.css" media="print">

<!-- ═══════════════════════════════════
     Stat panels row
═══════════════════════════════════ -->
<div class="row">

  <?php if(isset($_SESSION['userId']) && $_SESSION['userId'] == 1): ?>

  <div class="col-xs-12 col-sm-6 col-md-4">
    <div class="panel panel-default">
      <div class="panel-heading">
        <a href="product.php">
          <i class="glyphicon glyphicon-tag"></i> Total Products
          <span class="badge"><?php echo (int)$countProduct; ?></span>
        </a>
      </div>
    </div>
  </div>

  <div class="col-xs-12 col-sm-6 col-md-4">
    <div class="panel panel-default">
      <div class="panel-heading">
        <a href="product.php">
          <i class="glyphicon glyphicon-warning-sign"></i> Low Stock
          <span class="badge"><?php echo (int)$countLowStock; ?></span>
        </a>
      </div>
    </div>
  </div>

  <?php endif; ?>

  <div class="col-xs-12 col-sm-6 col-md-4">
    <div class="panel panel-default">
      <div class="panel-heading">
        <a href="orders.php?o=manord">
          <i class="glyphicon glyphicon-shopping-cart"></i> Total Orders
          <span class="badge"><?php echo (int)$countOrder; ?></span>
        </a>
      </div>
    </div>
  </div>

</div><!-- /stat panels row -->

<!-- ═══════════════════════════════════
     Cards + Orders-by-user table
═══════════════════════════════════ -->
<div class="row">

  <!-- Date & Revenue cards -->
  <div class="col-xs-12 col-sm-6 col-md-4">

    <div class="card">
      <div class="cardHeader">
        <h1><?php echo date('d'); ?></h1>
      </div>
      <div class="cardContainer">
        <p><?php echo date('l, d F Y'); ?></p>
      </div>
    </div>

    <div style="height:16px;"></div>

    <div class="card">
      <div class="cardHeader">
        <h1>Ksh <?php echo number_format($totalRevenue ?: 0, 2); ?></h1>
      </div>
      <div class="cardContainer">
        <p>Total Revenue (Active Orders)</p>
      </div>
    </div>

  </div><!-- /cards col -->

  <!-- Orders by user (admin only) -->
  <?php if(isset($_SESSION['userId']) && $_SESSION['userId'] == 1): ?>
  <div class="col-xs-12 col-sm-6 col-md-8">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="glyphicon glyphicon-user"></i> Orders by User
      </div>
      <div class="panel-body" style="padding:0 !important;">
        <div class="table-responsive">
          <table class="table" style="margin-bottom:0;">
            <thead>
              <tr>
                <th>Username</th>
                <th>Total (Ksh)</th>
              </tr>
            </thead>
            <tbody>
              <?php if($userwieseOrder > 0): ?>
                <?php while($row = $userwiseQuery->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo number_format($row['totalorder'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="2" class="text-center" style="color:#697587;padding:20px;">
                    No active orders found.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /cards row -->

<!-- fullCalendar JS (kept for backward compatibility, calendar div removed) -->
<script src="assets/plugins/moment/moment.min.js"></script>
<script src="assets/plugins/fullcalendar/fullcalendar.min.js"></script>

<script>
$(function() {
  $('#navDashboard').addClass('active');
});
</script>

<?php require_once 'includes/footer.php'; ?>
