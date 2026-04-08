var manageOrderTable;

$(document).ready(function() {
	// Payment place GST label change
	$("#paymentPlace").change(function(){
		if($("#paymentPlace").val() == 2) {
			$(".gst").text("IGST 18%");
		} else {
			$(".gst").text("GST 18%");	
		}
	});

	var divRequest = $(".div-request").text();

	// top nav bar 
	$("#navOrder").addClass('active');

	if(divRequest == 'add')  {
		// add order	
		// top nav child bar 
		$('#topNavAddOrder').addClass('active');	

		// order date picker
		$("#orderDate").datepicker();
		$("#expectReturnDate").datepicker();

		// create order form function
		$("#createOrderForm").unbind('submit').bind('submit', function() {
			var form = $(this);

			$('.form-group').removeClass('has-error').removeClass('has-success');
			$('.text-danger').remove();
				
			var orderDate = $("#orderDate").val();
			var expectReturnDate = $("#expectReturnDate").val();
			var siteLocation = $("#siteLocation").val();
			var clientName = $("#clientName").val();
			var clientContact = $("#clientContact").val();
			var driverName = $("#driverName").val();
			var driverContact = $("#driverContact").val();
			var paid = $("#paid").val();
			var discount = $("#discount").val();
			var paymentType = $("#paymentType").val();
			var paymentStatus = $("#paymentStatus").val();
			var paymentPlace = $("#paymentPlace").val();
			var orderStatus = $("#orderStatus").val();
			var gstn = $("#gstn").val();

			// form validation 
			if(orderDate == "") {
				$("#orderDate").after('<p class="text-danger"> The Order Date field is required </p>');
				$('#orderDate').closest('.form-group').addClass('has-error');
			} else {
				$('#orderDate').closest('.form-group').addClass('has-success');
			} // /else

			if(expectReturnDate == "") {
				$("#expectReturnDate").after('<p class="text-danger"> The Expected Return Date field is required </p>');
				$('#expectReturnDate').closest('.form-group').addClass('has-error');
			} else {
				$('#expectReturnDate').closest('.form-group').addClass('has-success');
			} // /else

			if(siteLocation == "") {
				$("#siteLocation").after('<p class="text-danger"> The Site Location field is required </p>');
				$('#siteLocation').closest('.form-group').addClass('has-error');
			} else {
				$('#siteLocation').closest('.form-group').addClass('has-success');
			} // /else

			if(clientName == "") {
				$("#clientName").after('<p class="text-danger"> The Client Name field is required </p>');
				$('#clientName').closest('.form-group').addClass('has-error');
			} else {
				$('#clientName').closest('.form-group').addClass('has-success');
			} // /else

			if(clientContact == "") {
				$("#clientContact").after('<p class="text-danger"> The Client Contact field is required </p>');
				$('#clientContact').closest('.form-group').addClass('has-error');
			} else {
				$('#clientContact').closest('.form-group').addClass('has-success');
			} // /else

			if(driverName == "") {
				$("#driverName").after('<p class="text-danger"> The Driver Name field is required </p>');
				$('#driverName').closest('.form-group').addClass('has-error');
			} else {
				$('#driverName').closest('.form-group').addClass('has-success');
			} // /else

			if(driverContact == "") {
				$("#driverContact").after('<p class="text-danger"> The Driver Contact field is required </p>');
				$('#driverContact').closest('.form-group').addClass('has-error');
			} else {
				$('#driverContact').closest('.form-group').addClass('has-success');
			} // /else

			if(paid == "") {
				$("#paid").after('<p class="text-danger"> The Paid field is required </p>');
				$('#paid').closest('.form-group').addClass('has-error');
			} else {
				$('#paid').closest('.form-group').addClass('has-success');
			} // /else

			if(discount == "") {
				$("#discount").after('<p class="text-danger"> The Discount field is required </p>');
				$('#discount').closest('.form-group').addClass('has-error');
			} else {
				$('#discount').closest('.form-group').addClass('has-success');
			} // /else

			if(paymentType == "") {
				$("#paymentType").after('<p class="text-danger"> The Payment Type field is required </p>');
				$('#paymentType').closest('.form-group').addClass('has-error');
			} else {
				$('#paymentType').closest('.form-group').addClass('has-success');
			} // /else

			if(paymentStatus == "") {
				$("#paymentStatus").after('<p class="text-danger"> The Payment Status field is required </p>');
				$('#paymentStatus').closest('.form-group').addClass('has-error');
			} else {
				$('#paymentStatus').closest('.form-group').addClass('has-success');
			} // /else

			if(paymentPlace == "") {
				$("#paymentPlace").after('<p class="text-danger"> The Payment Place field is required </p>');
				$('#paymentPlace').closest('.form-group').addClass('has-error');
			} else {
				$('#paymentPlace').closest('.form-group').addClass('has-success');
			} // /else

			if(orderStatus == "") {
				$("#orderStatus").after('<p class="text-danger"> The Order Status field is required </p>');
				$('#orderStatus').closest('.form-group').addClass('has-error');
			} else {
				$('#orderStatus').closest('.form-group').addClass('has-success');
			} // /else

			// GSTN is now optional - removed validation

			// array validation
			var productName = document.getElementsByName('productName[]');				
			var validateProduct;
			for (var x = 0; x < productName.length; x++) {       			
				var productNameId = productName[x].id;	    	
				if(productName[x].value == ''){	    		    	
					$("#"+productNameId+"").after('<p class="text-danger"> Product Name Field is required!! </p>');
					$("#"+productNameId+"").closest('.form-group').addClass('has-error');	    		    	    	
				} else {      	
					$("#"+productNameId+"").closest('.form-group').addClass('has-success');	    		    		    	
				}          
			} // for

			for (var x = 0; x < productName.length; x++) {       						
				if(productName[x].value){	    		    		    	
					validateProduct = true;
				} else {      	
					validateProduct = false;
				}          
			} // for       		   	
			
			var quantity = document.getElementsByName('quantity[]');		   	
			var validateQuantity;
			for (var x = 0; x < quantity.length; x++) {       
				var quantityId = quantity[x].id;
				if(quantity[x].value == ''){	    	
					$("#"+quantityId+"").after('<p class="text-danger"> Quantity Field is required!! </p>');
					$("#"+quantityId+"").closest('.form-group').addClass('has-error');	    		    		    	
				} else {      	
					$("#"+quantityId+"").closest('.form-group').addClass('has-success');	    		    		    		    	
				} 
			}  // for

			for (var x = 0; x < quantity.length; x++) {       						
				if(quantity[x].value){	    		    		    	
					validateQuantity = true;
				} else {      	
					validateQuantity = false;
				}          
			} // for       	
			

			if(orderDate && expectReturnDate && siteLocation && clientName && clientContact && 
			   driverName && driverContact && paid !== "" && discount !== "" && paymentType && paymentStatus && 
			   paymentPlace && orderStatus) {
				if(validateProduct == true && validateQuantity == true) {
					// Prevent double submission
					var $submitBtn = $("#createOrderBtn");
					if($submitBtn.prop('disabled')) {
						return false; // Already submitting
					}
					
					// Disable submit button and show loading
					$submitBtn.prop('disabled', true).button('loading');
					
					// Clear any previous messages
					$(".text-danger").remove();
					$('.form-group').removeClass('has-error').removeClass('has-success');

					// Debug: Log form data
					console.log("Submitting order form...");
					console.log("Form data:", form.serialize());

					$.ajax({
						url : form.attr('action'),
						type: form.attr('method'),
						data: form.serialize(),					
						dataType: 'json',
						success:function(response) {
							console.log("Order creation response:", response);
							console.log(response);
							
							// Reset button
							$submitBtn.prop('disabled', false).button('reset');

							if(response.success == true) {
								// Show success toast notification immediately
								showToast(response.messages, 'success', 6000);
								
								// Also show in success messages area
								$(".success-messages").html('<div class="alert alert-success">'+
	            	'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
	            	'<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
	            	' <br /> <br /> <a type="button" onclick="printOrder('+response.order_id+')" class="btn btn-primary"> <i class="glyphicon glyphicon-print"></i> Print </a>'+
	            	'<a href="orders.php?o=add" class="btn btn-default" style="margin-left:10px;"> <i class="glyphicon glyphicon-plus-sign"></i> Add New Order </a>'+
	            	
	   		       '</div>');
								
								$("html, body, div.panel, div.pane-body").animate({scrollTop: '0px'}, 100);

								// Disable the submit button footer
								$(".submitButtonFooter").addClass('div-hide');
								// Remove the product row buttons
								$(".removeProductRowBtn").addClass('div-hide');
								
							} else {
								// Show error toast
								var errorMsg = response.messages || 'Error creating order. Please try again.';
								
								// Include debug info if available
								if(response.debug && Object.keys(response.debug).length > 0) {
									console.error("Debug info:", response.debug);
									errorMsg += " (Check console for details)";
								}
								
								showToast(errorMsg, 'error', 8000);
								
								// Show detailed error in alert for debugging
								if(response.debug) {
									alert(errorMsg + "\n\nDebug Info:\n" + JSON.stringify(response.debug, null, 2));
								} else {
									alert(errorMsg);
								}
							}
						}, // /response
						error: function(xhr, status, error) {
							// Reset button on error
							$submitBtn.prop('disabled', false).button('reset');
							
							// Show error toast
							var errorMsg = 'Network error. Please check your connection and try again.';
							try {
								var response = JSON.parse(xhr.responseText);
								if(response.messages) {
									errorMsg = response.messages;
								}
							} catch(e) {
								if(xhr.responseText) {
									errorMsg = xhr.responseText.substring(0, 200);
								}
							}
							
							// Log full error for debugging
							console.error('Order creation error:', {
								status: status,
								error: error,
								responseText: xhr.responseText,
								statusCode: xhr.status,
								readyState: xhr.readyState
							});
							
							showToast(errorMsg, 'error', 5000);
						}
					}); // /ajax
				} // if array validate is true
			} // /if field validate is true
			

			return false;
		}); // /create order form function	
	
	} else if(divRequest == 'manord') {
		// top nav child bar 
		$('#topNavManageOrder').addClass('active');

		manageOrderTable = $("#manageOrderTable").DataTable({
			'ajax': 'php_action/fetchOrder.php',
			'order': [],
			'pageLength': 10,
			'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]]
		});		
					
	} else if(divRequest == 'editOrd') {
		$("#orderDate").datepicker();
		$("#expectReturnDate").datepicker();
		$("#returnedDate").datepicker();

		// edit order form function
		$("#editOrderForm").unbind('submit').bind('submit', function() {
			var form = $(this);

			$('.form-group').removeClass('has-error').removeClass('has-success');
			$('.text-danger').remove();
				
			var orderDate = $("#orderDate").val();
			var expectReturnDate = $("#expectReturnDate").val();
			var returnedDate = $("#returnedDate").val();
			var siteLocation = $("#siteLocation").val();
			var clientName = $("#clientName").val();
			var clientContact = $("#clientContact").val();
			var driverName = $("#driverName").val();
			var driverContact = $("#driverContact").val();
			var returnedBy = $("#returnedBy").val();
			var returnedByContact = $("#returnedByContact").val();
			var approvedBy = $("#approvedBy").val();
			var paid = $("#paid").val();
			var discount = $("#discount").val();
			var paymentType = $("#paymentType").val();
			var paymentStatus = $("#paymentStatus").val();
			var paymentPlace = $("#paymentPlace").val();
			var orderStatus = $("#orderStatus").val();
			var gstn = $("#gstn").val();

			// form validation 
			if(orderDate == "") {
				$("#orderDate").after('<p class="text-danger"> The Order Date field is required </p>');
				$('#orderDate').closest('.form-group').addClass('has-error');
			} else {
				$('#orderDate').closest('.form-group').addClass('has-success');
			} // /else

			if(expectReturnDate == "") {
				$("#expectReturnDate").after('<p class="text-danger"> The Expected Return Date field is required </p>');
				$('#expectReturnDate').closest('.form-group').addClass('has-error');
			} else {
				$('#expectReturnDate').closest('.form-group').addClass('has-success');
			} // /else

			if(siteLocation == "") {
				$("#siteLocation").after('<p class="text-danger"> The Site Location field is required </p>');
				$('#siteLocation').closest('.form-group').addClass('has-error');
			} else {
				$('#siteLocation').closest('.form-group').addClass('has-success');
			} // /else

			if(clientName == "") {
				$("#clientName").after('<p class="text-danger"> The Client Name field is required </p>');
				$('#clientName').closest('.form-group').addClass('has-error');
			} else {
				$('#clientName').closest('.form-group').addClass('has-success');
			} // /else

			if(clientContact == "") {
				$("#clientContact").after('<p class="text-danger"> The Client Contact field is required </p>');
				$('#clientContact').closest('.form-group').addClass('has-error');
			} else {
				$('#clientContact').closest('.form-group').addClass('has-success');
			} // /else

			if(driverName == "") {
				$("#driverName").after('<p class="text-danger"> The Driver Name field is required </p>');
				$('#driverName').closest('.form-group').addClass('has-error');
			} else {
				$('#driverName').closest('.form-group').addClass('has-success');
			} // /else

			if(driverContact == "") {
				$("#driverContact").after('<p class="text-danger"> The Driver Contact field is required </p>');
				$('#driverContact').closest('.form-group').addClass('has-error');
			} else {
				$('#driverContact').closest('.form-group').addClass('has-success');
			} // /else

			if(paid == "") {
				$("#paid").after('<p class="text-danger"> The Paid field is required </p>');
				$('#paid').closest('.form-group').addClass('has-error');
			} else {
				$('#paid').closest('.form-group').addClass('has-success');
			} // /else

			if(discount == "") {
				$("#discount").after('<p class="text-danger"> The Discount field is required </p>');
				$('#discount').closest('.form-group').addClass('has-error');
			} else {
				$('#discount').closest('.form-group').addClass('has-success');
			} // /else

			if(paymentType == "") {
				$("#paymentType").after('<p class="text-danger"> The Payment Type field is required </p>');
				$('#paymentType').closest('.form-group').addClass('has-error');
			} else {
				$('#paymentType').closest('.form-group').addClass('has-success');
			} // /else

			if(paymentStatus == "") {
				$("#paymentStatus").after('<p class="text-danger"> The Payment Status field is required </p>');
				$('#paymentStatus').closest('.form-group').addClass('has-error');
			} else {
				$('#paymentStatus').closest('.form-group').addClass('has-success');
			} // /else

			if(paymentPlace == "") {
				$("#paymentPlace").after('<p class="text-danger"> The Payment Place field is required </p>');
				$('#paymentPlace').closest('.form-group').addClass('has-error');
			} else {
				$('#paymentPlace').closest('.form-group').addClass('has-success');
			} // /else

			if(orderStatus == "") {
				$("#orderStatus").after('<p class="text-danger"> The Order Status field is required </p>');
				$('#orderStatus').closest('.form-group').addClass('has-error');
			} else {
				$('#orderStatus').closest('.form-group').addClass('has-success');
			} // /else

			// GSTN is now optional - removed validation

			// array validation
			var productName = document.getElementsByName('productName[]');				
			var validateProduct;
			for (var x = 0; x < productName.length; x++) {       			
				var productNameId = productName[x].id;	    	
				if(productName[x].value == ''){	    		    	
					$("#"+productNameId+"").after('<p class="text-danger"> Product Name Field is required!! </p>');
					$("#"+productNameId+"").closest('.form-group').addClass('has-error');	    		    	    	
				} else {      	
					$("#"+productNameId+"").closest('.form-group').addClass('has-success');	    		    		    	
				}          
			} // for

			for (var x = 0; x < productName.length; x++) {       						
				if(productName[x].value){	    		    		    	
					validateProduct = true;
				} else {      	
					validateProduct = false;
				}          
			} // for       		   	
			
			var quantity = document.getElementsByName('quantity[]');		   	
			var validateQuantity;
			for (var x = 0; x < quantity.length; x++) {       
				var quantityId = quantity[x].id;
				if(quantity[x].value == ''){	    	
					$("#"+quantityId+"").after('<p class="text-danger"> Quantity Field is required!! </p>');
					$("#"+quantityId+"").closest('.form-group').addClass('has-error');	    		    		    	
				} else {      	
					$("#"+quantityId+"").closest('.form-group').addClass('has-success');	    		    		    		    	
				} 
			}  // for

			for (var x = 0; x < quantity.length; x++) {       						
				if(quantity[x].value){	    		    		    	
					validateQuantity = true;
				} else {      	
					validateQuantity = false;
				}          
			} // for       	
			

			if(orderDate && expectReturnDate && siteLocation && clientName && clientContact && 
			   driverName && driverContact && paid !== "" && discount !== "" && paymentType && paymentStatus && 
			   paymentPlace && orderStatus) {
				if(validateProduct == true && validateQuantity == true) {
					// edit order button
					// $("#editOrderBtn").button('loading');

					$.ajax({
						url : form.attr('action'),
						type: form.attr('method'),
						data: form.serialize(),					
						dataType: 'json',
						success:function(response) {
							console.log(response);
							// reset button
							$("#editOrderBtn").button('reset');
							
							$(".text-danger").remove();
							$('.form-group').removeClass('has-error').removeClass('has-success');

							if(response.success == true) {
								
								// success message
								$(".success-messages").html('<div class="alert alert-success">'+
	            	'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
	            	'<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +	            		            		            	
	   		       '</div>');
								
								$("html, body, div.panel, div.pane-body").animate({scrollTop: '0px'}, 100);

								// disabled te modal footer button
								$(".editButtonFooter").addClass('div-hide');
								// remove the product row
								$(".removeProductRowBtn").addClass('div-hide');
								
							} else {
								alert(response.messages);								
							}
						} // /response
					}); // /ajax
				} // if array validate is true
			} // /if field validate is true
			

			return false;
		}); // /edit order form function	
	} 	

}); // /document

// Calculate total based on daily rate and rental days
function calculateRentalTotal(row = null) {
    if(row) {
        var dailyRate = $("#dailyRate"+row).val();
        var rentalDays = $("#rentalDays"+row).val();
        var quantity = $("#quantity"+row).val();
        
        if(dailyRate && rentalDays && quantity) {
            var total = Number(dailyRate) * Number(rentalDays) * Number(quantity);
            total = total.toFixed(2);
            $("#total"+row).val(total);
            $("#totalValue"+row).val(total);
            
            subAmount();
        }
    }
}

// print order function
function printOrder(orderId = null) {
	if(orderId) {		
			
		$.ajax({
			url: 'php_action/printOrder.php',
			type: 'post',
			data: {orderId: orderId},
			dataType: 'text',
			success:function(response) {
				
				var mywindow = window.open('', 'Stock Management System', 'height=400,width=600');
        mywindow.document.write('<html><head><title>Order Invoice</title>');        
        mywindow.document.write('</head><body>');
        mywindow.document.write(response);
        mywindow.document.write('</body></html>');

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10
        mywindow.resizeTo(screen.width, screen.height);
				setTimeout(function() {
					mywindow.print();
					mywindow.close();
				}, 1250);
				
			}// /success function
		}); // /ajax function to fetch the printable order
	} // /if orderId
} // /print order function

function addRow() {
	$("#addRowBtn").button("loading");

	var tableLength = $("#productTable tbody tr").length;

	var tableRow;
	var arrayNumber;
	var count;

	if(tableLength > 0) {		
		tableRow = $("#productTable tbody tr:last").attr('id');
		arrayNumber = $("#productTable tbody tr:last").attr('class');
		count = tableRow.substring(3);	
		count = Number(count) + 1;
		arrayNumber = Number(arrayNumber) + 1;					
	} else {
		// no table row
		count = 1;
		arrayNumber = 0;
	}

	$.ajax({
		url: 'php_action/fetchProductData.php',
		type: 'post',
		dataType: 'json',
		success:function(response) {
			$("#addRowBtn").button("reset");			

			var tr = '<tr id="row'+count+'" class="'+arrayNumber+'">'+			  				
			'<td>'+
				'<div class="form-group">'+
				'<select class="form-control" name="productName[]" id="productName'+count+'" onchange="getProductData('+count+')" >'+
					'<option value="">~~SELECT~~</option>';
					$.each(response, function(index, value) {
						tr += '<option value="'+value[0]+'">'+value[1]+'</option>';							
					});					
				tr += '</select>'+
				'</div>'+
			'</td>'+
			'<td style="padding-left:20px;">'+
				'<input type="text" name="dailyRate[]" id="dailyRate'+count+'" autocomplete="off" disabled="true" class="form-control" />'+
				'<input type="hidden" name="dailyRateValue[]" id="dailyRateValue'+count+'" autocomplete="off" class="form-control" />'+
			'</td>'+
			'<td style="padding-left:20px;">'+
				'<div class="form-group" style="margin:0">'+
				'<input type="number" name="rentalDays[]" id="rentalDays'+count+'" onkeyup="calculateRentalTotal('+count+')" autocomplete="off" class="form-control" min="1" value="1" />'+
				'</div>'+
			'</td>'+
			'<td style="padding-left:20px;">'+
				'<div class="form-group" style="margin:0">'+
				'<p id="available_quantity'+count+'"></p>'+
				'</div>'+
			'</td>'+
			'<td style="padding-left:20px;">'+
				'<div class="form-group" style="margin:0">'+
				'<input type="number" name="quantity[]" id="quantity'+count+'" onkeyup="calculateRentalTotal('+count+')" autocomplete="off" class="form-control" min="1" value="1" />'+
				'</div>'+
			'</td>'+
			'<td style="padding-left:20px;">'+
				'<input type="text" name="total[]" id="total'+count+'" autocomplete="off" class="form-control" disabled="true" />'+
				'<input type="hidden" name="totalValue[]" id="totalValue'+count+'" autocomplete="off" class="form-control" />'+
			'</td>'+
			'<td>'+
				'<button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow('+count+')"><i class="glyphicon glyphicon-trash"></i></button>'+
			'</td>'+
		'</tr>';
			if(tableLength > 0) {							
				$("#productTable tbody tr:last").after(tr);
			} else {				
				$("#productTable tbody").append(tr);
			}		

		} // /success
	});	// get the product data

} // /add row

function removeProductRow(row = null) {
	if(row) {
		$("#row"+row).remove();
		subAmount();
	} else {
		alert('error! Refresh the page again');
	}
}

// select on product data - FIXED VERSION
function getProductData(row = null) {
    if(row) {
        var productId = $("#productName"+row).val();        
        
        if(productId == "") {
            $("#dailyRate"+row).val("");
            $("#rentalDays"+row).val(1);
            $("#quantity"+row).val("");                        
            $("#total"+row).val("");
        } else {
            console.log("Fetching product data for ID:", productId); // Debug
            
            $.ajax({
                url: 'php_action/fetchSelectedProduct.php',
                type: 'post',
                data: {productId : productId},
                dataType: 'json',
                success:function(response) {
                    console.log("Product Data Response:", response); // Debug
                    
                    // Check if we have daily_rate
                    var dailyRate = response.daily_rate || 0;
                    if(dailyRate == 0) {
                        console.warn("No daily_rate found for product:", response.product_name);
                        dailyRate = response.rate || 0; // Fallback to purchase rate
                    }
                    
                    console.log("Setting daily rate to:", dailyRate);
                    
                    // setting the daily rate value
                    $("#dailyRate"+row).val(dailyRate);
                    $("#dailyRateValue"+row).val(dailyRate);
                    
                    // set rental days to 1 by default
                    var currentRentalDays = $("#rentalDays"+row).val();
                    if(!currentRentalDays || currentRentalDays < 1) {
                        $("#rentalDays"+row).val(1);
                    }
                    
                    // set quantity to 1 by default
                    var currentQuantity = $("#quantity"+row).val();
                    if(!currentQuantity || currentQuantity < 1) {
                        $("#quantity"+row).val(1);
                    }
                    
                    // show available quantity
                    $("#available_quantity"+row).text(response.quantity || 0);
                    
                    // calculate initial total
                    var rentalDays = parseFloat($("#rentalDays"+row).val()) || 1;
                    var quantity = parseFloat($("#quantity"+row).val()) || 1;
                    var total = dailyRate * rentalDays * quantity;
                    total = total.toFixed(2);
                    
                    console.log("Calculating total:", dailyRate, "×", rentalDays, "×", quantity, "=", total);
                    
                    $("#total"+row).val(total);
                    $("#totalValue"+row).val(total);
            
                    // recalculate subtotals
                    subAmount();
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching product:", error);
                    alert("Error loading product data. Please check console.");
                }
            });
        }
    } else {
        alert('no row! please refresh the page');
    }
}


// table total
function getTotal(row = null) {
    if(row) {
        var dailyRate = parseFloat($("#dailyRate"+row).val()) || 0;
        var rentalDays = parseFloat($("#rentalDays"+row).val()) || 0;
        var quantity = parseFloat($("#quantity"+row).val()) || 0;
        
        console.log("Calculating row", row, ":", dailyRate, rentalDays, quantity); // Debug log
        
        if(dailyRate && rentalDays && quantity) {
            var total = dailyRate * rentalDays * quantity;
            total = total.toFixed(2);
            $("#total"+row).val(total);
            $("#totalValue"+row).val(total);
            
            subAmount();
        }
    } else {
        alert('no row !! please refresh the page');
    }
}

// Calculate total based on daily rate and rental days
function calculateRentalTotal(row = null) {
    if(row) {
        var dailyRate = parseFloat($("#dailyRate"+row).val()) || 0;
        var rentalDays = parseFloat($("#rentalDays"+row).val()) || 1;
        var quantity = parseFloat($("#quantity"+row).val()) || 1;
        
        console.log("Rental Calculation - Row:", row);
        console.log("Daily Rate:", dailyRate);
        console.log("Rental Days:", rentalDays);
        console.log("Quantity:", quantity);
        
        if(dailyRate > 0 && rentalDays > 0 && quantity > 0) {
            var total = dailyRate * rentalDays * quantity;
            total = total.toFixed(2);
            console.log("Total calculated:", total);
            
            $("#total"+row).val(total);
            $("#totalValue"+row).val(total);
            
            subAmount();
        } else {
            console.log("Invalid values for calculation");
            $("#total"+row).val("0.00");
            $("#totalValue"+row).val("0.00");
            subAmount();
        }
    }
}

// Enable/disable returned date based on order status
function toggleReturnedDate() {
    var orderStatus = $("#orderStatus").val();
    var returnedDateField = $("#returnedDate");
    
    if(orderStatus == 1) { // Completed
        returnedDateField.prop('disabled', false);
        returnedDateField.prop('required', true);
        if(!returnedDateField.val()) {
            // Set default to today if empty
            returnedDateField.val(getTodayDate());
        }
    } else {
        returnedDateField.prop('disabled', true);
        returnedDateField.prop('required', false);
    }
}

// Get today's date in YYYY-MM-DD format
function getTodayDate() {
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
    var yyyy = today.getFullYear();
    return yyyy + '-' + mm + '-' + dd;
}

// Call on page load and when order status changes
$(document).ready(function() {
    toggleReturnedDate();
    $("#orderStatus").change(function() {
        toggleReturnedDate();
    });
});

function subAmount() {
	var tableProductLength = $("#productTable tbody tr").length;
	var totalSubAmount = 0;
	for(x = 0; x < tableProductLength; x++) {
		var tr = $("#productTable tbody tr")[x];
		var count = $(tr).attr('id');
		count = count.substring(3);

		totalSubAmount = Number(totalSubAmount) + Number($("#total"+count).val());
	} // /for

	totalSubAmount = totalSubAmount.toFixed(2);

	// sub total (no VAT)
	$("#subTotal").val(totalSubAmount);
	$("#subTotalValue").val(totalSubAmount);

	// VAT removed - set to 0
	$("#vat").val("0.00");
	$("#vatValue").val("0.00");

	// total amount (same as subtotal, no VAT)
	var totalAmount = Number($("#subTotal").val());
	totalAmount = totalAmount.toFixed(2);
	$("#totalAmount").val(totalAmount);
	$("#totalAmountValue").val(totalAmount);

	// Apply discount if any
	var discount = $("#discount").val();
	if(discount && discount > 0) {
		var grandTotal = Number($("#totalAmount").val()) - Number(discount);
		grandTotal = grandTotal.toFixed(2);
		$("#grandTotal").val(grandTotal);
		$("#grandTotalValue").val(grandTotal);
	} else {
		$("#grandTotal").val(totalAmount);
		$("#grandTotalValue").val(totalAmount);
	} // /else discount	

	// Calculate due amount
	var paidAmount = $("#paid").val();
	if(paidAmount && paidAmount > 0) {
		paidAmount =  Number($("#grandTotal").val()) - Number(paidAmount);
		paidAmount = paidAmount.toFixed(2);
		$("#due").val(paidAmount);
		$("#dueValue").val(paidAmount);
	} else {	
		$("#due").val($("#grandTotal").val());
		$("#dueValue").val($("#grandTotal").val());
	} // else

} // /sub total amount

function discountFunc() {
	var discount = $("#discount").val() || 0;
 	var totalAmount = Number($("#totalAmount").val()) || 0;
 	totalAmount = totalAmount.toFixed(2);

 	var grandTotal;
 	if(totalAmount > 0) { 	
 		grandTotal = Number($("#totalAmount").val()) - Number(discount);
 		if(grandTotal < 0) grandTotal = 0;
 		grandTotal = grandTotal.toFixed(2);

 		$("#grandTotal").val(grandTotal);
 		$("#grandTotalValue").val(grandTotal);
 	} else {
 		$("#grandTotal").val("0.00");
 		$("#grandTotalValue").val("0.00");
 	}

 	var paid = $("#paid").val() || 0;

 	var dueAmount; 	
 	if(paid > 0) {
 		dueAmount = Number($("#grandTotal").val()) - Number(paid);
 		if(dueAmount < 0) dueAmount = 0;
 		dueAmount = dueAmount.toFixed(2);

 		$("#due").val(dueAmount);
 		$("#dueValue").val(dueAmount);
 	} else {
 		$("#due").val($("#grandTotal").val());
 		$("#dueValue").val($("#grandTotal").val());
 	}

} // /discount function

function paidAmount() {
	var grandTotal = $("#grandTotal").val();

	if(grandTotal) {
		var dueAmount = Number($("#grandTotal").val()) - Number($("#paid").val());
		dueAmount = dueAmount.toFixed(2);
		$("#due").val(dueAmount);
		$("#dueValue").val(dueAmount);
	} // /if
} // /paid amoutn function


function resetOrderForm() {
	// reset the input field
	$("#createOrderForm")[0].reset();
	// remove remove text danger
	$(".text-danger").remove();
	// remove form group error 
	$(".form-group").removeClass('has-success').removeClass('has-error');
	// reset GST label
	$(".gst").text("GST 18%");
} // /reset order form


// remove order from server
function removeOrder(orderId = null) {
	if(orderId) {
		$("#removeOrderBtn").unbind('click').bind('click', function() {
			$("#removeOrderBtn").button('loading');

			$.ajax({
				url: 'php_action/removeOrder.php',
				type: 'post',
				data: {orderId : orderId},
				dataType: 'json',
				success:function(response) {
					$("#removeOrderBtn").button('reset');

					if(response.success == true) {

						manageOrderTable.ajax.reload(null, false);
						// hide modal
						$("#removeOrderModal").modal('hide');
						// success messages
						$("#success-messages").html('<div class="alert alert-success">'+
	            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
	            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
	          '</div>');

						// remove the mesages
	          $(".alert-success").delay(500).show(10, function() {
							$(this).delay(3000).hide(10, function() {
								$(this).remove();
							});
						}); // /.alert	          

					} else {
						// error messages
						$(".removeOrderMessages").html('<div class="alert alert-warning">'+
	            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
	            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
	          '</div>');

						// remove the mesages
	          $(".alert-success").delay(500).show(10, function() {
							$(this).delay(3000).hide(10, function() {
								$(this).remove();
							});
						}); // /.alert	          
					} // /else

				} // /success
			});  // /ajax function to remove the order

		}); // /remove order button clicked
		

	} else {
		alert('error! refresh the page again');
	}
}
// /remove order from server

// Payment ORDER - FIXED VERSION
function paymentOrder(orderId = null) {
    if(orderId) {
        console.log("Opening payment modal for Order ID:", orderId);
        
        // Reset form first
        $("#due").val('');
        $("#payAmount").val('');
        $("#paymentType").val('');
        $("#paymentStatus").val('');
        
        // Clear previous errors
        $('.text-danger').remove();
        $('.form-group').removeClass('has-error').removeClass('has-success');

        $.ajax({
            url: 'php_action/fetchOrderPaymentData.php', // Changed to new endpoint
            type: 'post',
            data: {orderId: orderId},
            dataType: 'json',
            success: function(response) {
                console.log("Payment Order Response:", response);
                
                if(response.success == true && response.order) {
                    var order = response.order;
                    
                    // Populate due amount
                    var dueAmount = parseFloat(order.due) || 0;
                    $("#due").val(dueAmount.toFixed(2));
                    
                    // Populate pay amount (default to due amount)
                    $("#payAmount").val(dueAmount.toFixed(2));
                    
                    // Set payment type if exists
                    if(order.payment_type) {
                        $("#paymentType").val(order.payment_type);
                    }
                    
                    // Set payment status if exists
                    if(order.payment_status) {
                        $("#paymentStatus").val(order.payment_status);
                    }
                    
                    console.log("Due Amount:", dueAmount);
                    
                    // Update payment
                    $("#updatePaymentOrderBtn").unbind('click').bind('click', function() {
                        // Add client-side validation before AJAX call
						var payAmount = parseFloat($("#payAmount").val()) || 0;
						var currentDue = parseFloat($("#due").val()) || 0;
						var grandTotal = parseFloat(order.grand_total) || 0;

						// Check if payment exceeds due amount
						if(payAmount > currentDue) {
							$("#payAmount").after('<p class="text-danger">Payment cannot exceed due amount of ₹' + currentDue.toFixed(2) + '</p>');
							$("#payAmount").closest('.form-group').addClass('has-error');
							$("#updatePaymentOrderBtn").button('reset');
							return false;
						}

						// Check if payment is positive
						if(payAmount <= 0) {
							$("#payAmount").after('<p class="text-danger">Payment amount must be greater than 0</p>');
							$("#payAmount").closest('.form-group').addClass('has-error');
							$("#updatePaymentOrderBtn").button('reset');
							return false;
}
                        var paymentType = $("#paymentType").val();
                        var paymentStatus = $("#paymentStatus").val();
                        
                        // Validation
                        var hasError = false;
                        
                        if(isNaN(payAmount) || payAmount <= 0) {
                            $("#payAmount").after('<p class="text-danger">Valid Pay Amount is required</p>');
                            $("#payAmount").closest('.form-group').addClass('has-error');
                            hasError = true;
                        } else {
                            $("#payAmount").closest('.form-group').removeClass('has-error').addClass('has-success');
                        }

                        if(!paymentType) {
                            $("#paymentType").after('<p class="text-danger">Payment Type is required</p>');
                            $("#paymentType").closest('.form-group').addClass('has-error');
                            hasError = true;
                        } else {
                            $("#paymentType").closest('.form-group').removeClass('has-error').addClass('has-success');
                        }

                        if(!paymentStatus) {
                            $("#paymentStatus").after('<p class="text-danger">Payment Status is required</p>');
                            $("#paymentStatus").closest('.form-group').addClass('has-error');
                            hasError = true;
                        } else {
                            $("#paymentStatus").closest('.form-group').removeClass('has-error').addClass('has-success');
                        }

                        if(!hasError) {
                            $("#updatePaymentOrderBtn").button('loading');
                            
                            // Calculate new paid and due amounts
							var currentPaid = parseFloat(order.paid) || 0;
							var currentDue = parseFloat(order.due) || 0;
							var payAmount = parseFloat($("#payAmount").val()) || 0;

							// VALIDATION: Payment should not exceed due amount
							if(payAmount > currentDue) {
								alert("Payment amount (₹" + payAmount.toFixed(2) + ") cannot exceed due amount (₹" + currentDue.toFixed(2) + ")");
								$("#payAmount").val(currentDue.toFixed(2)); // Auto-correct to max due
								payAmount = currentDue;
							}

							var newPaid = currentPaid + payAmount;
							var newDue = currentDue - payAmount;
							if(newDue < 0) newDue = 0;
                            
                            console.log("Payment Update:", {
                                orderId: orderId,
                                payAmount: payAmount,
                                paymentType: paymentType,
                                paymentStatus: paymentStatus,
                                currentPaid: currentPaid,
                                newPaid: newPaid,
                                newDue: newDue
                            });
                            
                            $.ajax({
                                url: 'php_action/editPayment.php',
                                type: 'post',
                                data: {
                                    orderId: orderId,
                                    payAmount: payAmount,
                                    paymentType: paymentType,
                                    paymentStatus: paymentStatus,
                                    currentPaid: currentPaid,
                                    newPaid: newPaid,
                                    newDue: newDue,
                                    grandTotal: order.grand_total
                                },
                                dataType: 'json',
                                success: function(response) {
                                    console.log("Payment Update Response:", response);
                                    $("#updatePaymentOrderBtn").button('reset');

                                    // remove error
                                    $('.text-danger').remove();
                                    $('.form-group').removeClass('has-error').removeClass('has-success');

                                    if(response.success == true) {
                                        $("#paymentOrderModal").modal('hide');
                                        
                                        // Show success message
                                        $("#success-messages").html('<div class="alert alert-success">'+
                                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                                        '</div>');

                                        // remove the mesages after delay
                                        $(".alert-success").delay(500).show(10, function() {
                                            $(this).delay(3000).hide(10, function() {
                                                $(this).remove();
                                            });
                                        });

                                        // refresh the manage order table
                                        if(typeof manageOrderTable !== 'undefined') {
                                            manageOrderTable.ajax.reload(null, false);
                                        } else {
                                            location.reload(); // Fallback
                                        }

                                    } else {
                                        // Show error in modal
                                        $(".paymentOrderMessages").html('<div class="alert alert-danger">'+
                                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                            '<strong><i class="glyphicon glyphicon-warning-sign"></i></strong> '+ response.messages +
                                        '</div>');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    $("#updatePaymentOrderBtn").button('reset');
                                    console.error("Payment update error:", error);
                                    alert("Error updating payment. Please try again.");
                                }
                            });
                        }
                        return false;
                    });

                } else {
                    alert("Error loading order data: " + (response.messages || "Unknown error"));
                    console.error("Failed to load order data:", response);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching order data:", error);
                alert("Error loading order information. Please try again.");
            }
        });
    } else {
        alert('Error! Refresh the page again');
    }
}