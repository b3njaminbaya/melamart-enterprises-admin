<?php
require_once 'core.php';

require_admin();
require_post();
csrf_verify();

$name   = trim($_POST['editCategoriesName'] ?? '');
$active = (int)($_POST['editCategoriesStatus'] ?? 0);
$id     = (int)($_POST['editCategoriesId'] ?? 0);

if($id <= 0) {
    json_out(array('success' => false, 'messages' => 'Invalid category.'));
}
if($name === '') {
    json_out(array('success' => false, 'messages' => 'Category name is required.'));
}
if(!in_array($active, array(1, 2), true)) {
    json_out(array('success' => false, 'messages' => 'Select a status.'));
}

$stmt = $connect->prepare("SELECT COUNT(*) FROM categories WHERE categories_name = ? AND categories_status = 1 AND categories_id != ?");
$stmt->bind_param('si', $name, $id);
$stmt->execute();
$exists = (int)$stmt->get_result()->fetch_row()[0];
$stmt->close();
if($exists) {
    json_out(array('success' => false, 'messages' => 'Another category is already called "' . $name . '".'));
}

$stmt = $connect->prepare("UPDATE categories SET categories_name = ?, categories_active = ? WHERE categories_id = ?");
$stmt->bind_param("sii", $name, $active, $id);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'Category "' . $name . '" has been updated.'));
