<?php 	

require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {
	$productId = $_POST['productId'];
	$productName 		= $_POST['editProductName']; 
  $quantity 			= $_POST['editQuantity'];
  $rate 					= $_POST['editRate'];
  $dailyRate 			= $_POST['editDailyRate']; // NEW FIELD ADDED
  $brandName 			= $_POST['editBrandName'];
  $categoryName 	= $_POST['editCategoryName'];
  $productStatus 	= $_POST['editProductStatus'];

	// Check if image is being updated
	if(isset($_FILES['editProductImage']) && $_FILES['editProductImage']['name'] != "") {
		$type = explode('.', $_FILES['editProductImage']['name']);
		$type = $type[count($type)-1];		
		$url = '../assests/images/stock/'.uniqid(rand()).'.'.$type;
		
		if(in_array($type, array('gif', 'jpg', 'jpeg', 'png', 'JPG', 'GIF', 'JPEG', 'PNG'))) {
			if(is_uploaded_file($_FILES['editProductImage']['tmp_name'])) {			
				if(move_uploaded_file($_FILES['editProductImage']['tmp_name'], $url)) {
					// Update with image
					$sql = "UPDATE product SET 
							product_name = '$productName', 
							product_image = '$url', 
							brand_id = '$brandName', 
							categories_id = '$categoryName', 
							quantity = '$quantity', 
							rate = '$rate', 
							daily_rate = '$dailyRate', 
							active = '$productStatus' 
							WHERE product_id = $productId";
				} else {
					$valid['success'] = false;
					$valid['messages'] = "Error while uploading image";
					echo json_encode($valid);
					exit();
				}
			}
		} else {
			$valid['success'] = false;
			$valid['messages'] = "Invalid image format";
			echo json_encode($valid);
			exit();
		}
	} else {
		// Update without changing image
		$sql = "UPDATE product SET 
				product_name = '$productName', 
				brand_id = '$brandName', 
				categories_id = '$categoryName', 
				quantity = '$quantity', 
				rate = '$rate', 
				daily_rate = '$dailyRate', 
				active = '$productStatus' 
				WHERE product_id = $productId";
	}

	if($connect->query($sql) === TRUE) {
		$valid['success'] = true;
		$valid['messages'] = "Successfully Updated";	
	} else {
		$valid['success'] = false;
		$valid['messages'] = "Error while updating product info: " . $connect->error;
	}

} // /$_POST
	 
$connect->close();

echo json_encode($valid);
 
?>