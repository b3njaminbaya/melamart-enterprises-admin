<?php
/*
 * report_query.php — shared filter + query for the order report
 * (on-screen view, printable report and CSV export).
 */

/**
 * Reads the report filters from an input array ($_GET or $_POST).
 * Returns [filters, error].
 */
function read_report_filters($input) {
    $start = parse_date_input($input['startDate'] ?? '');
    $end = parse_date_input($input['endDate'] ?? '');
    if(!$start || !$end) {
        return array(null, 'Enter a start and end date as dd/mm/yyyy.');
    }
    if($end < $start) {
        return array(null, 'The end date cannot be before the start date.');
    }
    $filters = array(
        'start'          => $start,
        'end'            => $end,
        'client'         => trim($input['searchClient'] ?? ''),
        'branch'         => (string)($input['branchFilter'] ?? ''),
        'payment_status' => (string)($input['paymentStatusFilter'] ?? ''),
        'order_status'   => (string)($input['orderStatusFilter'] ?? ''),
        'payment_type'   => (string)($input['paymentTypeFilter'] ?? ''),
    );
    return array($filters, null);
}

/** Orders matching the filters (cancelled orders only when asked for). */
function fetch_report_rows($connect, $f) {
    $sql = "SELECT * FROM orders WHERE order_date >= ? AND order_date <= ?";
    $types = 'ss';
    $params = array($f['start'], $f['end']);

    if($f['order_status'] !== '') {
        $sql .= " AND order_status = ?";
        $types .= 'i';
        $params[] = (int)$f['order_status'];
    } else {
        $sql .= " AND order_status != 2";
    }
    if($f['client'] !== '') {
        $sql .= " AND client_name LIKE ?";
        $types .= 's';
        $params[] = '%' . $f['client'] . '%';
    }
    if($f['branch'] !== '') {
        $sql .= " AND payment_place = ?";
        $types .= 'i';
        $params[] = (int)$f['branch'];
    }
    if($f['payment_status'] !== '') {
        $sql .= " AND payment_status = ?";
        $types .= 'i';
        $params[] = (int)$f['payment_status'];
    }
    if($f['payment_type'] !== '') {
        $sql .= " AND payment_type = ?";
        $types .= 'i';
        $params[] = (int)$f['payment_type'];
    }
    $sql .= " ORDER BY order_date DESC, order_id DESC";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function report_summary($rows) {
    $summary = array('total_orders' => count($rows), 'sub_total' => 0, 'vat' => 0, 'late_fee' => 0, 'discount' => 0, 'grand_total' => 0, 'paid' => 0, 'due' => 0);
    foreach($rows as $r) {
        foreach(array('sub_total', 'vat', 'late_fee', 'discount', 'grand_total', 'paid', 'due') as $k) {
            $summary[$k] += (float)$r[$k];
        }
    }
    return $summary;
}

/** One report row in display form (plain text, not escaped). */
function report_row_display($r) {
    return array(
        'order_id'           => (int)$r['order_id'],
        'order_date'         => format_date($r['order_date']),
        'expect_return_date' => format_date($r['expect_return_date']),
        'returned_date'      => has_date($r['returned_date']) ? format_date($r['returned_date']) : ((int)$r['order_status'] === 2 ? '' : 'On hire'),
        'branch'             => branch_label($r['payment_place']),
        'site_location'      => $r['site_location'],
        'client_name'        => $r['client_name'],
        'client_contact'     => format_phone($r['client_contact']),
        'driver_name'        => $r['driver_name'],
        'grand_total'        => money($r['grand_total']),
        'paid'               => money($r['paid']),
        'due'                => money($r['due']),
        'payment_type'       => (float)$r['paid'] > 0 ? payment_type_label($r['payment_type']) : '',
        'payment_status'     => payment_status_label($r['payment_status']),
        'order_status'       => order_status_label($r['order_status']),
    );
}
