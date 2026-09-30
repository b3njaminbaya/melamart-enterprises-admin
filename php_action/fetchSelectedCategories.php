<?php
require_once 'core.php';

require_admin();

$id = (int)($_POST['categoriesId'] ?? 0);
$stmt = $connect->prepare("SELECT categories_id, categories_name, categories_active FROM categories WHERE categories_id = ? AND categories_status = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$row) {
    json_out(array('success' => false, 'messages' => 'Category not found.'), 404);
}

json_out(array('success' => true, 'id' => (int)$row['categories_id'], 'name' => $row['categories_name'], 'active' => (int)$row['categories_active']));
