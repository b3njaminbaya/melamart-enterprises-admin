<?php
require_once 'core.php';

require_admin();
require_post();
csrf_verify();

$userId = (int)($_POST['userid'] ?? 0);
if($userId <= 0) {
    json_out(array('success' => false, 'messages' => 'Invalid user.'));
}
if($userId === 1) {
    json_out(array('success' => false, 'messages' => 'The administrator account cannot be removed.'));
}
if($userId === current_user_id()) {
    json_out(array('success' => false, 'messages' => 'You cannot remove your own account.'));
}

// Orders keep their history even after the user who created them is removed.
$stmt = $connect->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'User removed.'));
