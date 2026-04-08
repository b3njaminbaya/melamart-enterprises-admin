<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $productId     = intval($_POST['productId']          ?? 0);
    $productName   = trim($_POST['editProductName']       ?? '');
    $quantity      = intval($_POST['editQuantity']         ?? 0);
    $rate          = floatval($_POST['editRate']           ?? 0);
    $dailyRate     = floatval($_POST['editDailyRate']      ?? 0);
    $brandId       = intval($_POST['editBrandName']        ?? 0);
    $categoryId    = intval($_POST['editCategoryName']     ?? 0);
    $productStatus = intval($_POST['editProductStatus']    ?? 1);

    if($productId === 0 || $productName === '') {
        $valid['messages'] = "Product ID and name are required.";
        echo json_encode($valid); exit();
    }

    // Check if a new image was uploaded
    $fileInfo = $_FILES['editProductImage'] ?? null;
    $hasNewImage = $fileInfo && $fileInfo['error'] === UPLOAD_ERR_OK && $fileInfo['name'] !== '';

    if($hasNewImage) {
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
            "UPDATE product SET
                product_name = ?, product_image = ?, brand_id = ?, categories_id = ?,
                quantity = ?, rate = ?, daily_rate = ?, active = ?
             WHERE product_id = ?"
        );
        $stmt->bind_param("ssiiiddii", $productName, $url, $brandId, $categoryId, $quantity, $rate, $dailyRate, $productStatus, $productId);
    } else {
        $stmt = $connect->prepare(
            "UPDATE product SET
                product_name = ?, brand_id = ?, categories_id = ?,
                quantity = ?, rate = ?, daily_rate = ?, active = ?
             WHERE product_id = ?"
        );
        $stmt->bind_param("siiiddii", $productName, $brandId, $categoryId, $quantity, $rate, $dailyRate, $productStatus, $productId);
    }

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Product updated successfully.";
    } else {
        $valid['messages'] = "Error while updating the product.";
    }
    $stmt->close();
    echo json_encode($valid);
}
