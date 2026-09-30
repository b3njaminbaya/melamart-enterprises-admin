<?php
require_once 'core.php';
require_once 'user_input.php';

require_admin();
require_post();
csrf_verify();

$userId   = (int)($_POST['userid'] ?? 0);
$username = trim($_POST['edituserName'] ?? '');
$email    = trim($_POST['editEmail'] ?? '');
$password = $_POST['editPassword'] ?? '';

if($userId <= 0) {
    json_out(array('success' => false, 'messages' => 'Invalid user.'));
}
$error = username_error($connect, $username, $userId);
if(!$error && ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    $error = 'Enter a valid email address.';
}
if(!$error && $password !== '') {
    $error = password_error($password);
}
if($error) {
    json_out(array('success' => false, 'messages' => $error));
}

if($password !== '') {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $connect->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $username, $email, $hash, $userId);
} else {
    $stmt = $connect->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $username, $email, $userId);
}
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'User ' . $username . ' has been updated.' . ($password !== '' ? ' Their password was changed.' : '')));
