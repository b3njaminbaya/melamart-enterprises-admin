<?php
/*
 * Order report as CSV (opens in Excel). Same filters as the on-screen report.
 */
require_once 'core.php';
require_once 'report_query.php';

require_admin();

list($filters, $error) = read_report_filters($_GET);
if($error) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo $error;
    exit();
}

$rows = fetch_report_rows($connect, $filters);
$filename = 'melamart-orders-' . $filters['start'] . '-to-' . $filters['end'] . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows names correctly

// Spreadsheet apps execute cells starting with = + - @ — neutralise them.
$safe = function($value) {
    $value = (string)$value;
    return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
};

fputcsv($out, array('Order #', 'Order date', 'Expected return', 'Returned', 'Branch', 'Site', 'Client', 'Client phone', 'Client KRA PIN',
    'Driver', 'Sub total (KSh)', 'VAT (KSh)', 'Late charge (KSh)', 'Discount (KSh)', 'Grand total (KSh)', 'Paid (KSh)', 'Balance (KSh)',
    'Payment method', 'Payment status', 'Order status'));

foreach($rows as $r) {
    $d = report_row_display($r);
    fputcsv($out, array(
        $d['order_id'], $d['order_date'], $d['expect_return_date'], $d['returned_date'], $d['branch'],
        $safe($d['site_location']), $safe($d['client_name']), $safe($d['client_contact']), $safe($r['gstn']), $safe($d['driver_name']),
        number_format((float)$r['sub_total'], 2, '.', ''), number_format((float)$r['vat'], 2, '.', ''),
        number_format((float)$r['late_fee'], 2, '.', ''), number_format((float)$r['discount'], 2, '.', ''),
        number_format((float)$r['grand_total'], 2, '.', ''), number_format((float)$r['paid'], 2, '.', ''),
        number_format((float)$r['due'], 2, '.', ''),
        $d['payment_type'], $d['payment_status'], $d['order_status'],
    ));
}
fclose($out);
