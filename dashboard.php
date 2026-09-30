<?php require_once 'includes/header.php'; ?>

<?php
$today = date('Y-m-d');
$monthStart = date('Y-m-01');

// Orders currently on hire / overdue
$row = $connect->query(
	"SELECT
		SUM(order_status = 0 AND returned_date IS NULL) AS on_hire,
		SUM(order_status = 0 AND returned_date IS NULL AND expect_return_date < '$today') AS overdue
	 FROM orders"
)->fetch_assoc();
$countOnHire = (int)$row['on_hire'];
$countOverdue = (int)$row['overdue'];

// This month (by order date, excluding cancelled)
$stmt = $connect->prepare("SELECT COUNT(*) AS orders_count, COALESCE(SUM(grand_total), 0) AS billed FROM orders WHERE order_status != 2 AND order_date >= ?");
$stmt->bind_param('s', $monthStart);
$stmt->execute();
$month = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Money: collected overall and still owed (excluding cancelled orders)
$money = $connect->query("SELECT COALESCE(SUM(paid), 0) AS collected, COALESCE(SUM(GREATEST(due, 0)), 0) AS outstanding FROM orders WHERE order_status != 2")->fetch_assoc();

if(is_admin()) {
	$countProduct = (int)$connect->query("SELECT COUNT(*) FROM product WHERE status = 1")->fetch_row()[0];
	$lowStock = $connect->query("SELECT product_id, product_name, quantity FROM product WHERE status = 1 AND active = 1 AND quantity <= 3 ORDER BY quantity, product_name LIMIT 10");

	$userwiseQuery = $connect->query(
		"SELECT users.username, COUNT(*) AS order_count, SUM(orders.grand_total) AS totalorder
		 FROM orders
		 INNER JOIN users ON orders.user_id = users.user_id
		 WHERE orders.order_status != 2
		 GROUP BY orders.user_id, users.username
		 ORDER BY totalorder DESC"
	);
}
?>

<div class="row">
  <div class="col-xs-12">
    <h4 style="margin:0 0 16px;"><?php echo h(date('l, j F Y')); ?></h4>
  </div>
</div>

<div class="row">
  <div class="col-xs-6 col-md-3">
    <a class="stat-card" href="orders.php?o=manord">
      <div class="stat-label"><i class="glyphicon glyphicon-road"></i> On hire now</div>
      <div class="stat-value"><?php echo $countOnHire; ?></div>
    </a>
  </div>
  <div class="col-xs-6 col-md-3">
    <a class="stat-card <?php echo $countOverdue > 0 ? 'stat-danger' : ''; ?>" href="<?php echo is_admin() ? 'overdue_reminders.php' : 'orders.php?o=manord'; ?>">
      <div class="stat-label"><i class="glyphicon glyphicon-exclamation-sign"></i> Overdue returns</div>
      <div class="stat-value"><?php echo $countOverdue; ?></div>
    </a>
  </div>
  <div class="col-xs-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label"><i class="glyphicon glyphicon-calendar"></i> Orders this month</div>
      <div class="stat-value"><?php echo (int)$month['orders_count']; ?></div>
    </div>
  </div>
  <div class="col-xs-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label"><i class="glyphicon glyphicon-file"></i> Billed this month</div>
      <div class="stat-value">KSh <?php echo money($month['billed']); ?></div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-6">
    <div class="stat-card">
      <div class="stat-label"><i class="glyphicon glyphicon-ok-sign"></i> Total payments received</div>
      <div class="stat-value">KSh <?php echo money($money['collected']); ?></div>
    </div>
  </div>
  <div class="col-xs-12 col-sm-6">
    <?php $tag = is_admin() ? 'a href="report.php"' : 'div'; ?>
    <<?php echo $tag; ?> class="stat-card stat-warning">
      <div class="stat-label"><i class="glyphicon glyphicon-time"></i> Outstanding balances</div>
      <div class="stat-value">KSh <?php echo money($money['outstanding']); ?></div>
    </<?php echo is_admin() ? 'a' : 'div'; ?>>
  </div>
</div>

<?php if(is_admin()) { ?>
<div class="row">

  <div class="col-xs-12 col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="glyphicon glyphicon-user"></i> Orders by staff member
      </div>
      <div class="panel-body" style="padding:0 !important;">
        <div class="table-responsive">
          <table class="table" style="margin-bottom:0;">
            <thead>
              <tr>
                <th>Username</th>
                <th class="text-right">Orders</th>
                <th class="text-right">Total billed (KSh)</th>
              </tr>
            </thead>
            <tbody>
              <?php if($userwiseQuery->num_rows > 0): ?>
                <?php while($row = $userwiseQuery->fetch_assoc()): ?>
                <tr>
                  <td><?php echo h($row['username']); ?></td>
                  <td class="text-right"><?php echo (int)$row['order_count']; ?></td>
                  <td class="text-right"><?php echo money($row['totalorder']); ?></td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="3" class="text-center" style="color:#697587;padding:20px;">No orders yet.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xs-12 col-md-5">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="glyphicon glyphicon-warning-sign"></i> Low stock (3 or fewer in store) · <?php echo $countProduct; ?> products in total
      </div>
      <div class="panel-body" style="padding:0 !important;">
        <table class="table" style="margin-bottom:0;">
          <tbody>
            <?php if($lowStock->num_rows > 0): ?>
              <?php while($row = $lowStock->fetch_assoc()): ?>
              <tr>
                <td><?php echo h($row['product_name']); ?></td>
                <td class="text-right"><span class="label <?php echo (int)$row['quantity'] === 0 ? 'label-danger' : 'label-warning'; ?>"><?php echo (int)$row['quantity']; ?> in store</span></td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td class="text-center" style="color:#697587;padding:20px;">All equipment is well stocked.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        <div style="padding:10px 16px;"><a href="product.php">Manage equipment &rarr;</a></div>
      </div>
    </div>
  </div>

</div>
<?php } ?>

<script>
$(function() {
  $('#navDashboard').addClass('active');
});
</script>

<?php require_once 'includes/footer.php'; ?>
