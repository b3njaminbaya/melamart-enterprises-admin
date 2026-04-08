<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $brandName   = trim($_POST['editBrandName']   ?? '');
    $brandStatus = intval($_POST['editBrandStatus'] ?? 1);
    $brandId     = intval($_POST['brandId']         ?? 0);

    if($brandName === '' || $brandId === 0) {
        $valid['messages'] = "Brand name and ID are required.";
        echo json_encode($valid); exit();
    }

    $stmt = $connect->prepare(
        "UPDATE brands SET brand_name = ?, brand_active = ? WHERE brand_id = ?"
    );
    $stmt->bind_param("sii", $brandName, $brandStatus, $brandId);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Brand updated successfully.";
    } else {
        $valid['messages'] = "Error while updating the brand.";
    }
    $stmt->close();
    echo json_encode($valid);
}
