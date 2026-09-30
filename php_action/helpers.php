<?php
/*
 * helpers.php — shared functions for the Melamart admin.
 * Loaded by core.php (and by the CLI cron script).
 */

require_once __DIR__ . '/config.php';

// ─── Session / auth ────────────────────────────────────────

function mel_start_session() {
    if(session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ));
    session_start();
}

function current_user_id() {
    return isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : 0;
}

// The account with user_id 1 is the administrator.
function is_admin() {
    return current_user_id() === 1;
}

function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_out($data, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Admin-only guard. Handlers get a JSON 403, pages get a friendly message.
 */
function require_admin() {
    if(is_admin()) {
        return;
    }
    $inActionDir = basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'php_action';
    if(is_ajax() || $inActionDir) {
        json_out(array('success' => false, 'messages' => 'Only the administrator can do this.'), 403);
    }
    http_response_code(403);
    echo '<div class="alert alert-warning" style="margin-top:20px;"><strong>Access restricted.</strong> '
       . 'This page is available to the administrator only. <a href="dashboard.php">Back to dashboard</a></div>';
    require __DIR__ . '/../includes/footer.php';
    exit();
}

/** Rejects anything but POST with a JSON 405. */
function require_post() {
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_out(array('success' => false, 'messages' => 'Invalid request method.'), 405);
    }
}

// ─── Output ────────────────────────────────────────────────

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($amount) {
    return number_format((float)$amount, 2);
}

function ksh($amount) {
    return 'KSh ' . money($amount);
}

// ─── Dates (UI uses dd/mm/yyyy, DB uses Y-m-d) ─────────────

/**
 * Parses a dd/mm/yyyy (or yyyy-mm-dd) string. Returns 'Y-m-d' or null.
 */
function parse_date_input($value) {
    $value = trim((string)$value);
    if($value === '') {
        return null;
    }
    if(preg_match('#^(\d{1,2})[/.-](\d{1,2})[/.-](\d{4})$#', $value, $m)) {
        $day = (int)$m[1]; $month = (int)$m[2]; $year = (int)$m[3];
    } elseif(preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $value, $m)) {
        $year = (int)$m[1]; $month = (int)$m[2]; $day = (int)$m[3];
    } else {
        return null;
    }
    if(!checkdate($month, $day, $year)) {
        return null;
    }
    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

/** True when a DB date column holds a real date. */
function has_date($value) {
    return !empty($value) && $value !== '0000-00-00';
}

/** Y-m-d → dd/mm/yyyy ('' when empty). */
function format_date($value) {
    return has_date($value) ? date('d/m/Y', strtotime($value)) : '';
}

/** Number of hire days, counting both the start and end day (min 1). */
function hire_days($fromYmd, $toYmd) {
    $from = new DateTime($fromYmd);
    $to   = new DateTime($toYmd);
    if($to < $from) {
        return 1;
    }
    return $from->diff($to)->days + 1;
}

// ─── Phones ────────────────────────────────────────────────

/**
 * Normalises a Kenyan mobile/landline-style number to +254XXXXXXXXX.
 * Accepts 07XX…, 01XX…, 7XX…, 2547XX…, +2547XX… (spaces/dashes allowed).
 * Returns null when it is not a valid Kenyan number.
 */
function normalize_ke_phone($value) {
    $digits = preg_replace('/[\s\-().]/', '', (string)$value);
    if(preg_match('/^(?:\+?254|0)?([17]\d{8})$/', $digits, $m)) {
        return '+254' . $m[1];
    }
    return null;
}

/** +254712345678 → 0712 345 678 for display. */
function format_phone($value) {
    if(preg_match('/^\+254(\d{3})(\d{3})(\d{3})$/', (string)$value, $m)) {
        return '0' . $m[1] . ' ' . $m[2] . ' ' . $m[3];
    }
    return (string)$value;
}

// ─── Lookups ───────────────────────────────────────────────

function payment_type_label($id) {
    global $MEL_PAYMENT_TYPES;
    return $MEL_PAYMENT_TYPES[(int)$id] ?? 'Not set';
}

function payment_status_label($id) {
    global $MEL_PAYMENT_STATUSES;
    return $MEL_PAYMENT_STATUSES[(int)$id] ?? 'Not set';
}

function order_status_label($id) {
    global $MEL_ORDER_STATUSES;
    return $MEL_ORDER_STATUSES[(int)$id] ?? 'Unknown';
}

function branch_label($id) {
    global $MEL_BRANCHES;
    return isset($MEL_BRANCHES[(int)$id]) ? $MEL_BRANCHES[(int)$id]['name'] : 'Not set';
}

/** Payment status is derived, never entered by hand. */
function derive_payment_status($paid, $grandTotal) {
    $paid = round((float)$paid, 2);
    $grandTotal = round((float)$grandTotal, 2);
    if($paid <= 0) {
        return $grandTotal <= 0 ? 1 : 3;
    }
    return $paid >= $grandTotal ? 1 : 2;
}

/** <option> list for a lookup array. */
function render_options($items, $selected = null, $placeholder = '') {
    $html = $placeholder !== '' ? '<option value="">' . h($placeholder) . '</option>' : '';
    foreach($items as $id => $label) {
        if(is_array($label)) {
            $label = $label['name'];
        }
        $sel = ($selected !== null && $selected !== '' && (int)$selected === (int)$id) ? ' selected' : '';
        $html .= '<option value="' . (int)$id . '"' . $sel . '>' . h($label) . '</option>';
    }
    return $html;
}

// ─── Amount in words (Kenya Shillings) ─────────────────────

function number_to_words($number) {
    $number = (int)$number;
    $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');

    if($number === 0) {
        return 'Zero';
    }
    $words = array();
    foreach(array(1000000000 => 'Billion', 1000000 => 'Million', 1000 => 'Thousand') as $value => $name) {
        if($number >= $value) {
            $words[] = number_to_words(intdiv($number, $value)) . ' ' . $name;
            $number %= $value;
        }
    }
    if($number >= 100) {
        $words[] = $ones[intdiv($number, 100)] . ' Hundred';
        $number %= 100;
    }
    if($number > 0) {
        if(!empty($words)) {
            $words[] = 'and';
        }
        if($number < 20) {
            $words[] = $ones[$number];
        } else {
            $words[] = $tens[intdiv($number, 10)] . ($number % 10 ? '-' . $ones[$number % 10] : '');
        }
    }
    return implode(' ', $words);
}

function amount_in_words($amount) {
    $amount = round(abs((float)$amount), 2);
    $shillings = (int)floor($amount);
    $cents = (int)round(($amount - $shillings) * 100);
    $text = 'Kenya Shillings ' . number_to_words($shillings);
    if($cents > 0) {
        $text .= ' and ' . number_to_words($cents) . ' Cents';
    }
    return $text . ' Only';
}

// ─── Orders & stock ────────────────────────────────────────

/** Equipment is "out" (holding stock) while the order is not cancelled and not returned. */
function order_holds_stock($orderStatus, $returnedDate) {
    return (int)$orderStatus !== 2 && !has_date($returnedDate);
}

/** [product_id => quantity] currently recorded on an order. */
function order_item_quantities($connect, $orderId) {
    $stmt = $connect->prepare("SELECT product_id, quantity FROM order_item WHERE order_id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = array();
    while($row = $result->fetch_assoc()) {
        $pid = (int)$row['product_id'];
        $items[$pid] = ($items[$pid] ?? 0) + (int)$row['quantity'];
    }
    $stmt->close();
    return $items;
}

/**
 * Adds ($sign = +1) or removes ($sign = -1) stock for [product_id => qty].
 * Removal fails (returns an error message) if any product lacks stock.
 * Call inside a transaction.
 */
function adjust_stock($connect, $items, $sign) {
    foreach($items as $productId => $qty) {
        $qty = (int)$qty;
        if($qty <= 0) {
            continue;
        }
        $stmt = $connect->prepare("SELECT product_name, quantity FROM product WHERE product_id = ? FOR UPDATE");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if(!$row) {
            return "A selected product no longer exists.";
        }
        $newQty = (int)$row['quantity'] + ($sign * $qty);
        if($newQty < 0) {
            return "Not enough stock for " . $row['product_name'] . ": only " . (int)$row['quantity'] . " available, " . $qty . " requested.";
        }
        $upd = $connect->prepare("UPDATE product SET quantity = ? WHERE product_id = ?");
        $newQtyStr = (string)$newQty;
        $upd->bind_param('si', $newQtyStr, $productId);
        $upd->execute();
        $upd->close();
    }
    return null;
}

/**
 * Reads and validates the product rows posted by the order form.
 * Returns [items, error]. Each item: product_id, quantity, rental_days.
 */
function read_posted_order_items() {
    $products = $_POST['productName'] ?? array();
    $quantities = $_POST['quantity'] ?? array();
    $days = $_POST['rentalDays'] ?? array();
    if(!is_array($products)) {
        return array(array(), 'Please add at least one product.');
    }
    $items = array();
    $seen = array();
    foreach($products as $i => $productId) {
        $productId = (int)$productId;
        if($productId <= 0) {
            continue;
        }
        $qty = (int)($quantities[$i] ?? 0);
        $rentalDays = (int)($days[$i] ?? 0);
        if($qty <= 0) {
            return array(array(), 'Each product needs a quantity of at least 1.');
        }
        if($rentalDays <= 0) {
            return array(array(), 'Each product needs at least 1 rental day.');
        }
        if(isset($seen[$productId])) {
            return array(array(), 'The same product is listed twice. Combine the quantities into one row.');
        }
        $seen[$productId] = true;
        $items[] = array('product_id' => $productId, 'quantity' => $qty, 'rental_days' => $rentalDays);
    }
    if(empty($items)) {
        return array(array(), 'Please add at least one product.');
    }
    return array($items, null);
}

/** Current daily rates for the given product ids: [product_id => rate]. */
function product_daily_rates($connect, $productIds) {
    $rates = array();
    $stmt = $connect->prepare("SELECT daily_rate FROM product WHERE product_id = ?");
    foreach($productIds as $pid) {
        $pid = (int)$pid;
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if($row) {
            $rates[$pid] = (float)$row['daily_rate'];
        }
    }
    $stmt->close();
    return $rates;
}

/**
 * Late-return charge: every day past each line's paid rental period,
 * at that line's daily rate. Zero if not returned late.
 */
function calculate_late_fee($items, $orderDate, $returnedDate) {
    if(!has_date($returnedDate)) {
        return 0.0;
    }
    $actualDays = hire_days($orderDate, $returnedDate);
    $fee = 0.0;
    foreach($items as $item) {
        $extraDays = $actualDays - (int)$item['rental_days'];
        if($extraDays > 0) {
            $fee += $extraDays * (float)$item['rate'] * (int)$item['quantity'];
        }
    }
    return round($fee, 2);
}

/** Estimated late charge so far for an order that is still out. */
function accrued_late_fee($connect, $orderId, $orderDate) {
    $stmt = $connect->prepare("SELECT quantity, rate, rental_days FROM order_item WHERE order_id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return calculate_late_fee($items, $orderDate, date('Y-m-d'));
}
