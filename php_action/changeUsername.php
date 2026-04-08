<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $userId   = intval($_POST['user_id'] ?? 0);

    if($username === '' || $userId === 0) {
        $valid['messages'] = "Username and user ID are required.";
        echo json_encode($valid); exit();
    }

    $stmt = $connect->prepare("UPDATE users SET username = ? WHERE user_id = ?");
    $stmt->bind_param("si", $username, $userId);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Username updated successfully.";
    } else {
        $valid['messages'] = "Error while updating username.";
    }
    $stmt->close();
    echo json_encode($valid);
}
