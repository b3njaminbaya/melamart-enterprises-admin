<?php
/*
 * Shared validation for the add/edit product forms.
 */

/**
 * $prefix '' for the add form (productName, quantity, …),
 * 'edit' for the edit form (editProductName, editQuantity, …).
 */
function read_product_input($prefix) {
    $field = function($name) use ($prefix) {
        return trim($_POST[$prefix === '' ? $name : $prefix . ucfirst($name)] ?? '');
    };
    $p = array(
        'name'        => $field('productName'),
        'quantity'    => $field('quantity'),
        'rate'        => $field('rate'),
        'daily_rate'  => $field('dailyRate'),
        'brand_id'    => (int)$field('brandName'),
        'category_id' => (int)$field('categoryName'),
        'active'      => (int)$field('productStatus'),
    );
    if($p['name'] === '') {
        return array(null, 'Product name is required.');
    }
    if(!ctype_digit($p['quantity'])) {
        return array(null, 'Quantity in store must be a whole number (0 or more).');
    }
    if($p['rate'] === '') { $p['rate'] = '0'; }
    if(!is_numeric($p['rate']) || (float)$p['rate'] < 0) {
        return array(null, 'Purchase cost must be a number (0 or more).');
    }
    if(!is_numeric($p['daily_rate']) || (float)$p['daily_rate'] <= 0) {
        return array(null, 'Daily hire rate must be more than 0.');
    }
    if($p['brand_id'] <= 0) {
        return array(null, 'Select a brand.');
    }
    if($p['category_id'] <= 0) {
        return array(null, 'Select a category.');
    }
    if(!in_array($p['active'], array(1, 2), true)) {
        return array(null, 'Select whether the product is available for hire.');
    }
    $p['quantity'] = (int)$p['quantity'];
    $p['rate'] = round((float)$p['rate'], 2);
    $p['daily_rate'] = round((float)$p['daily_rate'], 2);
    return array($p, null);
}

/**
 * Validates and moves an uploaded product photo. Returns [url, error].
 */
function store_product_image($file) {
    if($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return array(null, 'The photo is too large. Use an image under 2.5 MB.');
    }
    if($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return array(null, 'The photo could not be uploaded. Please try again.');
    }
    if($file['size'] > 2.5 * 1024 * 1024) {
        return array(null, 'The photo is too large. Use an image under 2.5 MB.');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(!in_array($ext, array('gif', 'jpg', 'jpeg', 'png'), true) || @getimagesize($file['tmp_name']) === false) {
        return array(null, 'The photo must be a JPG, PNG or GIF image.');
    }
    $url = '../assets/images/stock/' . bin2hex(random_bytes(10)) . '.' . $ext;
    if(!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $url)) {
        return array(null, 'The photo could not be saved on the server.');
    }
    return array($url, null);
}
