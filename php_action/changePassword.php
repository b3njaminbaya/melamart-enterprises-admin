<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $currentPassword = $_POST['password']  ?? '';
    $newPassword     = $_POST['npassword'] ?? '';
    $confirmPassword = $_POST['cpassword'] ?? '';
    $userId          = intval($_POST['user_id'] ?? 0);

    if($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $valid['messages'] = "All password fields are required.";
        echo json_encode($valid); exit();
    }
    if($userId === 0) {
        $valid['messages'] = "Invalid user ID.";
        echo json_encode($valid); exit();
    }
    if($newPassword !== $confirmPassword) {
        $valid['messages'] = "New password and confirm password do not match.";
        echo json_encode($valid); exit();
    }
    if(strlen($newPassword) < 8) {
        $valid['messages'] = "New password must be at least 8 characters.";
        echo json_encode($valid); exit();
    }

    // Fetch stored hash
    $stmt = $connect->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if($result->num_rows !== 1) {
        $valid['messages'] = "User not found.";
        echo json_encode($valid); exit();
    }
    $user = $result->fetch_assoc();
    $stored = $user['password'];

    // Verify current password — supports both bcrypt and legacy MD5
    $currentVerified = password_verify($currentPassword, $stored)
                    || $stored === md5($currentPassword);

    if(!$currentVerified) {
        $valid['messages'] = "Current password is incorrect.";
        echo json_encode($valid); exit();
    }

    // Store new password as bcrypt (replaces any legacy MD5)
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $upStmt  = $connect->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $upStmt->bind_param("si", $newHash, $userId);

    if($upStmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Password updated successfully.";
    } else {
        $valid['messages'] = "Error while updating the password.";
    }
    $upStmt->close();
    echo json_encode($valid);
}
