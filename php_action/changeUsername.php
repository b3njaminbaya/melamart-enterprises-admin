<?php
/*
 * The logged-in user changes their own username.
 */
require_once 'core.php';
require_once 'user_input.php';

require_post();
csrf_verify();

$userId = current_user_id();
$username = trim($_POST['username'] ?? '');

$error = username_error($connect, $username, $userId);
if($error) {
    json_out(array('success' => false, 'messages' => $error));
}

$stmt = $connect->prepare("UPDATE users SET username = ? WHERE user_id = ?");
$stmt->bind_param("si", $username, $userId);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'Your username is now ' . $username . '.'));
