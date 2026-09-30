<?php
/*
 * sms.php — SMS via Africa's Talking (plain cURL, no Composer needed).
 * Configure MEL_SMS_USERNAME / MEL_SMS_API_KEY in config.local.php.
 */

require_once __DIR__ . '/helpers.php';

function sms_configured() {
    return MEL_SMS_USERNAME !== '' && MEL_SMS_API_KEY !== '';
}

/**
 * Sends one SMS and logs it. Returns [bool ok, string message].
 */
function send_sms($connect, $to, $message, $orderId = null) {
    if(!sms_configured()) {
        return array(false, 'SMS is not set up yet. Add the Africa\'s Talking username and API key to php_action/config.local.php.');
    }
    $phone = normalize_ke_phone($to);
    if(!$phone) {
        return array(false, 'The phone number ' . $to . ' is not a valid Kenyan number.');
    }
    if(!function_exists('curl_init')) {
        return array(false, 'The PHP cURL extension is required to send SMS.');
    }

    $host = MEL_SMS_USERNAME === 'sandbox' ? 'https://api.sandbox.africastalking.com' : 'https://api.africastalking.com';
    $fields = array('username' => MEL_SMS_USERNAME, 'to' => $phone, 'message' => $message);
    if(MEL_SMS_SENDER_ID !== '') {
        $fields['from'] = MEL_SMS_SENDER_ID;
    }

    $ch = curl_init($host . '/version1/messaging');
    curl_setopt_array($ch, array(
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => array('Accept: application/json', 'apiKey: ' . MEL_SMS_API_KEY),
    ));
    $body = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    $status = 'Failed';
    if($body !== false) {
        $json = json_decode($body, true);
        $status = $json['SMSMessageData']['Recipients'][0]['status'] ?? ($json['SMSMessageData']['Message'] ?? 'Failed');
    }
    $ok = $status === 'Success';

    $stmt = $connect->prepare("INSERT INTO sms_logs (order_id, recipient, message, status, sent_date) VALUES (?, ?, ?, ?, NOW())");
    $statusShort = substr($status, 0, 30);
    $stmt->bind_param('isss', $orderId, $phone, $message, $statusShort);
    $stmt->execute();
    $stmt->close();

    if($ok) {
        return array(true, 'SMS sent to ' . format_phone($phone) . '.');
    }
    error_log('[melamart-admin] SMS failed: ' . ($curlError ?: $body));
    return array(false, 'The SMS could not be sent (' . ($curlError ?: $status) . ').');
}

/** Overdue orders still out on hire, oldest first. */
function fetch_overdue_orders($connect) {
    $today = date('Y-m-d');
    $stmt = $connect->prepare(
        "SELECT o.order_id, o.order_date, o.expect_return_date, o.site_location, o.client_name, o.client_contact,
                o.driver_name, o.driver_contact, o.grand_total, o.paid, o.due, o.last_reminder_date, o.payment_place,
                GROUP_CONCAT(CONCAT(p.product_name, ' × ', oi.quantity) ORDER BY oi.order_item_id SEPARATOR ', ') AS items
         FROM orders o
         LEFT JOIN order_item oi ON o.order_id = oi.order_id
         LEFT JOIN product p ON oi.product_id = p.product_id
         WHERE o.returned_date IS NULL
           AND o.order_status = 0
           AND o.expect_return_date < ?
         GROUP BY o.order_id
         ORDER BY o.expect_return_date ASC"
    );
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function client_overdue_message($order) {
    global $MEL_BRANCHES;
    $branch = $MEL_BRANCHES[(int)$order['payment_place']] ?? reset($MEL_BRANCHES);
    return 'Dear ' . $order['client_name'] . ', the equipment hired from ' . MEL_COMPANY_NAME
        . ' (order #' . $order['order_id'] . ') was due back on ' . format_date($order['expect_return_date'])
        . '. Extra days are charged at the daily rate. Please arrange the return or call us on ' . $branch['phone'] . '.';
}
