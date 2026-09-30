<?php
require_once 'core.php';
require_once 'report_query.php';

require_admin();

list($filters, $error) = read_report_filters($_POST);
if($error) {
    json_out(array('success' => false, 'messages' => $error));
}

$rows = fetch_report_rows($connect, $filters);
$data = array_map('report_row_display', $rows);
$summary = report_summary($rows);

json_out(array(
    'success' => true,
    'data'    => $data,
    'summary' => array(
        'total_orders'  => $summary['total_orders'],
        'total_revenue' => money($summary['grand_total']),
        'total_paid'    => money($summary['paid']),
        'total_due'     => money($summary['due']),
    ),
));
