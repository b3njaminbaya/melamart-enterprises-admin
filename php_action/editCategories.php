<?php
require_once 'core.php';
require_once 'csrf.php';

$valid = ['success' => false, 'messages' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $catName   = trim($_POST['editCategoriesName']   ?? '');
    $catStatus = intval($_POST['editCategoriesStatus'] ?? 1);
    $catId     = intval($_POST['editCategoriesId']     ?? 0);

    if($catName === '' || $catId === 0) {
        $valid['messages'] = "Category name and ID are required.";
        echo json_encode($valid); exit();
    }

    $stmt = $connect->prepare(
        "UPDATE categories SET categories_name = ?, categories_active = ? WHERE categories_id = ?"
    );
    $stmt->bind_param("sii", $catName, $catStatus, $catId);

    if($stmt->execute()) {
        $valid['success']  = true;
        $valid['messages'] = "Category updated successfully.";
    } else {
        $valid['messages'] = "Error while updating the category.";
    }
    $stmt->close();
    echo json_encode($valid);
}
