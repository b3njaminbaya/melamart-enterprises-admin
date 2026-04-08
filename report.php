<?php require_once 'includes/header.php'; ?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="glyphicon glyphicon-check"></i>	Order Reports & Analytics
			</div>
			<!-- /panel-heading -->
			<div class="panel-body">
				<div id="report-messages"></div>
				 
				<form class="form-horizontal" action="php_action/getOrderReport.php" method="post" id="getOrderReportForm">
				  <div class="row">
				    <div class="col-md-4">
				      <div class="form-group">
				        <label for="startDate" class="control-label">Start Date</label>
				        <input type="text" class="form-control" id="startDate" name="startDate" placeholder="Start Date" />
				      </div>
				    </div>
				    <div class="col-md-4">
				      <div class="form-group">
				        <label for="endDate" class="control-label">End Date</label>
				        <input type="text" class="form-control" id="endDate" name="endDate" placeholder="End Date" />
				      </div>
				    </div>
				    <div class="col-md-4">
				      <div class="form-group">
				        <label for="searchClient" class="control-label">Search Client</label>
				        <input type="text" class="form-control" id="searchClient" name="searchClient" placeholder="Client Name" />
				      </div>
				    </div>
				  </div>
				  <div class="row">
				    <div class="col-md-4">
				      <div class="form-group">
				        <label for="paymentStatusFilter" class="control-label">Payment Status</label>
				        <select class="form-control" id="paymentStatusFilter" name="paymentStatusFilter">
				          <option value="">All</option>
				          <option value="1">Full Payment</option>
				          <option value="2">Advance Payment</option>
				          <option value="3">No Payment</option>
				        </select>
				      </div>
				    </div>
				    <div class="col-md-4">
				      <div class="form-group">
				        <label for="orderStatusFilter" class="control-label">Order Status</label>
				        <select class="form-control" id="orderStatusFilter" name="orderStatusFilter">
				          <option value="">All</option>
				          <option value="0">Pending</option>
				          <option value="1">Completed</option>
				          <option value="2">Cancelled</option>
				        </select>
				      </div>
				    </div>
				    <div class="col-md-4">
				      <div class="form-group">
				        <label for="paymentTypeFilter" class="control-label">Payment Type</label>
				        <select class="form-control" id="paymentTypeFilter" name="paymentTypeFilter">
				          <option value="">All</option>
				          <option value="1">Cheque</option>
				          <option value="2">Cash</option>
				          <option value="3">Credit Card</option>
				        </select>
				      </div>
				    </div>
				  </div>
				  <div class="form-group">
				    <div class="col-sm-12">
				      <button type="submit" class="btn btn-success" id="generateReportBtn"> <i class="glyphicon glyphicon-ok-sign"></i> Generate Report</button>
				      <button type="button" class="btn btn-info" id="viewReportBtn"> <i class="glyphicon glyphicon-eye-open"></i> View Report</button>
				      <button type="button" class="btn btn-primary" id="exportReportBtn"> <i class="glyphicon glyphicon-download"></i> Export CSV</button>
				    </div>
				  </div>
				</form>

				<div id="reportResults" style="margin-top: 20px; display: none;">
					<div class="table-responsive">
						<table class="table table-bordered table-striped" id="reportTable">
							<thead>
								<tr>
									<th>#</th>
									<th>Order Date</th>
									<th>Expected Return</th>
									<th>Returned Date</th>
									<th>Site Location</th>
									<th>Client Name</th>
									<th>Client Contact</th>
									<th>Driver Name</th>
									<th>Grand Total</th>
									<th>Paid</th>
									<th>Due</th>
									<th>Payment Status</th>
									<th>Order Status</th>
								</tr>
							</thead>
							<tbody id="reportTableBody">
								<!-- Data loaded via AJAX -->
							</tbody>
						</table>
					</div>
					<div id="reportSummary" class="row" style="margin-top: 20px;">
						<!-- Summary cards will be loaded here -->
					</div>
				</div>

			</div>
			<!-- /panel-body -->
		</div>
	</div>
	<!-- /col-dm-12 -->
</div>
<!-- /row -->

<script src="custom/js/report.js"></script>

<?php require_once 'includes/footer.php'; ?>