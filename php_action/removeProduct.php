<?php
require_once 'core.php';

require_admin();
require_post();
csrf_verify();

$productId = (int)($_POST['productId'] ?? 0);
if($productId <= 0) {
    json_out(array('success' => false, 'messages' => 'Invalid product.'));
}

// Equipment that is out on hire must come back before the product can be removed.
$stmt = $connect->prepare(
    "SELECT COALESCE(SUM(oi.quantity), 0) FROM order_item oi
     INNER JOIN orders o ON o.order_id = oi.order_id
     WHERE oi.product_id = ? AND o.order_status != 2 AND o.returned_date IS NULL"
);
$stmt->bind_param('i', $productId);
$stmt->execute();
$onHire = (int)$stmt->get_result()->fetch_row()[0];
$stmt->close();
if($onHire > 0) {
    json_out(array('success' => false, 'messages' => $onHire . ' of this item are still on hire. Record their return before removing the product.'));
}

$stmt = $connect->prepare("UPDATE product SET active = 2, status = 2 WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'Product removed.'));
