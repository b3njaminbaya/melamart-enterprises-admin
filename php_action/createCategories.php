<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $categoriesName   = trim($_POST['categoriesName']   ?? '');
    $categoriesStatus = intval($_POST['categoriesStatus'] ?? 1);

    if($categoriesName === '') {
        $valid['messages'] = "Category name is required.";
        echo json_encode($valid); exit();
    }

    $stmt = $connect->prepare(
        "INSERT INTO categories (categories_name, categories_active, categories_status) VALUES (?, ?, 1)"
    );
    $stmt->bind_param("si", $categoriesName, $categoriesStatus);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Category added successfully.";
    } else {
        $valid['messages'] = "Error while adding the category.";
    }
    $stmt->close();
    echo json_encode($valid);
}
