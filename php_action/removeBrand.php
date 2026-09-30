<?php
require_once 'core.php';

require_admin();
require_post();
csrf_verify();

$id = (int)($_POST['brandId'] ?? 0);
if($id <= 0) {
    json_out(array('success' => false, 'messages' => 'Invalid brand.'));
}

// Products must be moved to another brand first, or they would lose their brand.
$stmt = $connect->prepare("SELECT COUNT(*) FROM product WHERE brand_id = ? AND status = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$inUse = (int)$stmt->get_result()->fetch_row()[0];
$stmt->close();
if($inUse > 0) {
    json_out(array('success' => false, 'messages' => $inUse . ' product(s) still use this brand. Change them to another brand first, or mark this brand as "Not available" instead.'));
}

$stmt = $connect->prepare("UPDATE brands SET brand_status = 2 WHERE brand_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'Brand removed.'));
