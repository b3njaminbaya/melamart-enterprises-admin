<?php	
require_once 'core.php';

$productId = $_POST['productId'];

$sql = "SELECT product_id, product_name, product_image, brand_id, categories_id, quantity, rate, daily_rate, active, status FROM product WHERE product_id = $productId";
$result = $connect->query($sql);

if($result->num_rows > 0) { 
    $row = $result->fetch_array();
    
    // Create a proper response with all fields
    $response = array(
        'product_id' => $row[0],
        'product_name' => $row[1],
        'product_image' => $row[2],
        'brand_id' => $row[3],
        'categories_id' => $row[4],
        'quantity' => $row[5],
        'rate' => $row[6],          // Purchase price
        'daily_rate' => $row[7],    // Rental price per day
        'active' => $row[8],
        'status' => $row[9]
    );
}

$connect->close();
echo json_encode($response);
?>