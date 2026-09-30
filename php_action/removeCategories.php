<?php
require_once 'core.php';

require_admin();
require_post();
csrf_verify();

$id = (int)($_POST['categoriesId'] ?? 0);
if($id <= 0) {
    json_out(array('success' => false, 'messages' => 'Invalid category.'));
}

// Products must be moved to another category first, or they would lose their category.
$stmt = $connect->prepare("SELECT COUNT(*) FROM product WHERE categories_id = ? AND status = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$inUse = (int)$stmt->get_result()->fetch_row()[0];
$stmt->close();
if($inUse > 0) {
    json_out(array('success' => false, 'messages' => $inUse . ' product(s) still use this category. Change them to another category first, or mark this category as "Not available" instead.'));
}

$stmt = $connect->prepare("UPDATE categories SET categories_status = 2 WHERE categories_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'Category removed.'));
