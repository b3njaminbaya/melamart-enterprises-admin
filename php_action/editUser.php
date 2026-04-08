<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $editUserName = trim($_POST['edituserName'] ?? '');
    $editPassword = $_POST['editPassword']      ?? '';
    $userId       = intval($_POST['userid']     ?? 0);

    if($editUserName === '' || $userId === 0) {
        $valid['messages'] = "Username and user ID are required.";
        echo json_encode($valid); exit();
    }

    if($editPassword !== '') {
        // Password provided — update both username and password
        if(strlen($editPassword) < 8) {
            $valid['messages'] = "Password must be at least 8 characters.";
            echo json_encode($valid); exit();
        }
        $hashedPassword = password_hash($editPassword, PASSWORD_BCRYPT);

        $stmt = $connect->prepare("UPDATE users SET username = ?, password = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $editUserName, $hashedPassword, $userId);
    } else {
        // No new password — update only username
        $stmt = $connect->prepare("UPDATE users SET username = ? WHERE user_id = ?");
        $stmt->bind_param("si", $editUserName, $userId);
    }

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "User updated successfully.";
    } else {
        $valid['messages'] = "Error while updating the user.";
    }
    $stmt->close();
    echo json_encode($valid);
}
