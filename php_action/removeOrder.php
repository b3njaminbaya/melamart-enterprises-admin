<?php
/*
 * Cancels an order (orders are never hard-deleted).
 * Equipment still out on hire goes back into stock.
 */
require_once 'core.php';

require_post();
csrf_verify();

$orderId = (int)($_POST['orderId'] ?? 0);
if($orderId <= 0) {
    json_out(array('success' => false, 'messages' => 'Invalid order.'));
}

$connect->begin_transaction();
try {
    $stmt = $connect->prepare("SELECT order_status, returned_date FROM orders WHERE order_id = ? FOR UPDATE");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$order) {
        $connect->rollback();
        json_out(array('success' => false, 'messages' => 'This order no longer exists.'));
    }
    if((int)$order['order_status'] === 2) {
        $connect->rollback();
        json_out(array('success' => false, 'messages' => 'Order #' . $orderId . ' is already cancelled.'));
    }

    if(order_holds_stock($order['order_status'], $order['returned_date'])) {
        adjust_stock($connect, order_item_quantities($connect, $orderId), +1);
    }

    $stmt = $connect->prepare("UPDATE orders SET order_status = 2 WHERE order_id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $stmt->close();

    $stmt = $connect->prepare("UPDATE order_item SET order_item_status = 2 WHERE order_id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $stmt->close();

    $connect->commit();
} catch(Throwable $e) {
    $connect->rollback();
    throw $e;
}

json_out(array('success' => true, 'messages' => 'Order #' . $orderId . ' has been cancelled and its equipment returned to stock.'));
