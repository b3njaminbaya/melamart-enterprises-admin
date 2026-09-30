<?php
require_once 'core.php';
require_once 'product_input.php';

require_admin();
require_post();
csrf_verify();

list($p, $error) = read_product_input('');
if($error) {
    json_out(array('success' => false, 'messages' => $error));
}

// Photo is optional; without one the default placeholder is shown.
$url = '../assets/images/photo_default.png';
if(isset($_FILES['productImage']) && $_FILES['productImage']['error'] !== UPLOAD_ERR_NO_FILE) {
    list($url, $uploadError) = store_product_image($_FILES['productImage']);
    if($uploadError) {
        json_out(array('success' => false, 'messages' => $uploadError));
    }
}

$stmt = $connect->prepare(
    "INSERT INTO product (product_name, product_image, brand_id, categories_id, quantity, rate, daily_rate, active, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)"
);
$qty = (string)$p['quantity'];
$rate = number_format($p['rate'], 2, '.', '');
$stmt->bind_param("ssiissdi", $p['name'], $url, $p['brand_id'], $p['category_id'], $qty, $rate, $p['daily_rate'], $p['active']);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => $p['name'] . ' has been added.'));
