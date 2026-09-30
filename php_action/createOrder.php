<?php
require_once 'core.php';
require_once 'order_service.php';

require_post();
csrf_verify();

$result = save_order($connect, null);
if($result['success']) {
    $_SESSION['flash'] = $result['messages'];
}
json_out($result);
