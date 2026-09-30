<?php
/*
 * Records a payment against an order. Amounts come from the database,
 * never from the browser; only the new payment amount is posted.
 */
require_once 'core.php';
require_once 'order_service.php';

require_post();
csrf_verify();

$orderId     = (int)($_POST['orderId'] ?? 0);
$amountRaw   = trim($_POST['payAmount'] ?? '');
$paymentType = (int)($_POST['paymentType'] ?? 0);
$reference   = strtoupper(trim($_POST['paymentReference'] ?? ''));

if(!is_numeric($amountRaw) || (float)$amountRaw <= 0) {
    json_out(array('success' => false, 'messages' => 'Enter a payment amount greater than 0.'));
}
$amount = round((float)$amountRaw, 2);
if(!isset($MEL_PAYMENT_TYPES[$paymentType])) {
    json_out(array('success' => false, 'messages' => 'Select how the client paid.'));
}
if(strlen($reference) > 100) {
    json_out(array('success' => false, 'messages' => 'The payment reference is too long.'));
}

$connect->begin_transaction();
try {
    $stmt = $connect->prepare("SELECT grand_total, paid, order_status FROM orders WHERE order_id = ? FOR UPDATE");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$order) {
        $connect->rollback();
        json_out(array('success' => false, 'messages' => 'Order not found.'));
    }
    if((int)$order['order_status'] === 2) {
        $connect->rollback();
        json_out(array('success' => false, 'messages' => 'Payments cannot be recorded on a cancelled order.'));
    }

    $grandTotal = round((float)$order['grand_total'], 2);
    $currentDue = round($grandTotal - (float)$order['paid'], 2);
    if($amount > $currentDue) {
        $connect->rollback();
        json_out(array('success' => false, 'messages' => 'The payment cannot be more than the balance of ' . ksh($currentDue) . '.'));
    }

    $newPaid = round((float)$order['paid'] + $amount, 2);
    $newDue = round($grandTotal - $newPaid, 2);
    $paymentStatus = derive_payment_status($newPaid, $grandTotal);
    $newPaidStr = money_db($newPaid);
    $newDueStr = money_db($newDue);

    $stmt = $connect->prepare("UPDATE orders SET paid = ?, due = ?, payment_type = ?, payment_reference = ?, payment_status = ? WHERE order_id = ?");
    $stmt->bind_param('ssisii', $newPaidStr, $newDueStr, $paymentType, $reference, $paymentStatus, $orderId);
    $stmt->execute();
    $stmt->close();

    record_payment_history($connect, $orderId, $amount, $paymentType, $reference, current_user_id());

    $connect->commit();
} catch(Throwable $e) {
    $connect->rollback();
    throw $e;
}

$message = 'Payment of ' . ksh($amount) . ' recorded for order #' . $orderId . '.';
$message .= $newDue > 0 ? ' Balance: ' . ksh($newDue) . '.' : ' The order is now fully paid.';
json_out(array('success' => true, 'messages' => $message));
