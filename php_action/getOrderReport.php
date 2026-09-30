<?php
/*
 * Printable order report. Opened in a new tab with the report filters as GET parameters.
 */
require_once 'core.php';
require_once 'report_query.php';

require_admin();

list($filters, $error) = read_report_filters($_GET);
if($error) {
    http_response_code(400);
    echo '<p style="font-family:sans-serif;padding:30px;">' . h($error) . '</p>';
    exit();
}

$rows = fetch_report_rows($connect, $filters);
$summary = report_summary($rows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order report <?php echo h(format_date($filters['start'])); ?> – <?php echo h(format_date($filters['end'])); ?></title>
<style>
  body { font-family: 'Open Sans', Arial, sans-serif; color: #1a2634; font-size: 11px; margin: 20px; }
  h2 { color: #0b3d5e; margin: 0; }
  .head { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #f5a800; padding-bottom: 8px; margin-bottom: 12px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #0b3d5e; color: #fff; text-align: left; padding: 6px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  td { border-bottom: 1px solid #dde3ea; padding: 5px 6px; }
  .num { text-align: right; white-space: nowrap; }
  tfoot td { font-weight: 700; border-top: 2px solid #0b3d5e; }
  .toolbar { text-align: right; margin-bottom: 10px; }
  .toolbar button { background: #0b3d5e; color: #fff; border: 0; border-radius: 6px; padding: 8px 16px; cursor: pointer; }
  @media print { .toolbar { display: none; } body { margin: 0; } @page { size: landscape; } }
</style>
</head>
<body>
<div class="toolbar"><button type="button" onclick="window.print()">Print</button></div>
<div class="head">
  <div>
    <h2><?php echo h(MEL_COMPANY_NAME); ?> — Order report</h2>
    Period: <?php echo h(format_date($filters['start'])); ?> to <?php echo h(format_date($filters['end'])); ?>
  </div>
  <div>Generated <?php echo date('d/m/Y H:i'); ?></div>
</div>

<table>
  <thead>
    <tr>
      <th>#</th><th>Order date</th><th>Expected</th><th>Returned</th><th>Branch</th><th>Site</th><th>Client</th><th>Phone</th>
      <th class="num">Grand total</th><th class="num">Paid</th><th class="num">Balance</th><th>Payment</th><th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php if(!$rows) { ?>
    <tr><td colspan="13" style="text-align:center;padding:20px;">No orders match these filters.</td></tr>
    <?php } ?>
    <?php foreach($rows as $r) { $d = report_row_display($r); ?>
    <tr>
      <td><?php echo $d['order_id']; ?></td>
      <td><?php echo h($d['order_date']); ?></td>
      <td><?php echo h($d['expect_return_date']); ?></td>
      <td><?php echo h($d['returned_date']); ?></td>
      <td><?php echo h($d['branch']); ?></td>
      <td><?php echo h($d['site_location']); ?></td>
      <td><?php echo h($d['client_name']); ?></td>
      <td><?php echo h($d['client_contact']); ?></td>
      <td class="num"><?php echo h($d['grand_total']); ?></td>
      <td class="num"><?php echo h($d['paid']); ?></td>
      <td class="num"><?php echo h($d['due']); ?></td>
      <td><?php echo h($d['payment_status']); ?></td>
      <td><?php echo h($d['order_status']); ?></td>
    </tr>
    <?php } ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="8"><?php echo (int)$summary['total_orders']; ?> order(s) — totals (KSh)</td>
      <td class="num"><?php echo money($summary['grand_total']); ?></td>
      <td class="num"><?php echo money($summary['paid']); ?></td>
      <td class="num"><?php echo money($summary['due']); ?></td>
      <td colspan="2"></td>
    </tr>
  </tfoot>
</table>

<p style="margin-top:14px;">
  Sub total: KSh <?php echo money($summary['sub_total']); ?> ·
  <?php if($summary['vat'] > 0) { ?>VAT: KSh <?php echo money($summary['vat']); ?> · <?php } ?>
  Late return charges: KSh <?php echo money($summary['late_fee']); ?> ·
  Discounts: KSh <?php echo money($summary['discount']); ?>
</p>

<script>window.addEventListener('load', function() { window.print(); });</script>
</body>
</html>
