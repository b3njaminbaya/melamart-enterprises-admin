<?php
/*
 * Manage Orders table data. ?status= onhire | overdue | completed | cancelled | all
 * (default: everything except cancelled). All text is HTML-escaped here.
 */
require_once 'core.php';

$filter = $_GET['status'] ?? '';
$today = date('Y-m-d');

$where = "o.order_status != 2";
switch($filter) {
    case 'onhire':    $where = "o.order_status = 0 AND o.returned_date IS NULL"; break;
    case 'overdue':   $where = "o.order_status = 0 AND o.returned_date IS NULL AND o.expect_return_date < '" . $today . "'"; break;
    case 'completed': $where = "o.order_status = 1"; break;
    case 'cancelled': $where = "o.order_status = 2"; break;
    case 'all':       $where = "1 = 1"; break;
}

$sql = "SELECT o.order_id, o.order_date, o.expect_return_date, o.returned_date, o.site_location, o.client_name,
               o.client_contact, o.grand_total, o.paid, o.due, o.payment_status, o.payment_place, o.order_status
        FROM orders o
        WHERE $where
        ORDER BY o.order_id DESC";
$result = $connect->query($sql);

$statusLabels = array(0 => 'label-info', 1 => 'label-success', 2 => 'label-default');
$paymentLabels = array(1 => 'label-success', 2 => 'label-warning', 3 => 'label-danger');

$output = array('data' => array());
while($row = $result->fetch_assoc()) {
    $orderId = (int)$row['order_id'];
    $status = (int)$row['order_status'];
    $onHire = $status === 0 && !has_date($row['returned_date']);
    $overdueDays = 0;
    if($onHire && $row['expect_return_date'] < $today) {
        $overdueDays = hire_days($row['expect_return_date'], $today) - 1;
    }

    $expected = h(format_date($row['expect_return_date']));
    if($overdueDays > 0) {
        $expected .= ' <span class="label label-danger" title="Not yet returned">' . $overdueDays . ' day' . ($overdueDays === 1 ? '' : 's') . ' overdue</span>';
    }

    $returned = has_date($row['returned_date'])
        ? h(format_date($row['returned_date']))
        : '<span class="text-muted">' . ($status === 2 ? '—' : 'On hire') . '</span>';

    $paymentStatus = (int)$row['payment_status'];
    $due = (float)$row['due'];

    $actions = '<li><a href="orders.php?o=editOrd&i=' . $orderId . '"><i class="glyphicon glyphicon-edit"></i> ' . ($onHire ? 'Edit / Record return' : 'View / Edit') . '</a></li>';
    if($status !== 2 && $due > 0) {
        $actions .= '<li><a href="#" onclick="paymentOrder(' . $orderId . '); return false;"><i class="glyphicon glyphicon-usd"></i> Record payment</a></li>';
    } elseif($status !== 2) {
        $actions .= '<li><a href="#" onclick="paymentOrder(' . $orderId . '); return false;"><i class="glyphicon glyphicon-list-alt"></i> Payment history</a></li>';
    }
    $actions .= '<li><a href="#" onclick="printOrder(' . $orderId . '); return false;"><i class="glyphicon glyphicon-print"></i> Print invoice</a></li>';
    if($status === 0) {
        $actions .= '<li role="separator" class="divider"></li>'
                  . '<li><a href="#" style="color:#c62828;" onclick="removeOrder(' . $orderId . '); return false;"><i class="glyphicon glyphicon-ban-circle"></i> Cancel order</a></li>';
    }
    $button = '<div class="btn-group">
	  <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    Action <span class="caret"></span>
	  </button>
	  <ul class="dropdown-menu dropdown-menu-right">' . $actions . '</ul>
	</div>';

    $output['data'][] = array(
        'id'        => array('display' => '<strong>#' . $orderId . '</strong>', 'sort' => $orderId),
        'date'      => array('display' => h(format_date($row['order_date'])), 'sort' => $row['order_date']),
        'client'    => h($row['client_name']) . '<span class="sub"><a href="tel:' . h($row['client_contact']) . '">' . h(format_phone($row['client_contact'])) . '</a></span>',
        'site'      => h($row['site_location']) . '<span class="sub">' . h(branch_label($row['payment_place'])) . ' branch</span>',
        'expected'  => array('display' => $expected, 'sort' => $row['expect_return_date']),
        'returned'  => array('display' => $returned, 'sort' => (string)$row['returned_date']),
        'total'     => array('display' => money($row['grand_total']), 'sort' => (float)$row['grand_total']),
        'paid'      => array('display' => money($row['paid']), 'sort' => (float)$row['paid']),
        'due'       => array('display' => $due > 0 ? '<strong>' . money($due) . '</strong>' : money($due), 'sort' => $due),
        'payment'   => '<span class="label ' . ($paymentLabels[$paymentStatus] ?? 'label-default') . '">' . h(payment_status_label($paymentStatus)) . '</span>',
        'status'    => '<span class="label ' . ($statusLabels[$status] ?? 'label-default') . '">' . h(order_status_label($status)) . '</span>',
        'action'    => $button,
    );
}

json_out($output);
