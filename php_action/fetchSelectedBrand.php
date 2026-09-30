<?php
require_once 'core.php';

require_admin();

$id = (int)($_POST['brandId'] ?? 0);
$stmt = $connect->prepare("SELECT brand_id, brand_name, brand_active FROM brands WHERE brand_id = ? AND brand_status = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$row) {
    json_out(array('success' => false, 'messages' => 'Brand not found.'), 404);
}

json_out(array('success' => true, 'id' => (int)$row['brand_id'], 'name' => $row['brand_name'], 'active' => (int)$row['brand_active']));
