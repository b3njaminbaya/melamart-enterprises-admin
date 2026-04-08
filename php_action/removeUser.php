<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $userId = intval($_POST['userid'] ?? 0);

    if($userId === 0) {
        $valid['messages'] = "Invalid user ID.";
        echo json_encode($valid); exit();
    }

    // Prevent admin from deleting themselves
    if($userId === intval($_SESSION['userId'])) {
        $valid['messages'] = "You cannot delete your own account.";
        echo json_encode($valid); exit();
    }

    $stmt = $connect->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "User removed successfully.";
    } else {
        $valid['messages'] = "Error while removing the user.";
    }
    $stmt->close();
    echo json_encode($valid);
}
