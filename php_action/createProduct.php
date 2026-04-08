<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $productName   = trim($_POST['productName']    ?? '');
    $quantity      = intval($_POST['quantity']      ?? 0);
    $rate          = floatval($_POST['rate']        ?? 0);
    $dailyRate     = floatval($_POST['dailyRate']   ?? 0);
    $brandId       = intval($_POST['brandName']     ?? 0);   // select sends brand_id
    $categoryId    = intval($_POST['categoryName']  ?? 0);   // select sends categories_id
    $productStatus = intval($_POST['productStatus'] ?? 1);

    if($productName === '') {
        $valid['messages'] = "Product name is required.";
        echo json_encode($valid); exit();
    }

    $fileInfo = $_FILES['productImage'] ?? null;
    if(!$fileInfo || $fileInfo['error'] !== UPLOAD_ERR_OK) {
        $valid['messages'] = "Please select a valid image to upload.";
        echo json_encode($valid); exit();
    }

    $ext = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
    if(!in_array($ext, ['gif','jpg','jpeg','png'])) {
        $valid['messages'] = "Invalid image format. Allowed: gif, jpg, jpeg, png.";
        echo json_encode($valid); exit();
    }

    if(!is_uploaded_file($fileInfo['tmp_name'])) {
        $valid['messages'] = "Invalid file upload.";
        echo json_encode($valid); exit();
    }

    $url = '../assets/images/stock/' . uniqid(rand()) . '.' . $ext;
    if(!move_uploaded_file($fileInfo['tmp_name'], $url)) {
        $valid['messages'] = "Error while uploading image.";
        echo json_encode($valid); exit();
    }

    $stmt = $connect->prepare(
        "INSERT INTO product (product_name, product_image, brand_id, categories_id, quantity, rate, daily_rate, active, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)"
    );
    $stmt->bind_param("ssiidddi", $productName, $url, $brandId, $categoryId, $quantity, $rate, $dailyRate, $productStatus);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Product added successfully.";
    } else {
        $valid['messages'] = "Error while adding the product.";
    }
    $stmt->close();
    echo json_encode($valid);
}
