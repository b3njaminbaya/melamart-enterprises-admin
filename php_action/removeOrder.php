<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $orderId = intval($_POST['orderId'] ?? 0);

    if($orderId === 0) {
        $valid['messages'] = "Invalid order ID.";
        echo json_encode($valid); exit();
    }

    $stmt1 = $connect->prepare("UPDATE orders SET order_status = 2 WHERE order_id = ?");
    $stmt1->bind_param("i", $orderId);
    $ok1 = $stmt1->execute();
    $stmt1->close();

    $stmt2 = $connect->prepare("UPDATE order_item SET order_item_status = 2 WHERE order_id = ?");
    $stmt2->bind_param("i", $orderId);
    $ok2 = $stmt2->execute();
    $stmt2->close();

    if($ok1 && $ok2) {
        $valid['success']  = true;
        $valid['messages'] = "Order removed successfully.";
    } else {
        $valid['messages'] = "Error while removing the order.";
    }
    echo json_encode($valid);
}
