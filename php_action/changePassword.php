<?php
/*
 * The logged-in user changes their own password.
 */
require_once 'core.php';
require_once 'user_input.php';

require_post();
csrf_verify();

if(is_locked_demo_user($connect)) {
    json_out(array('success' => false, 'messages' => 'The shared demo account cannot be changed.'));
}

$userId          = current_user_id();
$currentPassword = $_POST['password']  ?? '';
$newPassword     = $_POST['npassword'] ?? '';
$confirmPassword = $_POST['cpassword'] ?? '';

if($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    json_out(array('success' => false, 'messages' => 'Fill in all three password fields.'));
}
if($newPassword !== $confirmPassword) {
    json_out(array('success' => false, 'messages' => 'The new password and confirmation do not match.'));
}
$error = password_error($newPassword);
if($error) {
    json_out(array('success' => false, 'messages' => $error));
}

$stmt = $connect->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Supports both bcrypt and legacy MD5 hashes.
if(!$user || !(password_verify($currentPassword, $user['password']) || hash_equals($user['password'], md5($currentPassword)))) {
    json_out(array('success' => false, 'messages' => 'Your current password is incorrect.'));
}

$newHash = password_hash($newPassword, PASSWORD_BCRYPT);
$stmt = $connect->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$stmt->bind_param("si", $newHash, $userId);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'Your password has been changed.'));
