<?php
/*
 * order_service.php — the single place where orders are validated,
 * priced and saved. createOrder.php and editOrder.php are thin wrappers.
 *
 * Rules
 *  - Every amount is recalculated here from the database; totals posted
 *    by the browser are ignored.
 *  - Line total = daily rate × rental days × quantity.
 *  - New lines use the product's current daily rate; lines kept on an
 *    edited order keep the rate they were hired at.
 *  - Stock is held while an order is not cancelled and not returned.
 *  - Late-return charge = extra days past each line's rental days
 *    × daily rate × quantity (see calculate_late_fee()).
 *  - Payment status is derived from paid vs grand total.
 */

require_once __DIR__ . '/helpers.php';

/**
 * Validates the posted order form. Returns [data, error].
 */
function collect_order_input($isEdit) {
    global $MEL_BRANCHES, $MEL_PAYMENT_TYPES;

    $d = array();
    $d['order_date'] = parse_date_input($_POST['orderDate'] ?? '');
    if(!$d['order_date']) {
        return array(null, 'Enter a valid order date (dd/mm/yyyy).');
    }
    $d['expect_return_date'] = parse_date_input($_POST['expectReturnDate'] ?? '');
    if(!$d['expect_return_date']) {
        return array(null, 'Enter a valid expected return date (dd/mm/yyyy).');
    }
    if($d['expect_return_date'] < $d['order_date']) {
        return array(null, 'The expected return date cannot be before the order date.');
    }

    $d['branch'] = (int)($_POST['branch'] ?? 0);
    if(!isset($MEL_BRANCHES[$d['branch']])) {
        return array(null, 'Select the branch handling this order.');
    }

    $d['site_location'] = trim($_POST['siteLocation'] ?? '');
    $d['client_name'] = trim($_POST['clientName'] ?? '');
    $d['driver_name'] = trim($_POST['driverName'] ?? '');
    if($d['site_location'] === '') { return array(null, 'Site location is required.'); }
    if($d['client_name'] === '')   { return array(null, 'Client name is required.'); }
    if($d['driver_name'] === '')   { return array(null, 'Driver name is required.'); }

    $d['client_contact'] = normalize_ke_phone($_POST['clientContact'] ?? '');
    if(!$d['client_contact']) {
        return array(null, 'Enter a valid Kenyan phone number for the client, e.g. 0712 345 678.');
    }
    $d['driver_contact'] = normalize_ke_phone($_POST['driverContact'] ?? '');
    if(!$d['driver_contact']) {
        return array(null, 'Enter a valid Kenyan phone number for the driver, e.g. 0712 345 678.');
    }

    $d['client_kra_pin'] = strtoupper(trim($_POST['clientKraPin'] ?? ''));
    if($d['client_kra_pin'] !== '' && !preg_match('/^[AP]\d{9}[A-Z]$/', $d['client_kra_pin'])) {
        return array(null, 'The client KRA PIN should look like P051234567X (or leave it blank).');
    }

    $discount = trim($_POST['discount'] ?? '0');
    if($discount === '') { $discount = '0'; }
    if(!is_numeric($discount) || (float)$discount < 0) {
        return array(null, 'Discount must be a number of 0 or more.');
    }
    $d['discount'] = round((float)$discount, 2);

    list($items, $itemError) = read_posted_order_items();
    if($itemError) {
        return array(null, $itemError);
    }
    $d['items'] = $items;

    if($isEdit) {
        $d['returned_date'] = null;
        $returnedRaw = trim($_POST['returnedDate'] ?? '');
        if($returnedRaw !== '') {
            $d['returned_date'] = parse_date_input($returnedRaw);
            if(!$d['returned_date']) {
                return array(null, 'Enter a valid returned date (dd/mm/yyyy), or leave it blank if the equipment is still on hire.');
            }
            if($d['returned_date'] < $d['order_date']) {
                return array(null, 'The returned date cannot be before the order date.');
            }
            if($d['returned_date'] > date('Y-m-d')) {
                return array(null, 'The returned date cannot be in the future.');
            }
        }
        $d['returned_by'] = trim($_POST['returnedBy'] ?? '');
        $d['approved_by'] = trim($_POST['approvedBy'] ?? '');
        $d['returned_by_contact'] = '';
        $returnedContact = trim($_POST['returnedByContact'] ?? '');
        if($returnedContact !== '') {
            $d['returned_by_contact'] = normalize_ke_phone($returnedContact);
            if(!$d['returned_by_contact']) {
                return array(null, 'Enter a valid Kenyan phone number for the person who returned the equipment.');
            }
        }
        $d['order_status'] = (int)($_POST['orderStatus'] ?? 0);
        if(!in_array($d['order_status'], array(0, 1, 2), true)) {
            return array(null, 'Select a valid order status.');
        }
        if($d['order_status'] === 1 && !$d['returned_date']) {
            return array(null, 'Enter the returned date before marking the order as completed.');
        }
        // A returned hire is complete.
        if($d['order_status'] === 0 && $d['returned_date']) {
            $d['order_status'] = 1;
        }
    } else {
        $paid = trim($_POST['paid'] ?? '0');
        if($paid === '') { $paid = '0'; }
        if(!is_numeric($paid) || (float)$paid < 0) {
            return array(null, 'Amount paid must be a number of 0 or more.');
        }
        $d['paid'] = round((float)$paid, 2);
        $d['payment_type'] = (int)($_POST['paymentType'] ?? 0);
        $d['payment_reference'] = strtoupper(trim($_POST['paymentReference'] ?? ''));
        if($d['paid'] > 0 && !isset($MEL_PAYMENT_TYPES[$d['payment_type']])) {
            return array(null, 'Select how the client paid.');
        }
        if(strlen($d['payment_reference']) > 100) {
            return array(null, 'The payment reference is too long.');
        }
        $d['order_status'] = 0;
        $d['returned_date'] = null;
        $d['returned_by'] = $d['returned_by_contact'] = $d['approved_by'] = '';
    }

    return array($d, null);
}

/** Sub total, VAT, total, discount, grand total. */
function compute_order_totals($items, $discount, $lateFee) {
    $sub = 0.0;
    foreach($items as $item) {
        $sub += round((float)$item['rate'] * (int)$item['rental_days'] * (int)$item['quantity'], 2);
    }
    $sub = round($sub, 2);
    $vat = round($sub * MEL_VAT_RATE / 100, 2);
    $total = round($sub + $vat, 2);
    $grand = round($total + $lateFee - $discount, 2);
    return array('sub_total' => $sub, 'vat' => $vat, 'total_amount' => $total, 'grand_total' => $grand);
}

/**
 * Creates ($orderId = null) or updates an order.
 * Returns ['success' => bool, 'messages' => string, 'order_id' => int].
 */
function save_order($connect, $orderId) {
    $isEdit = $orderId !== null;
    list($d, $error) = collect_order_input($isEdit);
    if($error) {
        return array('success' => false, 'messages' => $error);
    }

    $connect->begin_transaction();
    try {
        $oldRates = array();
        $paid = $isEdit ? 0.0 : $d['paid'];

        if($isEdit) {
            $stmt = $connect->prepare("SELECT order_status, returned_date, paid, payment_type, payment_reference FROM orders WHERE order_id = ? FOR UPDATE");
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
            $old = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if(!$old) {
                $connect->rollback();
                return array('success' => false, 'messages' => 'This order no longer exists.');
            }
            $paid = round((float)$old['paid'], 2);
            $d['payment_type'] = (int)$old['payment_type'];
            $d['payment_reference'] = $old['payment_reference'];

            $stmt = $connect->prepare("SELECT product_id, rate FROM order_item WHERE order_id = ?");
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
            $res = $stmt->get_result();
            while($row = $res->fetch_assoc()) {
                $oldRates[(int)$row['product_id']] = (float)$row['rate'];
            }
            $stmt->close();

            // Put back whatever the order was holding; the new lines are taken out below.
            if(order_holds_stock($old['order_status'], $old['returned_date'])) {
                adjust_stock($connect, order_item_quantities($connect, $orderId), +1);
            }
        }

        // Price the lines.
        $newIds = array();
        foreach($d['items'] as $item) {
            if(!isset($oldRates[$item['product_id']])) {
                $newIds[] = $item['product_id'];
            }
        }
        if($newIds) {
            $in = implode(',', array_fill(0, count($newIds), '?'));
            $stmt = $connect->prepare("SELECT product_id, product_name, daily_rate FROM product WHERE product_id IN ($in) AND status = 1 AND active = 1");
            $stmt->bind_param(str_repeat('i', count($newIds)), ...$newIds);
            $stmt->execute();
            $res = $stmt->get_result();
            $found = array();
            while($row = $res->fetch_assoc()) {
                if((float)$row['daily_rate'] <= 0) {
                    $connect->rollback();
                    return array('success' => false, 'messages' => $row['product_name'] . ' has no daily hire rate set. Set it on the Equipment page first.');
                }
                $found[(int)$row['product_id']] = (float)$row['daily_rate'];
            }
            $stmt->close();
            foreach($newIds as $pid) {
                if(!isset($found[$pid])) {
                    $connect->rollback();
                    return array('success' => false, 'messages' => 'One of the selected products is no longer available for hire.');
                }
                $oldRates[$pid] = $found[$pid];
            }
        }
        foreach($d['items'] as $i => $item) {
            $d['items'][$i]['rate'] = $oldRates[$item['product_id']];
        }

        // Take the equipment out of stock if it is (still) on hire.
        if(order_holds_stock($d['order_status'], $d['returned_date'])) {
            $qty = array();
            foreach($d['items'] as $item) {
                $qty[$item['product_id']] = $item['quantity'];
            }
            $stockError = adjust_stock($connect, $qty, -1);
            if($stockError) {
                $connect->rollback();
                return array('success' => false, 'messages' => $stockError);
            }
        }

        $lateFee = calculate_late_fee($d['items'], $d['order_date'], $d['returned_date']);
        $totals = compute_order_totals($d['items'], 0, $lateFee);
        $maxDiscount = $totals['total_amount'] + $lateFee;
        if($d['discount'] > $maxDiscount) {
            $connect->rollback();
            return array('success' => false, 'messages' => 'The discount cannot be more than the order total (' . ksh($maxDiscount) . ').');
        }
        $totals = compute_order_totals($d['items'], $d['discount'], $lateFee);
        if($paid > $totals['grand_total']) {
            $connect->rollback();
            $msg = $isEdit
                ? 'This change would make the grand total (' . ksh($totals['grand_total']) . ') less than the ' . ksh($paid) . ' already paid.'
                : 'Amount paid (' . ksh($paid) . ') cannot be more than the grand total (' . ksh($totals['grand_total']) . ').';
            return array('success' => false, 'messages' => $msg);
        }
        $due = round($totals['grand_total'] - $paid, 2);
        $paymentStatus = derive_payment_status($paid, $totals['grand_total']);

        $f = array(
            'sub'   => money_db($totals['sub_total']),
            'vat'   => money_db($totals['vat']),
            'total' => money_db($totals['total_amount']),
            'disc'  => money_db($d['discount']),
            'grand' => money_db($totals['grand_total']),
            'paid'  => money_db($paid),
            'due'   => money_db($due),
        );
        $userId = current_user_id();

        if($isEdit) {
            $stmt = $connect->prepare(
                "UPDATE orders SET order_date = ?, expect_return_date = ?, returned_date = ?, site_location = ?,
                    client_name = ?, client_contact = ?, driver_name = ?, driver_contact = ?, returned_by = ?,
                    returned_by_contact = ?, approved_by = ?, sub_total = ?, vat = ?, total_amount = ?, discount = ?,
                    late_fee = ?, grand_total = ?, paid = ?, due = ?, payment_status = ?, payment_place = ?, gstn = ?,
                    order_status = ?
                 WHERE order_id = ?"
            );
            $stmt->bind_param('sssssssssssssssdsssiisii',
                $d['order_date'], $d['expect_return_date'], $d['returned_date'], $d['site_location'],
                $d['client_name'], $d['client_contact'], $d['driver_name'], $d['driver_contact'], $d['returned_by'],
                $d['returned_by_contact'], $d['approved_by'], $f['sub'], $f['vat'], $f['total'], $f['disc'],
                $lateFee, $f['grand'], $f['paid'], $f['due'], $paymentStatus, $d['branch'], $d['client_kra_pin'],
                $d['order_status'], $orderId);
            $stmt->execute();
            $stmt->close();

            $stmt = $connect->prepare("DELETE FROM order_item WHERE order_id = ?");
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $connect->prepare(
                "INSERT INTO orders (order_date, expect_return_date, returned_date, site_location, client_name,
                    client_contact, driver_name, driver_contact, returned_by, returned_by_contact, approved_by,
                    sub_total, vat, total_amount, discount, late_fee, grand_total, paid, due, payment_type,
                    payment_reference, payment_status, payment_place, gstn, order_status, user_id)
                 VALUES (?, ?, NULL, ?, ?, ?, ?, ?, '', '', '', ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)"
            );
            $stmt->bind_param('ssssssssssssssisiisi',
                $d['order_date'], $d['expect_return_date'], $d['site_location'], $d['client_name'],
                $d['client_contact'], $d['driver_name'], $d['driver_contact'],
                $f['sub'], $f['vat'], $f['total'], $f['disc'], $f['grand'], $f['paid'], $f['due'],
                $d['payment_type'], $d['payment_reference'], $paymentStatus, $d['branch'], $d['client_kra_pin'], $userId);
            $stmt->execute();
            $orderId = (int)$connect->insert_id;
            $stmt->close();

            if($paid > 0) {
                record_payment_history($connect, $orderId, $paid, $d['payment_type'], $d['payment_reference'], $userId);
            }
        }

        $stmt = $connect->prepare(
            "INSERT INTO order_item (order_id, product_id, quantity, rental_days, rate, total, order_item_status)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $itemStatus = $d['order_status'] === 2 ? 2 : 1;
        foreach($d['items'] as $item) {
            $qtyStr = (string)$item['quantity'];
            $rateStr = money_db($item['rate']);
            $lineStr = money_db((float)$item['rate'] * $item['rental_days'] * $item['quantity']);
            $stmt->bind_param('iisissi', $orderId, $item['product_id'], $qtyStr, $item['rental_days'], $rateStr, $lineStr, $itemStatus);
            $stmt->execute();
        }
        $stmt->close();

        $connect->commit();
    } catch(Throwable $e) {
        $connect->rollback();
        throw $e;
    }

    if($isEdit) {
        $message = 'Order #' . $orderId . ' has been updated.';
        if($lateFee > 0) {
            $message .= ' A late-return charge of ' . ksh($lateFee) . ' was applied.';
        }
    } else {
        $message = 'Order #' . $orderId . ' has been created.';
    }
    return array('success' => true, 'messages' => $message, 'order_id' => $orderId);
}

/** Money as stored in the (varchar) money columns: plain 2-decimal string. */
function money_db($amount) {
    return number_format((float)$amount, 2, '.', '');
}

function record_payment_history($connect, $orderId, $amount, $paymentType, $reference, $userId) {
    $stmt = $connect->prepare(
        "INSERT INTO payment_history (order_id, amount, payment_type, reference, payment_date, received_by)
         VALUES (?, ?, ?, ?, NOW(), ?)"
    );
    $amountStr = money_db($amount);
    $stmt->bind_param('isisi', $orderId, $amountStr, $paymentType, $reference, $userId);
    $stmt->execute();
    $stmt->close();
}
