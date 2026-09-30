<?php
require_once 'core.php';

$productId = (int)($_POST['productId'] ?? 0);

$stmt = $connect->prepare("SELECT product_id, product_name, product_image, brand_id, categories_id, quantity, rate, daily_rate, active, status FROM product WHERE product_id = ?");
$stmt->bind_param('i', $productId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$row) {
    json_out(array('success' => false, 'messages' => 'Product not found.'), 404);
}

json_out(array(
    'success'       => true,
    'product_id'    => (int)$row['product_id'],
    'product_name'  => $row['product_name'],
    'product_image' => $row['product_image'],
    'brand_id'      => (int)$row['brand_id'],
    'categories_id' => (int)$row['categories_id'],
    'quantity'      => (int)$row['quantity'],
    'rate'          => $row['rate'],          // purchase cost
    'daily_rate'    => $row['daily_rate'],    // hire price per day
    'active'        => (int)$row['active'],
    'status'        => (int)$row['status'],
));
