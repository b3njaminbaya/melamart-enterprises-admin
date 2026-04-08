<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $brandName   = trim($_POST['brandName']   ?? '');
    $brandStatus = intval($_POST['brandStatus'] ?? 1);

    if($brandName === '') {
        $valid['messages'] = "Brand name is required.";
        echo json_encode($valid); exit();
    }

    $stmt = $connect->prepare(
        "INSERT INTO brands (brand_name, brand_active, brand_status) VALUES (?, ?, 1)"
    );
    $stmt->bind_param("si", $brandName, $brandStatus);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Brand added successfully.";
    } else {
        $valid['messages'] = "Error while adding the brand.";
    }
    $stmt->close();
    echo json_encode($valid);
}
