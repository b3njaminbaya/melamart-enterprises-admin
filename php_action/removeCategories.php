<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $categoriesId = intval($_POST['categoriesId'] ?? 0);

    if($categoriesId === 0) {
        $valid['messages'] = "Invalid category ID.";
        echo json_encode($valid); exit();
    }

    $stmt = $connect->prepare("UPDATE categories SET categories_status = 2 WHERE categories_id = ?");
    $stmt->bind_param("i", $categoriesId);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Category removed successfully.";
    } else {
        $valid['messages'] = "Error while removing the category.";
    }
    $stmt->close();
    echo json_encode($valid);
}
