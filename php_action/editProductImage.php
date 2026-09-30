<?php
require_once 'core.php';
require_once 'product_input.php';

require_admin();
require_post();
csrf_verify();

$productId = (int)($_POST['productId'] ?? 0);
if($productId <= 0) {
    json_out(array('success' => false, 'messages' => 'Invalid product.'));
}
if(!isset($_FILES['editProductImage']) || $_FILES['editProductImage']['error'] === UPLOAD_ERR_NO_FILE) {
    json_out(array('success' => false, 'messages' => 'Choose a photo to upload.'));
}

list($url, $uploadError) = store_product_image($_FILES['editProductImage']);
if($uploadError) {
    json_out(array('success' => false, 'messages' => $uploadError));
}

$stmt = $connect->prepare("UPDATE product SET product_image = ? WHERE product_id = ?");
$stmt->bind_param('si', $url, $productId);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'Photo updated.', 'image_url' => preg_replace('#^\.\./#', '', $url)));
