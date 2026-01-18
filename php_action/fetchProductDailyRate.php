<?php	
require_once 'core.php';

$valid = array('success' => false, 'messages' => array());

if($_POST) {
    $productId = $_POST['productId'];
    
    $sql = "SELECT daily_rate, quantity FROM product WHERE product_id = $productId AND status = 1 AND active = 1";
    $result = $connect->query($sql);
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $valid['success'] = true;
        $valid['daily_rate'] = $row['daily_rate'];
        $valid['quantity'] = $row['quantity'];
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Product not found";
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>