<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $productId = intval($_POST['productId'] ?? 0);

    if($productId === 0) {
        $valid['messages'] = "Invalid product ID.";
        echo json_encode($valid); exit();
    }

    $stmt = $connect->prepare("UPDATE product SET active = 2, status = 2 WHERE product_id = ?");
    $stmt->bind_param("i", $productId);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Product removed successfully.";
    } else {
        $valid['messages'] = "Error while removing the product.";
    }
    $stmt->close();
    echo json_encode($valid);
}
