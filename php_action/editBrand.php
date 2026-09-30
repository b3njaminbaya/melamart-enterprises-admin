<?php
require_once 'core.php';

require_admin();
require_post();
csrf_verify();

$name   = trim($_POST['editBrandName'] ?? '');
$active = (int)($_POST['editBrandStatus'] ?? 0);
$id     = (int)($_POST['brandId'] ?? 0);

if($id <= 0) {
    json_out(array('success' => false, 'messages' => 'Invalid brand.'));
}
if($name === '') {
    json_out(array('success' => false, 'messages' => 'Brand name is required.'));
}
if(!in_array($active, array(1, 2), true)) {
    json_out(array('success' => false, 'messages' => 'Select a status.'));
}

$stmt = $connect->prepare("SELECT COUNT(*) FROM brands WHERE brand_name = ? AND brand_status = 1 AND brand_id != ?");
$stmt->bind_param('si', $name, $id);
$stmt->execute();
$exists = (int)$stmt->get_result()->fetch_row()[0];
$stmt->close();
if($exists) {
    json_out(array('success' => false, 'messages' => 'Another brand is already called "' . $name . '".'));
}

$stmt = $connect->prepare("UPDATE brands SET brand_name = ?, brand_active = ? WHERE brand_id = ?");
$stmt->bind_param("sii", $name, $active, $id);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'Brand "' . $name . '" has been updated.'));
