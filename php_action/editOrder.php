<?php
require_once 'core.php';
require_once 'order_service.php';

require_post();
csrf_verify();

$orderId = (int)($_POST['orderId'] ?? 0);
if($orderId <= 0) {
    json_out(array('success' => false, 'messages' => 'Invalid order.'));
}

$result = save_order($connect, $orderId);
if($result['success']) {
    $_SESSION['flash'] = $result['messages'];
}
json_out($result);
