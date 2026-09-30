<?php
require_once 'core.php';

require_admin();

$userId = (int)($_POST['userid'] ?? 0);
$stmt = $connect->prepare("SELECT user_id, username, email FROM users WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$row) {
    json_out(array('success' => false, 'messages' => 'User not found.'), 404);
}

json_out(array('success' => true, 'user_id' => (int)$row['user_id'], 'username' => $row['username'], 'email' => $row['email']));
