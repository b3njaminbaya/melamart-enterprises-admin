<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $brandId = intval($_POST['brandId'] ?? 0);

    if($brandId === 0) {
        $valid['messages'] = "Invalid brand ID.";
        echo json_encode($valid); exit();
    }

    $stmt = $connect->prepare("UPDATE brands SET brand_status = 2 WHERE brand_id = ?");
    $stmt->bind_param("i", $brandId);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Brand removed successfully.";
    } else {
        $valid['messages'] = "Error while removing the brand.";
    }
    $stmt->close();
    echo json_encode($valid);
}
