<?php
/*
 * Products that can be put on a new order line:
 * [product_id, product_name, available quantity, daily_rate]
 */
require_once 'core.php';

$result = $connect->query("SELECT product_id, product_name, quantity, daily_rate FROM product WHERE status = 1 AND active = 1 ORDER BY product_name");

$data = array();
while($row = $result->fetch_assoc()) {
    $data[] = array((int)$row['product_id'], $row['product_name'], (int)$row['quantity'], (float)$row['daily_rate']);
}

json_out($data);
