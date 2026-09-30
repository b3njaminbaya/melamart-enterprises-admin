<?php require_once 'includes/header.php'; ?>
<?php require_admin(); ?>

<ol class="breadcrumb">
  <li><a href="dashboard.php">Home</a></li>
  <li class="active">Reports</li>
</ol>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="glyphicon glyphicon-stats"></i> Order Reports
			</div>
			<!-- /panel-heading -->
			<div class="panel-body">
				<div id="report-messages"></div>

				<form id="getOrderReportForm" novalidate>
				  <div class="row">
				    <div class="col-sm-6 col-md-3 form-group">
				      <label for="startDate" class="control-label">From *</label>
				      <input type="text" class="form-control" id="startDate" name="startDate" placeholder="dd/mm/yyyy" autocomplete="off" value="<?php echo h(date('01/m/Y')); ?>">
				    </div>
				    <div class="col-sm-6 col-md-3 form-group">
				      <label for="endDate" class="control-label">To *</label>
				      <input type="text" class="form-control" id="endDate" name="endDate" placeholder="dd/mm/yyyy" autocomplete="off" value="<?php echo h(date('d/m/Y')); ?>">
				    </div>
				    <div class="col-sm-6 col-md-3 form-group">
				      <label for="searchClient" class="control-label">Client name</label>
				      <input type="text" class="form-control" id="searchClient" name="searchClient" placeholder="Any client" autocomplete="off">
				    </div>
				    <div class="col-sm-6 col-md-3 form-group">
				      <label for="branchFilter" class="control-label">Branch</label>
				      <select class="form-control" id="branchFilter" name="branchFilter">
				        <?php echo render_options($MEL_BRANCHES, null, 'All branches'); ?>
				      </select>
				    </div>
				  </div>
				  <div class="row">
				    <div class="col-sm-4 form-group">
				      <label for="paymentStatusFilter" class="control-label">Payment status</label>
				      <select class="form-control" id="paymentStatusFilter" name="paymentStatusFilter">
				        <?php echo render_options($MEL_PAYMENT_STATUSES, null, 'All'); ?>
				      </select>
				    </div>
				    <div class="col-sm-4 form-group">
				      <label for="orderStatusFilter" class="control-label">Order status</label>
				      <select class="form-control" id="orderStatusFilter" name="orderStatusFilter">
				        <option value="">All except cancelled</option>
				        <?php echo render_options($MEL_ORDER_STATUSES); ?>
				      </select>
				    </div>
				    <div class="col-sm-4 form-group">
				      <label for="paymentTypeFilter" class="control-label">Last payment method</label>
				      <select class="form-control" id="paymentTypeFilter" name="paymentTypeFilter">
				        <?php echo render_options($MEL_PAYMENT_TYPES, null, 'All'); ?>
				      </select>
				    </div>
				  </div>
				  <div>
				      <button type="submit" class="btn btn-primary" id="viewReportBtn" data-loading-text="Loading…"><i class="glyphicon glyphicon-eye-open"></i> View report</button>
				      <button type="button" class="btn btn-default" id="printReportBtn"><i class="glyphicon glyphicon-print"></i> Print</button>
				      <button type="button" class="btn btn-default" id="exportReportBtn"><i class="glyphicon glyphicon-download-alt"></i> Export to Excel (CSV)</button>
				  </div>
				</form>

				<div id="reportResults" style="margin-top: 24px; display: none;">
					<div id="reportSummary" class="row"></div>
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-condensed" id="reportTable">
							<thead>
								<tr>
									<th>Order</th>
									<th>Order date</th>
									<th>Expected</th>
									<th>Returned</th>
									<th>Branch / site</th>
									<th>Client</th>
									<th class="text-right">Grand total</th>
									<th class="text-right">Paid</th>
									<th class="text-right">Balance</th>
									<th>Payment</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody id="reportTableBody"></tbody>
						</table>
					</div>
				</div>

			</div>
			<!-- /panel-body -->
		</div>
	</div>
</div>

<script src="custom/js/report.js"></script>

<?php require_once 'includes/footer.php'; ?>
