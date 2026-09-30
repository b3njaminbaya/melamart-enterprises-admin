<?php
require_once 'core.php';

require_admin();
require_post();
csrf_verify();

$name   = trim($_POST['brandName'] ?? '');
$active = (int)($_POST['brandStatus'] ?? 0);

if($name === '') {
    json_out(array('success' => false, 'messages' => 'Brand name is required.'));
}
if(!in_array($active, array(1, 2), true)) {
    json_out(array('success' => false, 'messages' => 'Select a status.'));
}

$stmt = $connect->prepare("SELECT COUNT(*) FROM brands WHERE brand_name = ? AND brand_status = 1");
$stmt->bind_param('s', $name);
$stmt->execute();
$exists = (int)$stmt->get_result()->fetch_row()[0];
$stmt->close();
if($exists) {
    json_out(array('success' => false, 'messages' => 'A brand called "' . $name . '" already exists.'));
}

$stmt = $connect->prepare("INSERT INTO brands (brand_name, brand_active, brand_status) VALUES (?, ?, 1)");
$stmt->bind_param("si", $name, $active);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'Brand "' . $name . '" has been added.'));
