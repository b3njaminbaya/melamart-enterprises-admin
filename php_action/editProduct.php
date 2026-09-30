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

list($p, $error) = read_product_input('edit');
if($error) {
    json_out(array('success' => false, 'messages' => $error));
}

$stmt = $connect->prepare(
    "UPDATE product SET product_name = ?, brand_id = ?, categories_id = ?, quantity = ?, rate = ?, daily_rate = ?, active = ?
     WHERE product_id = ? AND status = 1"
);
$qty = (string)$p['quantity'];
$rate = number_format($p['rate'], 2, '.', '');
$stmt->bind_param("siissdii", $p['name'], $p['brand_id'], $p['category_id'], $qty, $rate, $p['daily_rate'], $p['active'], $productId);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => $p['name'] . ' has been updated.'));
