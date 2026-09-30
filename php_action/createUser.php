<?php
require_once 'core.php';
require_once 'user_input.php';

require_admin();
require_post();
csrf_verify();

$userName  = trim($_POST['userName'] ?? '');
$upassword = $_POST['upassword'] ?? '';
$uemail    = trim($_POST['uemail'] ?? '');

$error = username_error($connect, $userName) ?? password_error($upassword);
if(!$error && ($uemail === '' || !filter_var($uemail, FILTER_VALIDATE_EMAIL))) {
    $error = 'Enter a valid email address.';
}
if($error) {
    json_out(array('success' => false, 'messages' => $error));
}

$hashedPassword = password_hash($upassword, PASSWORD_BCRYPT);
$stmt = $connect->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $userName, $hashedPassword, $uemail);
$stmt->execute();
$stmt->close();

json_out(array('success' => true, 'messages' => 'User ' . $userName . ' has been added.'));
