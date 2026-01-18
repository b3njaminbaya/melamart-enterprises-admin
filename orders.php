<?php 
require_once 'php_action/db_connect.php'; 
require_once 'includes/header.php'; 

if($_GET['o'] == 'add') { 
// add order
	echo "<div class='div-request div-hide'>add</div>";
} else if($_GET['o'] == 'manord') { 
	echo "<div class='div-request div-hide'>manord</div>";
} else if($_GET['o'] == 'editOrd') { 
	echo "<div class='div-request div-hide'>editOrd</div>";
} // /else manage order


?>

<ol class="breadcrumb">
  <li><a href="dashboard.php">Home</a></li>
  <li>Order</li>
  <li class="active">
  	<?php if($_GET['o'] == 'add') { ?>
  		Add Order
		<?php } else if($_GET['o'] == 'manord') { ?>
			Manage Order
		<?php } // /else manage order ?>
  </li>
</ol>


<h4>
	<i class='glyphicon glyphicon-circle-arrow-right'></i>
	<?php if($_GET['o'] == 'add') {
		echo "Add Order";
	} else if($_GET['o'] == 'manord') { 
		echo "Manage Order";
	} else if($_GET['o'] == 'editOrd') { 
		echo "Edit Order";
	}
	?>	
</h4>



<div class="panel panel-default">
	<div class="panel-heading">

		<?php if($_GET['o'] == 'add') { ?>
  		<i class="glyphicon glyphicon-plus-sign"></i>	Add Order
		<?php } else if($_GET['o'] == 'manord') { ?>
			<i class="glyphicon glyphicon-edit"></i> Manage Order
		<?php } else if($_GET['o'] == 'editOrd') { ?>
			<i class="glyphicon glyphicon-edit"></i> Edit Order
		<?php } ?>

	</div> <!--/panel-->	
	<div class="panel-body">
			
		<?php if($_GET['o'] == 'add') { 
			// add order
			?>			

			<div class="success-messages"></div> <!--/success-messages-->

  		<form class="form-horizontal" method="POST" action="php_action/createOrder.php" id="createOrderForm">

			  <div class="form-group" style="margin:0">
			    <label for="orderDate" class="col-sm-2 control-label">Order Date</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="orderDate" name="orderDate" autocomplete="off" />
			    </div>
			  </div> 
			  <!--/form-group-->
			  
			  <!-- New Fields Added Here -->
			  <div class="form-group" style="margin:0">
			    <label for="expectReturnDate" class="col-sm-2 control-label">Expected Return Date</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="expectReturnDate" name="expectReturnDate" autocomplete="off" />
			    </div>
			  </div>
			  
			  <div class="form-group" style="margin:0">
			    <label for="siteLocation" class="col-sm-2 control-label">Site Location</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="siteLocation" name="siteLocation" placeholder="Site Location" autocomplete="off" />
			    </div>
			  </div>
			  <!-- End New Fields -->
			  
			  <div class="form-group" style="margin:0">
			    <label for="clientName" class="col-sm-2 control-label">Client Name</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="clientName" name="clientName" placeholder="Client Name" autocomplete="off" />
			    </div>
			  </div> 
			  <!--/form-group-->
			  <div class="form-group" style="margin:0">
			    <label for="clientContact" class="col-sm-2 control-label">Client Contact</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="clientContact" name="clientContact" placeholder="Contact Number" autocomplete="off" />
			    </div>
			  </div> 
			  <!--/form-group-->	

			  <!--/form-group-->
			  <div class="form-group" style="margin:0">
			    <label for="driverName" class="col-sm-2 control-label">Driver Name</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="driverName" name="driverName" placeholder="Driver Name" autocomplete="off" />
			    </div>
			  </div> 
			  <!--/form-group-->
			  <div class="form-group" style="margin:0">
			    <label for="driverContact" class="col-sm-2 control-label">Driver Contact</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="driverContact" name="driverContact" placeholder="Contact Number" autocomplete="off" />
			    </div>
			  </div> 
			  <!--/form-group-->			  

			  <table class="table" id="productTable">
			  	<thead>
			  		<tr>			  			
			  			<th >Product</th>
			  			<th ">Daily Rate</th>
						<th >Rental Days</th>
			  			<th >Available Quantity</th>
			  			<th >Quantity</th>			  			
			  			<th ">Total</th>			  			
			  			<th ></th>
			  		</tr>
			  	</thead>
			  	<tbody>
			  		<?php
			  		$arrayNumber = 0;
			  		for($x = 1; $x < 4; $x++) { ?>
			  			<tr id="row<?php echo $x; ?>" class="<?php echo $arrayNumber; ?>">			  				
			  				<td style="margin-left:20px;">
			  					<div class="form-group" style="margin:0">
			  					<select class="form-control" name="productName[]" id="productName<?php echo $x; ?>" onchange="getProductData(<?php echo $x; ?>)" >
			  						<option value="">~~SELECT~~</option>
			  						<?php
			  							$productSql = "SELECT * FROM product WHERE active = 1 AND status = 1 AND quantity != 0";
			  							$productData = $connect->query($productSql);

			  							while($row = $productData->fetch_array()) {									 		
			  								echo "<option value='".$row['product_id']."' id='changeProduct".$row['product_id']."'>".$row['product_name']."</option>";
										 	} // /while 
			  						?>
		  						</select>
			  					</div>
			  				</td>
			  				<td style="padding-left:20px;">			  					
			  					<input type="text" name="dailyRate[]" id="dailyRate<?php echo $x; ?>" autocomplete="off" disabled="true" class="form-control" />			  					
			  					<input type="hidden" name="dailyRateValue[]" id="dailyRateValue<?php echo $x; ?>" autocomplete="off" class="form-control" />			  					
			  				</td>
							<td style="padding-left:20px;">
								<div class="form-group" style="margin:0">
									<input type="number" name="rentalDays[]" id="rentalDays<?php echo $x; ?>" 
										onkeyup="calculateRentalTotal(<?php echo $x ?>)" 
										onchange="calculateRentalTotal(<?php echo $x ?>)" 
										autocomplete="off" class="form-control" min="1" value="1" />
								</div>
							</td>
							<td style="padding-left:20px;">
			  					<div class="form-group" style="margin:0">
									<p id="available_quantity<?php echo $x; ?>"></p>
			  					</div>
			  				</td>
			  				<td style="padding-left:20px;">
								<div class="form-group" style="margin:0">
									<input type="number" name="quantity[]" id="quantity<?php echo $x; ?>" 
										onkeyup="calculateRentalTotal(<?php echo $x ?>)" 
										onchange="calculateRentalTotal(<?php echo $x ?>)" 
										autocomplete="off" class="form-control" min="1" value="1" />
								</div>
							</td>
			  				<td style="padding-left:20px;">			  					
			  					<input type="text" name="total[]" id="total<?php echo $x; ?>" autocomplete="off" class="form-control" disabled="true" />			  					
			  					<input type="hidden" name="totalValue[]" id="totalValue<?php echo $x; ?>" autocomplete="off" class="form-control" />			  					
			  				</td>
			  				<td>
			  					<button class="btn btn-default removeProductRowBtn" type="button" id="removeProductRowBtn" onclick="removeProductRow(<?php echo $x; ?>)"><i class="glyphicon glyphicon-trash"></i></button>
			  				</td>
			  			</tr>
		  			<?php
		  			$arrayNumber++;
			  		} // /for
			  		?>
			  	</tbody>			  	
			  </table>

			  <div class="col-md-6">
			  	<div class="form-group" style="margin:0">
				    <label for="subTotal" class="col-sm-3 control-label">Sub Amount</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="subTotal" name="subTotal" disabled="true" />
				      <input type="hidden" class="form-control" id="subTotalValue" name="subTotalValue" />
				    </div>
				  </div> <!--/form-group-->
				  <div class="form-group" style="margin:0">
				    <label for="vat" class="col-sm-3 control-label">VAT</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="vat" name="vat" readonly="true" />
				      <input type="hidden" class="form-control" id="vatValue" name="vatValue" />
				    </div>
				  </div> <!--/form-group-->			  
				  <div class="form-group" style="margin:0">
				    <label for="totalAmount" class="col-sm-3 control-label">Total Amount</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="totalAmount" name="totalAmount" disabled="true"/>
				      <input type="hidden" class="form-control" id="totalAmountValue" name="totalAmountValue" />
				    </div>
				  </div> <!--/form-group-->			  
				  <div class="form-group" style="margin:0">
				    <label for="discount" class="col-sm-3 control-label">Discount</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="discount" name="discount" onkeyup="discountFunc()" autocomplete="off" />
				    </div>
				  </div> <!--/form-group-->	
				  <div class="form-group" style="margin:0">
				    <label for="grandTotal" class="col-sm-3 control-label">Grand Total</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="grandTotal" name="grandTotal" disabled="true" />
				      <input type="hidden" class="form-control" id="grandTotalValue" name="grandTotalValue" />
				    </div>
				  </div> <!--/form-group-->
				  <div class="form-group" style="margin:0">
				    <label for="gstn" class="col-sm-3 control-label">GSTN</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="gstn" name="gstn" placeholder="GST Number" autocomplete="off" />
				    </div>
				  </div> <!--/form-group-->
			  </div> <!--/col-md-6-->

			  <div class="col-md-6">
			  	<div class="form-group" style="margin:0">
				    <label for="paid" class="col-sm-3 control-label">Paid Amount</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="paid" name="paid" autocomplete="off" onkeyup="paidAmount()" />
				    </div>
				  </div> <!--/form-group-->			  
				  <div class="form-group" style="margin:0">
				    <label for="due" class="col-sm-3 control-label">Due Amount</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="due" name="due" disabled="true" />
				      <input type="hidden" class="form-control" id="dueValue" name="dueValue" />
				    </div>
				  </div> <!--/form-group-->		
				  <div class="form-group" style="margin:0">
				    <label for="clientContact" class="col-sm-3 control-label">Payment Type</label>
				    <div class="col-sm-9">
				      <select class="form-control" name="paymentType" id="paymentType">
				      	<option value="">~~SELECT~~</option>
				      	<option value="1">Cheque</option>
				      	<option value="2">Cash</option>
				      	<option value="3">Credit Card</option>
				      </select>
				    </div>
				  </div> <!--/form-group-->							  
				  <div class="form-group" style="margin:0">
				    <label for="clientContact" class="col-sm-3 control-label">Payment Status</label>
				    <div class="col-sm-9">
				      <select class="form-control" name="paymentStatus" id="paymentStatus">
				      	<option value="">~~SELECT~~</option>
				      	<option value="1">Full Payment</option>
				      	<option value="2">Advance Payment</option>
				      	<option value="3">No Payment</option>
				      </select>
				    </div>
				  </div> <!--/form-group-->
				  <div class="form-group" style="margin:0">
				    <label for="clientContact" class="col-sm-3 control-label">Payment Place</label>
				    <div class="col-sm-9">
				      <select class="form-control" name="paymentPlace" id="paymentPlace">
				      	<option value="">~~SELECT~~</option>
				      	<option value="1">In Gujarat</option>
				      	<option value="2">Out Of Gujarat</option>
				      </select>
				    </div>
				  </div> <!--/form-group-->
				  <div class="form-group" style="margin:0">
				    <label for="orderStatus" class="col-sm-3 control-label">Order Status</label>
				    <div class="col-sm-9">
				      <select class="form-control" name="orderStatus" id="orderStatus">
				      	<option value="0">Pending</option>
				      	<option value="1">Completed</option>
				      	<option value="2">Cancelled</option>
				      </select>
				    </div>
				  </div> <!--/form-group-->							  
			  </div> <!--/col-md-6-->


			  <div class="form-group submitButtonFooter">
			    <div class="col-sm-offset-2 col-sm-10">
			    <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>

			      <button type="submit" id="createOrderBtn" data-loading-text="Loading..." class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>

			      <button type="reset" class="btn btn-default" onclick="resetOrderForm()"><i class="glyphicon glyphicon-erase"></i> Reset</button>
			    </div>
			  </div>
			</form>
		<?php } else if($_GET['o'] == 'manord') { 
			// manage order
			?>

			<div id="success-messages"></div>
			
			<div class="table-responsive">
  			<table class="table table-bordered table-striped table-condensed" id="manageOrderTable">

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
						<th>Driver Contact</th>
						<th>Returned By</th>
						<th>Approved By</th>
						<th>Grand Total</th>
						<th>Paid</th>
						<th>Due</th>
						<th>Payment Type</th>
						<th>Payment Status</th>
						<th>Order Status</th>
						<th>Option</th>
					</tr>
				</thead>
			</table>
		</div>

		
		<?php 
		// /else manage order
		} else if($_GET['o'] == 'editOrd') {
			// get order
			?>
			
			<div class="success-messages"></div> <!--/success-messages-->

  		<form class="form-horizontal" method="POST" action="php_action/editOrder.php" id="editOrderForm">

  			<?php $orderId = $_GET['i'];

  			$sql = "SELECT orders.order_id, orders.order_date, orders.expect_return_date, orders.returned_date, orders.site_location, orders.client_name, orders.client_contact, orders.driver_name, orders.driver_contact, orders.returned_by, orders.returned_by_contact, orders.approved_by, orders.sub_total, orders.vat, orders.total_amount, orders.discount, orders.grand_total, orders.paid, orders.due, orders.payment_type, orders.payment_status, orders.payment_place, orders.gstn, orders.order_status FROM orders 	
					WHERE orders.order_id = {$orderId}";

				$result = $connect->query($sql);
				$data = $result->fetch_row();
  			?>

			  <div class="form-group" style="margin:0">
			    <label for="orderDate" class="col-sm-2 control-label">Order Date</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="orderDate" name="orderDate" autocomplete="off" value="<?php echo $data[1] ?>" />
			    </div>
			  </div> 
			  <!--/form-group-->
			  
			  <!-- New Fields Added Here -->
			  <div class="form-group" style="margin:0">
			    <label for="expectReturnDate" class="col-sm-2 control-label">Expected Return Date</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="expectReturnDate" name="expectReturnDate" autocomplete="off" value="<?php echo $data[2] ?>" />
			    </div>
			  </div>
			  
			  <!-- In the edit order section of orders.php -->
				<div class="form-group" style="margin:0">
					<label for="returnedDate" class="col-sm-2 control-label">Returned Date</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" id="returnedDate" name="returnedDate" value="<?php echo ($data[3] != '0000-00-00') ? $data[3] : ''; ?>" />
					</div>
				</div>
			  
			  <div class="form-group" style="margin:0">
			    <label for="siteLocation" class="col-sm-2 control-label">Site Location</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="siteLocation" name="siteLocation" placeholder="Site Location" autocomplete="off" value="<?php echo $data[4] ?>" />
			    </div>
			  </div>
			  <!-- End New Fields -->
			  
			  <div class="form-group" style="margin:0">
			    <label for="clientName" class="col-sm-2 control-label">Client Name</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="clientName" name="clientName" placeholder="Client Name" autocomplete="off" value="<?php echo $data[5] ?>" />
			    </div>
			  </div> 
			  <!--/form-group-->
			  <div class="form-group" style="margin:0">
			    <label for="clientContact" class="col-sm-2 control-label">Client Contact</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="clientContact" name="clientContact" placeholder="Contact Number" autocomplete="off" value="<?php echo $data[6] ?>" />
			    </div>
			  </div> 
			  <!--/form-group-->		
			  
			  <!--/form-group-->
			  <div class="form-group" style="margin:0">
			    <label for="driverName" class="col-sm-2 control-label">Driver Name</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="driverName" name="driverName" placeholder="Driver Name" autocomplete="off" value="<?php echo $data[7] ?>" />
			    </div>
			  </div> 
			  <!--/form-group-->
			  <div class="form-group" style="margin:0">
			    <label for="driverContact" class="col-sm-2 control-label">Driver Contact</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="driverContact" name="driverContact" placeholder="Contact Number" autocomplete="off" value="<?php echo $data[8] ?>" />
			    </div>
			  </div> 
			  <!--/form-group-->
			  
			  <!-- Additional Return Fields -->
			  <div class="form-group" style="margin:0">
			    <label for="returnedBy" class="col-sm-2 control-label">Returned By</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="returnedBy" name="returnedBy" placeholder="Returned By" autocomplete="off" value="<?php echo $data[9] ?>" />
			    </div>
			  </div>
			  
			  <div class="form-group" style="margin:0">
			    <label for="returnedByContact" class="col-sm-2 control-label">Returned By Contact</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="returnedByContact" name="returnedByContact" placeholder="Contact Number" autocomplete="off" value="<?php echo $data[10] ?>" />
			    </div>
			  </div>
			  
			  <div class="form-group" style="margin:0">
			    <label for="approvedBy" class="col-sm-2 control-label">Approved By</label>
			    <div class="col-sm-10">
			      <input type="text" class="form-control" id="approvedBy" name="approvedBy" placeholder="Approved By" autocomplete="off" value="<?php echo $data[11] ?>" />
			    </div>
			  </div>
			  <!-- End Additional Return Fields -->

			  <table class="table" id="productTable">
			  	<thead>
			  		<tr>			  			
			  			<th >Product</th>
						<th >Daily Rate</th>
						<th >Rental Days</th>
			  			<th">Available Quantity</th>			  			
			  			<th">Quantity</th>			  			
			  			<th >Total</th>			  			
			  			<th ></th>
			  		</tr>
			  	</thead>
			  	<tbody>
			  		<?php
			  		$orderItemSql = "SELECT order_item.order_item_id, order_item.order_id, order_item.product_id, order_item.quantity, order_item.rate, order_item.total FROM order_item WHERE order_item.order_id = {$orderId}";
						$orderItemResult = $connect->query($orderItemSql);
			  		$arrayNumber = 0;
			  		$x = 1;
			  		while($orderItemData = $orderItemResult->fetch_array()) { 
			  			// Calculate rental days from existing total
			  			$dailyRate = $orderItemData['rate'];
			  			$quantity = $orderItemData['quantity'];
			  			$total = $orderItemData['total'];
			  			$rentalDays = ($dailyRate > 0 && $quantity > 0) ? round($total / ($dailyRate * $quantity), 0) : 1;
			  			?>
			  			<tr id="row<?php echo $x; ?>" class="<?php echo $arrayNumber; ?>">			  				
			  				<td style="margin-left:20px;">
			  					<div class="form-group" style="margin:0">
			  					<select class="form-control" name="productName[]" id="productName<?php echo $x; ?>" onchange="getProductData(<?php echo $x; ?>)" >
			  						<option value="">~~SELECT~~</option>
			  						<?php
			  							$productSql = "SELECT * FROM product WHERE active = 1 AND status = 1";
			  							$productData = $connect->query($productSql);

			  							while($row = $productData->fetch_array()) {									 		
			  								$selected = "";
			  								if($row['product_id'] == $orderItemData['product_id']) {
			  									$selected = "selected";
			  								} else {
			  									$selected = "";
			  								}

			  								echo "<option value='".$row['product_id']."' id='changeProduct".$row['product_id']."' ".$selected." >".$row['product_name']."</option>";
										 	} // /while 
			  						?>
		  						</select>
			  					</div>
			  				</td>
			  				<td style="padding-left:20px;">			  					
								<input type="text" name="dailyRate[]" id="dailyRate<?php echo $x; ?>" autocomplete="off" disabled="true" class="form-control" value="<?php echo $dailyRate; ?>" />			  					
								<input type="hidden" name="dailyRateValue[]" id="dailyRateValue<?php echo $x; ?>" autocomplete="off" class="form-control" value="<?php echo $dailyRate; ?>" />			  					
							</td>
							<td style="padding-left:20px;">
								<div class="form-group" style="margin:0">
									<input type="number" name="rentalDays[]" id="rentalDays<?php echo $x; ?>" 
										onkeyup="calculateRentalTotal(<?php echo $x ?>)" 
										onchange="calculateRentalTotal(<?php echo $x ?>)" 
										autocomplete="off" class="form-control" min="1" value="1" />
								</div>
							</td>
							<td style="padding-left:20px;">
			  					<div class="form-group" style="margin:0">
									<?php
			  							$productSql = "SELECT * FROM product WHERE product_id = ".$orderItemData['product_id'];
			  							$productData = $connect->query($productSql);

			  							while($row = $productData->fetch_array()) {									 		
			  								echo "<p id='available_quantity".$row['product_id']."'>".$row['quantity']."</p>";
										 	} // /while 
			  						?>
			  					</div>
			  				</td>
			  				<td style="padding-left:20px;">
								<div class="form-group" style="margin:0">
									<input type="number" name="quantity[]" id="quantity<?php echo $x; ?>" 
										onkeyup="calculateRentalTotal(<?php echo $x ?>)" 
										onchange="calculateRentalTotal(<?php echo $x ?>)" 
										autocomplete="off" class="form-control" min="1" value="1" />
								</div>
							</td>
			  				<td style="padding-left:20px;">			  					
			  					<input type="text" name="total[]" id="total<?php echo $x; ?>" autocomplete="off" class="form-control" disabled="true" value="<?php echo $total; ?>"/>			  					
			  					<input type="hidden" name="totalValue[]" id="totalValue<?php echo $x; ?>" autocomplete="off" class="form-control" value="<?php echo $total; ?>"/>			  					
			  				</td>
			  				<td>
			  					<button class="btn btn-default removeProductRowBtn" type="button" id="removeProductRowBtn" onclick="removeProductRow(<?php echo $x; ?>)"><i class="glyphicon glyphicon-trash"></i></button>
			  				</td>
			  			</tr>
		  			<?php
		  			$arrayNumber++;
		  			$x++;
			  		} // /while
			  		?>
			  	</tbody>			  	
			  </table>

			  <div class="col-md-6">
			  	<div class="form-group" style="margin:0">
				    <label for="subTotal" class="col-sm-3 control-label">Sub Amount</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="subTotal" name="subTotal" disabled="true" value="<?php echo $data[12] ?>" />
				      <input type="hidden" class="form-control" id="subTotalValue" name="subTotalValue" value="<?php echo $data[12] ?>" />
				    </div>
				  </div> <!--/form-group-->
				  <div class="form-group" style="margin:0">
				    <label for="vat" class="col-sm-3 control-label">VAT</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="vat" name="vat" disabled="true" value="<?php echo $data[13] ?>"  />
				      <input type="hidden" class="form-control" id="vatValue" name="vatValue" value="<?php echo $data[13] ?>"  />
				    </div>
				  </div> <!--/form-group-->			  
				  <div class="form-group" style="margin:0">
				    <label for="totalAmount" class="col-sm-3 control-label">Total Amount</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="totalAmount" name="totalAmount" disabled="true" value="<?php echo $data[14] ?>" />
				      <input type="hidden" class="form-control" id="totalAmountValue" name="totalAmountValue" value="<?php echo $data[14] ?>"  />
				    </div>
				  </div> <!--/form-group-->			  
				  <div class="form-group" style="margin:0">
				    <label for="discount" class="col-sm-3 control-label">Discount</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="discount" name="discount" onkeyup="discountFunc()" autocomplete="off" value="<?php echo $data[15] ?>" />
				    </div>
				  </div> <!--/form-group-->	
				  <div class="form-group" style="margin:0">
				    <label for="grandTotal" class="col-sm-3 control-label">Grand Total</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="grandTotal" name="grandTotal" disabled="true" value="<?php echo $data[16] ?>"  />
				      <input type="hidden" class="form-control" id="grandTotalValue" name="grandTotalValue" value="<?php echo $data[16] ?>"  />
				    </div>
				  </div> <!--/form-group-->
				  <div class="form-group" style="margin:0">
				    <label for="gstn" class="col-sm-3 control-label">GSTN</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="gstn" name="gstn" value="<?php echo $data[22] ?>"  />
				    </div>
				  </div> <!--/form-group-->
				  <div class="form-group" style="margin:0">
				    <label for="orderStatus" class="col-sm-3 control-label">Order Status</label>
				    <div class="col-sm-9">
				      <select class="form-control" name="orderStatus" id="orderStatus">
				      	<option value="0" <?php if($data[23] == 0) { echo "selected"; } ?>>Pending</option>
				      	<option value="1" <?php if($data[23] == 1) { echo "selected"; } ?>>Completed</option>
				      	<option value="2" <?php if($data[23] == 2) { echo "selected"; } ?>>Cancelled</option>
				      </select>
				    </div>
				  </div> <!--/form-group-->		  		  
			  </div> <!--/col-md-6-->

			  <div class="col-md-6">
			  	<div class="form-group" style="margin:0">
				    <label for="paid" class="col-sm-3 control-label">Paid Amount</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="paid" name="paid" autocomplete="off" onkeyup="paidAmount()" value="<?php echo $data[17] ?>"  />
				    </div>
				  </div> <!--/form-group-->			  
				  <div class="form-group" style="margin:0">
				    <label for="due" class="col-sm-3 control-label">Due Amount</label>
				    <div class="col-sm-9">
				      <input type="text" class="form-control" id="due" name="due" disabled="true" value="<?php echo $data[18] ?>"  />
				      <input type="hidden" class="form-control" id="dueValue" name="dueValue" value="<?php echo $data[18] ?>"  />
				    </div>
				  </div> <!--/form-group-->		
				  <div class="form-group" style="margin:0">
				    <label for="clientContact" class="col-sm-3 control-label">Payment Type</label>
				    <div class="col-sm-9">
				      <select class="form-control" name="paymentType" id="paymentType" >
				      	<option value="">~~SELECT~~</option>
				      	<option value="1" <?php if($data[19] == 1) { echo "selected"; } ?> >Cheque</option>
				      	<option value="2" <?php if($data[19] == 2) { echo "selected"; } ?>  >Cash</option>
				      	<option value="3" <?php if($data[19] == 3) { echo "selected"; } ?> >Credit Card</option>
				      </select>
				    </div>
				  </div> <!--/form-group-->							  
				  <div class="form-group" style="margin:0">
				    <label for="clientContact" class="col-sm-3 control-label">Payment Status</label>
				    <div class="col-sm-9">
				      <select class="form-control" name="paymentStatus" id="paymentStatus">
				      	<option value="">~~SELECT~~</option>
				      	<option value="1" <?php if($data[20] == 1) { echo "selected"; } ?>  >Full Payment</option>
				      	<option value="2" <?php if($data[20] == 2) { echo "selected"; } ?> >Advance Payment</option>
				      	<option value="3" <?php if($data[20] == 3) { echo "selected"; } ?> >No Payment</option>
				      </select>
				    </div>
				  </div> <!--/form-group-->
				  <div class="form-group" style="margin:0">
				    <label for="clientContact" class="col-sm-3 control-label">Payment Place</label>
				    <div class="col-sm-9">
				      <select class="form-control" name="paymentPlace" id="paymentPlace">
				      	<option value="">~~SELECT~~</option>
				      	<option value="1" <?php if($data[21] == 1) { echo "selected"; } ?>  >In Gujarat</option>
				      	<option value="2" <?php if($data[21] == 2) { echo "selected"; } ?> >Out Of Gujarat</option>
				      </select>
				    </div>
				  </div>							  
			  </div> <!--/col-md-6-->


			  <div class="form-group editButtonFooter">
			    <div class="col-sm-offset-2 col-sm-10">
			    <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>

			    <input type="hidden" name="orderId" id="orderId" value="<?php echo $_GET['i']; ?>" />

			    <button type="submit" id="editOrderBtn" data-loading-text="Loading..." class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
			      
			    </div>
			  </div>
			</form>

			<?php
		} // /get order else  ?>


	</div> <!--/panel-->	
</div> <!--/panel-->	


<!-- edit order -->
<div class="modal fade" tabindex="-1" role="dialog" id="paymentOrderModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-edit"></i> Edit Payment</h4>
      </div>      

      <div class="modal-body form-horizontal" style="max-height:500px; overflow:auto;" >

      	<div class="paymentOrderMessages"></div>

      	     				 				 
			  <div class="form-group" style="margin:0">
			    <label for="due" class="col-sm-3 control-label">Due Amount</label>
			    <div class="col-sm-9">
			      <input type="text" class="form-control" id="due" name="due" disabled="true" />					
			    </div>
			  </div> <!--/form-group-->		
			  <div class="form-group" style="margin:0">
			    <label for="payAmount" class="col-sm-3 control-label">Pay Amount</label>
			    <div class="col-sm-9">
			      <input type="text" class="form-control" id="payAmount" name="payAmount"/>					      
			    </div>
			  </div> <!--/form-group-->		
			  <div class="form-group" style="margin:0">
			    <label for="clientContact" class="col-sm-3 control-label">Payment Type</label>
			    <div class="col-sm-9">
			      <select class="form-control" name="paymentType" id="paymentType" >
			      	<option value="">~~SELECT~~</option>
			      	<option value="1">Cheque</option>
			      	<option value="2">Cash</option>
			      	<option value="3">Credit Card</option>
			      </select>
			    </div>
			  </div> <!--/form-group-->							  
			  <div class="form-group" style="margin:0">
			    <label for="clientContact" class="col-sm-3 control-label">Payment Status</label>
			    <div class="col-sm-9">
			      <select class="form-control" name="paymentStatus" id="paymentStatus">
			      	<option value="">~~SELECT~~</option>
			      	<option value="1">Full Payment</option>
			      	<option value="2">Advance Payment</option>
			      	<option value="3">No Payment</option>
			      </select>
			    </div>
			  </div> <!--/form-group-->							  				  
      	        
      </div> <!--/modal-body-->
      <div class="modal-footer">
      	<button type="button" class="btn btn-default" data-dismiss="modal"> <i class="glyphicon glyphicon-remove-sign"></i> Close</button>
        <button type="button" class="btn btn-primary" id="updatePaymentOrderBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-ok-sign"></i> Save changes</button>	
      </div>           
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- /edit order-->

<!-- remove order -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeOrderModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Order</h4>
      </div>
      <div class="modal-body">

      	<div class="removeOrderMessages"></div>

        <p>Do you really want to remove ?</p>
      </div>
      <div class="modal-footer removeProductFooter">
        <button type="button" class="btn btn-default" data-dismiss="modal"> <i class="glyphicon glyphicon-remove-sign"></i> Close</button>
        <button type="button" class="btn btn-primary" id="removeOrderBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- /remove order-->


<script src="custom/js/order.js"></script>

<?php require_once 'includes/footer.php'; ?>