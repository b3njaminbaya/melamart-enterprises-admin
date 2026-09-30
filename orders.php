<?php
require_once 'includes/header.php';

$mode = $_GET['o'] ?? 'manord';
if(!in_array($mode, array('add', 'manord', 'editOrd'), true)) {
	$mode = 'manord';
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$order = null;
$orderItems = array();
if($mode === 'editOrd') {
	$orderId = (int)($_GET['i'] ?? 0);
	$stmt = $connect->prepare("SELECT * FROM orders WHERE order_id = ?");
	$stmt->bind_param('i', $orderId);
	$stmt->execute();
	$order = $stmt->get_result()->fetch_assoc();
	$stmt->close();

	if(!$order) {
		echo '<div class="alert alert-warning" style="margin-top:20px;">Order not found. <a href="orders.php?o=manord">Back to orders</a></div>';
		require_once 'includes/footer.php';
		exit();
	}

	$stmt = $connect->prepare("SELECT product_id, quantity, rental_days, rate FROM order_item WHERE order_id = ? ORDER BY order_item_id");
	$stmt->bind_param('i', $orderId);
	$stmt->execute();
	$res = $stmt->get_result();
	while($row = $res->fetch_assoc()) {
		$orderItems[] = array(
			'product_id'  => (int)$row['product_id'],
			'quantity'    => (int)$row['quantity'],
			'rental_days' => max(1, (int)$row['rental_days']),
			'rate'        => (float)$row['rate'],
		);
	}
	$stmt->close();
}

if($mode === 'add' || $mode === 'editOrd') {
	// Products that can be picked, with how many are available to this order.
	$heldByOrder = array();
	if($order && order_holds_stock($order['order_status'], $order['returned_date'])) {
		foreach($orderItems as $item) {
			$heldByOrder[$item['product_id']] = ($heldByOrder[$item['product_id']] ?? 0) + $item['quantity'];
		}
	}
	$products = array();
	$res = $connect->query("SELECT product_id, product_name, quantity, daily_rate, active, status FROM product ORDER BY product_name");
	while($row = $res->fetch_assoc()) {
		$pid = (int)$row['product_id'];
		$isHireable = (int)$row['active'] === 1 && (int)$row['status'] === 1;
		$onThisOrder = false;
		foreach($orderItems as $item) {
			if($item['product_id'] === $pid) { $onThisOrder = true; }
		}
		if(!$isHireable && !$onThisOrder) {
			continue;
		}
		$products[] = array(
			'id'        => $pid,
			'name'      => $row['product_name'],
			'available' => (int)$row['quantity'] + ($heldByOrder[$pid] ?? 0),
			'rate'      => (float)$row['daily_rate'],
		);
	}
}

$isEdit = $mode === 'editOrd';
$val = function($key, $default = '') use ($order) {
	return $order ? $order[$key] : $default;
};
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$pageTitle = $mode === 'add' ? 'New Order' : ($isEdit ? 'Order #' . (int)$order['order_id'] : 'Manage Orders');
?>

<div class="div-request div-hide"><?php echo h($mode); ?></div>

<ol class="breadcrumb">
  <li><a href="dashboard.php">Home</a></li>
  <li><a href="orders.php?o=manord">Orders</a></li>
  <li class="active"><?php echo h($pageTitle); ?></li>
</ol>

<?php if($flash !== '') { ?>
<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="glyphicon glyphicon-ok-sign"></i> <?php echo h($flash); ?></div>
<?php } ?>

<div class="panel panel-default">
	<div class="panel-heading">
		<?php if($mode === 'add') { ?>
			<i class="glyphicon glyphicon-plus-sign"></i> New Order
		<?php } elseif($mode === 'manord') { ?>
			<i class="glyphicon glyphicon-list"></i> Manage Orders
		<?php } else { ?>
			<i class="glyphicon glyphicon-edit"></i> Order #<?php echo (int)$order['order_id']; ?>
			— <?php echo h(order_status_label($order['order_status'])); ?>
		<?php } ?>
	</div>
	<div class="panel-body">

	<?php if($mode === 'manord') { ?>

		<div id="success-messages"></div>

		<div class="filter-bar clearfix">
			<label for="orderStatusFilter" class="control-label" style="margin-right:8px;">Show</label>
			<select id="orderStatusFilter" class="form-control">
				<option value="">All open &amp; completed orders</option>
				<option value="onhire">On hire now</option>
				<option value="overdue">Overdue returns</option>
				<option value="completed">Completed</option>
				<option value="cancelled">Cancelled</option>
				<option value="all">Everything (incl. cancelled)</option>
			</select>
			<a href="orders.php?o=add" class="btn btn-primary pull-right"><i class="glyphicon glyphicon-plus-sign"></i> New Order</a>
		</div>

		<div class="table-responsive">
			<table class="table table-bordered table-striped table-condensed" id="manageOrderTable" style="width:100%;">
				<thead>
					<tr>
						<th>Order</th>
						<th>Date</th>
						<th>Client</th>
						<th>Site</th>
						<th>Expected return</th>
						<th>Returned</th>
						<th class="text-right">Total (KSh)</th>
						<th class="text-right">Paid</th>
						<th class="text-right">Balance</th>
						<th>Payment</th>
						<th>Status</th>
						<th></th>
					</tr>
				</thead>
			</table>
		</div>

	<?php } else { ?>

		<div class="order-messages"></div>

		<form method="POST" action="php_action/<?php echo $isEdit ? 'editOrder.php' : 'createOrder.php'; ?>" id="orderForm" novalidate>
			<?php if($isEdit) { ?>
			<input type="hidden" name="orderId" id="orderId" value="<?php echo (int)$order['order_id']; ?>">
			<?php } ?>

			<h4 class="form-section-title">Hire details</h4>
			<div class="row">
				<div class="col-sm-6 col-md-3 form-group">
					<label for="orderDate">Order date *</label>
					<input type="text" class="form-control" id="orderDate" name="orderDate" placeholder="dd/mm/yyyy" autocomplete="off"
						value="<?php echo h($isEdit ? format_date($order['order_date']) : date('d/m/Y')); ?>">
				</div>
				<div class="col-sm-6 col-md-3 form-group">
					<label for="expectReturnDate">Expected return date *</label>
					<input type="text" class="form-control" id="expectReturnDate" name="expectReturnDate" placeholder="dd/mm/yyyy" autocomplete="off"
						value="<?php echo h(format_date($val('expect_return_date'))); ?>">
					<p class="help-block small" id="hirePeriodHint"></p>
				</div>
				<div class="col-sm-6 col-md-3 form-group">
					<label for="branch">Branch *</label>
					<select class="form-control" id="branch" name="branch">
						<?php echo render_options($MEL_BRANCHES, $val('payment_place', count($MEL_BRANCHES) === 1 ? 1 : ''), '— Select branch —'); ?>
					</select>
				</div>
				<div class="col-sm-6 col-md-3 form-group">
					<label for="siteLocation">Site location *</label>
					<input type="text" class="form-control" id="siteLocation" name="siteLocation" placeholder="e.g. Kamakis, Ruiru" autocomplete="off" value="<?php echo h($val('site_location')); ?>">
				</div>
			</div>

			<h4 class="form-section-title">Client &amp; delivery</h4>
			<div class="row">
				<div class="col-sm-6 col-md-3 form-group">
					<label for="clientName">Client name *</label>
					<input type="text" class="form-control" id="clientName" name="clientName" autocomplete="off" value="<?php echo h($val('client_name')); ?>">
				</div>
				<div class="col-sm-6 col-md-3 form-group">
					<label for="clientContact">Client phone *</label>
					<input type="tel" class="form-control" id="clientContact" name="clientContact" placeholder="0712 345 678" autocomplete="off" value="<?php echo h(format_phone($val('client_contact'))); ?>">
				</div>
				<div class="col-sm-6 col-md-2 form-group">
					<label for="clientKraPin">Client KRA PIN</label>
					<input type="text" class="form-control" id="clientKraPin" name="clientKraPin" placeholder="Optional" maxlength="11" autocomplete="off" value="<?php echo h($val('gstn')); ?>" style="text-transform:uppercase;">
				</div>
				<div class="col-sm-6 col-md-2 form-group">
					<label for="driverName">Driver name *</label>
					<input type="text" class="form-control" id="driverName" name="driverName" autocomplete="off" value="<?php echo h($val('driver_name')); ?>">
				</div>
				<div class="col-sm-6 col-md-2 form-group">
					<label for="driverContact">Driver phone *</label>
					<input type="tel" class="form-control" id="driverContact" name="driverContact" placeholder="0712 345 678" autocomplete="off" value="<?php echo h(format_phone($val('driver_contact'))); ?>">
				</div>
			</div>

			<?php if($isEdit) { ?>
			<h4 class="form-section-title">Return</h4>
			<div class="row">
				<div class="col-sm-6 col-md-3 form-group">
					<label for="returnedDate">Returned date</label>
					<input type="text" class="form-control" id="returnedDate" name="returnedDate" placeholder="Leave blank while on hire" autocomplete="off" value="<?php echo h(format_date($order['returned_date'])); ?>">
					<p class="help-block small">Setting this returns the equipment to stock and completes the order.</p>
				</div>
				<div class="col-sm-6 col-md-3 form-group">
					<label for="returnedBy">Returned by</label>
					<input type="text" class="form-control" id="returnedBy" name="returnedBy" autocomplete="off" value="<?php echo h($order['returned_by']); ?>">
				</div>
				<div class="col-sm-6 col-md-2 form-group">
					<label for="returnedByContact">Returned by phone</label>
					<input type="tel" class="form-control" id="returnedByContact" name="returnedByContact" placeholder="0712 345 678" autocomplete="off" value="<?php echo h(format_phone($order['returned_by_contact'])); ?>">
				</div>
				<div class="col-sm-6 col-md-2 form-group">
					<label for="approvedBy">Approved by</label>
					<input type="text" class="form-control" id="approvedBy" name="approvedBy" autocomplete="off" value="<?php echo h($order['approved_by']); ?>">
				</div>
				<div class="col-sm-6 col-md-2 form-group">
					<label for="orderStatus">Order status</label>
					<select class="form-control" id="orderStatus" name="orderStatus">
						<?php echo render_options($MEL_ORDER_STATUSES, (int)$order['order_status']); ?>
					</select>
				</div>
			</div>
			<?php } ?>

			<h4 class="form-section-title">Equipment</h4>
			<div class="table-responsive">
				<table class="table" id="productTable">
					<thead>
						<tr>
							<th style="min-width:220px;">Product *</th>
							<th class="text-right">Daily rate (KSh)</th>
							<th style="width:110px;">Quantity *</th>
							<th style="width:110px;">Rental days *</th>
							<th class="text-right">Available</th>
							<th class="text-right">Line total (KSh)</th>
							<th style="width:50px;"></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<button type="button" class="btn btn-default btn-sm" id="addRowBtn"><i class="glyphicon glyphicon-plus-sign"></i> Add another product</button>

			<div class="row" style="margin-top:20px;">
				<div class="col-md-6">
					<?php if(!$isEdit) { ?>
					<h4 class="form-section-title">Payment received now</h4>
					<div class="row">
						<div class="col-sm-4 form-group">
							<label for="paid">Amount paid (KSh)</label>
							<input type="number" min="0" step="0.01" class="form-control" id="paid" name="paid" value="0" autocomplete="off">
						</div>
						<div class="col-sm-4 form-group">
							<label for="paymentType">Payment method</label>
							<select class="form-control" id="paymentType" name="paymentType">
								<?php echo render_options($MEL_PAYMENT_TYPES, null, '— Select —'); ?>
							</select>
						</div>
						<div class="col-sm-4 form-group">
							<label for="paymentReference">Reference</label>
							<input type="text" class="form-control" id="paymentReference" name="paymentReference" placeholder="M-Pesa code / cheque no." maxlength="100" autocomplete="off" style="text-transform:uppercase;">
						</div>
					</div>
					<p class="help-block small">Leave the amount at 0 if the client has not paid yet. Further payments can be recorded later from Manage Orders.</p>
					<?php } else { ?>
					<h4 class="form-section-title">Payments</h4>
					<p>Paid so far: <strong>KSh <?php echo money($order['paid']); ?></strong> · Status: <strong><?php echo h(payment_status_label($order['payment_status'])); ?></strong></p>
					<?php if((int)$order['order_status'] !== 2) { ?>
					<button type="button" class="btn btn-default btn-sm" onclick="paymentOrder(<?php echo (int)$order['order_id']; ?>)"><i class="glyphicon glyphicon-usd"></i> Record payment / view history</button>
					<?php } ?>
					<button type="button" class="btn btn-default btn-sm" onclick="printOrder(<?php echo (int)$order['order_id']; ?>)"><i class="glyphicon glyphicon-print"></i> Print invoice</button>
					<?php } ?>
				</div>

				<div class="col-md-6 order-summary">
					<h4 class="form-section-title">Summary</h4>
					<div class="form-horizontal">
						<div class="form-group">
							<label class="col-xs-6 control-label">Sub total</label>
							<div class="col-xs-6"><input type="text" class="form-control text-right" id="subTotal" readonly></div>
						</div>
						<?php if(MEL_VAT_RATE > 0) { ?>
						<div class="form-group">
							<label class="col-xs-6 control-label">VAT <?php echo h(MEL_VAT_RATE); ?>%</label>
							<div class="col-xs-6"><input type="text" class="form-control text-right" id="vat" readonly></div>
						</div>
						<?php } ?>
						<?php if($isEdit) { ?>
						<div class="form-group">
							<label class="col-xs-6 control-label">Late return charge</label>
							<div class="col-xs-6"><input type="text" class="form-control text-right" id="lateFee" readonly></div>
						</div>
						<?php } ?>
						<div class="form-group">
							<label for="discount" class="col-xs-6 control-label">Discount (KSh)</label>
							<div class="col-xs-6"><input type="number" min="0" step="0.01" class="form-control text-right" id="discount" name="discount" value="<?php echo h($isEdit ? number_format((float)$order['discount'], 2, '.', '') : '0'); ?>" autocomplete="off"></div>
						</div>
						<div class="form-group grand-total">
							<label class="col-xs-6 control-label">Grand total (KSh)</label>
							<div class="col-xs-6"><input type="text" class="form-control text-right" id="grandTotal" readonly></div>
						</div>
						<div class="form-group">
							<label class="col-xs-6 control-label">Balance due (KSh)</label>
							<div class="col-xs-6"><input type="text" class="form-control text-right" id="due" readonly></div>
						</div>
					</div>
				</div>
			</div>

			<hr>
			<div class="text-right">
				<a href="orders.php?o=manord" class="btn btn-link">Back to orders</a>
				<button type="submit" id="saveOrderBtn" data-loading-text="Saving…" class="btn btn-primary"><i class="glyphicon glyphicon-ok-sign"></i> <?php echo $isEdit ? 'Save changes' : 'Create order'; ?></button>
			</div>
		</form>

		<script>
			window.ORDER_FORM = <?php echo json_encode(array(
				'isEdit'       => $isEdit,
				'products'     => $products,
				'items'        => $orderItems,
				'vatRate'      => (float)MEL_VAT_RATE,
				'alreadyPaid'  => $isEdit ? (float)$order['paid'] : 0,
				'orderStatus'  => $isEdit ? (int)$order['order_status'] : 0,
			), $jsonFlags); ?>;
		</script>

	<?php } ?>

	</div> <!--/panel-body-->
</div> <!--/panel-->


<!-- record payment -->
<div class="modal fade" tabindex="-1" role="dialog" id="paymentOrderModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-usd"></i> Payments — <span id="paymentOrderTitle"></span></h4>
      </div>
      <div class="modal-body">
      	<div class="paymentOrderMessages"></div>

      	<div class="row text-center" style="margin-bottom:12px;">
      		<div class="col-xs-4"><small class="text-muted">Grand total</small><div><strong id="payGrandTotal"></strong></div></div>
      		<div class="col-xs-4"><small class="text-muted">Paid</small><div><strong id="payPaid"></strong></div></div>
      		<div class="col-xs-4"><small class="text-muted">Balance</small><div><strong id="payDue"></strong></div></div>
      	</div>

      	<div id="paymentHistory"></div>

      	<form id="paymentForm" novalidate>
      		<h4 class="form-section-title">Record a payment</h4>
      		<div class="row">
      			<div class="col-sm-4 form-group">
      				<label for="payAmount">Amount (KSh) *</label>
      				<input type="number" min="0.01" step="0.01" class="form-control" id="payAmount" name="payAmount" autocomplete="off">
      			</div>
      			<div class="col-sm-4 form-group">
      				<label for="payPaymentType">Method *</label>
      				<select class="form-control" id="payPaymentType" name="paymentType">
      					<?php echo render_options($MEL_PAYMENT_TYPES, null, '— Select —'); ?>
      				</select>
      			</div>
      			<div class="col-sm-4 form-group">
      				<label for="payReference">Reference</label>
      				<input type="text" class="form-control" id="payReference" name="paymentReference" placeholder="M-Pesa code" maxlength="100" autocomplete="off" style="text-transform:uppercase;">
      			</div>
      		</div>
      	</form>
      	<p id="paymentFullyPaid" class="text-success" style="display:none;"><i class="glyphicon glyphicon-ok-sign"></i> This order is fully paid.</p>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="updatePaymentOrderBtn" data-loading-text="Saving…"><i class="glyphicon glyphicon-ok-sign"></i> Record payment</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- cancel order -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeOrderModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-ban-circle"></i> Cancel order <span id="removeOrderTitle"></span></h4>
      </div>
      <div class="modal-body">
      	<div class="removeOrderMessages"></div>
        <p>Cancel this order? Any equipment still on hire will be put back into stock. Recorded payments stay in the payment history.</p>
        <p class="text-muted">This cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Keep order</button>
        <button type="button" class="btn btn-danger" id="removeOrderBtn" data-loading-text="Cancelling…"><i class="glyphicon glyphicon-ban-circle"></i> Cancel order</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script src="custom/js/order.js"></script>

<?php require_once 'includes/footer.php'; ?>
