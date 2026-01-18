<?php 	

require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {	

	$productName 		= $_POST['productName'];
  $quantity 			= $_POST['quantity'];
  $rate 					= $_POST['rate'];
  $dailyRate 			= $_POST['dailyRate']; // NEW FIELD ADDED
  $brandName 			= $_POST['brandName'];
  $categoryName 	= $_POST['categoryName'];
  $productStatus 	= $_POST['productStatus'];

	$type = explode('.', $_FILES['productImage']['name']);
	$type = $type[count($type)-1];		
	$url = '../assests/images/stock/'.uniqid(rand()).'.'.$type;
	
	if(in_array($type, array('gif', 'jpg', 'jpeg', 'png', 'JPG', 'GIF', 'JPEG', 'PNG'))) {
		if(is_uploaded_file($_FILES['productImage']['tmp_name'])) {			
			if(move_uploaded_file($_FILES['productImage']['tmp_name'], $url)) {
				
				$sql = "INSERT INTO product (product_name, product_image, brand_id, categories_id, quantity, rate, daily_rate, active, status) 
				VALUES ('$productName', '$url', '$brandName', '$categoryName', '$quantity', '$rate', '$dailyRate', '$productStatus', 1)";

				if($connect->query($sql) === TRUE) {
					$valid['success'] = true;
					$valid['messages'] = "Successfully Added";	
				} else {
					$valid['success'] = false;
					$valid['messages'] = "Error while adding the product: " . $connect->error;
				}

			}	else {
				$valid['success'] = false;
				$valid['messages'] = "Error while uploading image";
			}	// /else	
		} // if
	} else {
		$valid['success'] = false;
		$valid['messages'] = "Invalid image format. Please upload gif, jpg, jpeg, or png files.";
	} // if in_array 		

	$connect->close();

	echo json_encode($valid);
 
} // /if $_POST