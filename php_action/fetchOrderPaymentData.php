<?php	
require_once 'core.php';

$valid = array('success' => false, 'messages' => array());

if($_POST) {
    $orderId = $_POST['orderId'];
    
    $sql = "SELECT order_id, grand_total, paid, due, payment_type, payment_status, order_date 
            FROM orders 
            WHERE order_id = $orderId";
    $result = $connect->query($sql);
    
    if($result->num_rows > 0) {
        $row = $result->fetch_array();
        
        $valid['success'] = true;
        $valid['order'] = array(
            'order_id' => $row[0],
            'grand_total' => $row[1],
            'paid' => $row[2],
            'due' => $row[3],
            'payment_type' => $row[4],
            'payment_status' => $row[5],
            'order_date' => $row[6]
        );
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Order not found";
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>