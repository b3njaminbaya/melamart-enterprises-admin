<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $userName = trim($_POST['userName']  ?? '');
    $upassword = $_POST['upassword']     ?? '';
    $uemail   = trim($_POST['uemail']    ?? '');

    if($userName === '' || $upassword === '' || $uemail === '') {
        $valid['messages'] = "All fields are required.";
        echo json_encode($valid); exit();
    }
    if(strlen($upassword) < 8) {
        $valid['messages'] = "Password must be at least 8 characters.";
        echo json_encode($valid); exit();
    }
    if(!filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
        $valid['messages'] = "Please enter a valid email address.";
        echo json_encode($valid); exit();
    }

    // Check if username already exists
    $chk = $connect->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
    $chk->bind_param("s", $userName);
    $chk->execute();
    if($chk->get_result()->num_rows > 0) {
        $valid['messages'] = "Username already exists. Please choose another.";
        $chk->close();
        echo json_encode($valid); exit();
    }
    $chk->close();

    // Hash password with bcrypt (NOT md5)
    $hashedPassword = password_hash($upassword, PASSWORD_BCRYPT);

    $stmt = $connect->prepare(
        "INSERT INTO users (username, password, email) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $userName, $hashedPassword, $uemail);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "User added successfully.";
    } else {
        $valid['messages'] = "Error while adding the user.";
    }
    $stmt->close();
    echo json_encode($valid);
}
