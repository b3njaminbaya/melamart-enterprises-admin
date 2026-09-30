<?php
/*
 * Printable invoice / delivery note. Opened in a new tab: printOrder.php?id=123
 */
require_once 'core.php';

$orderId = (int)($_GET['id'] ?? 0);

$stmt = $connect->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$order) {
    http_response_code(404);
    echo '<p style="font-family:sans-serif;padding:30px;">Order not found.</p>';
    exit();
}

$stmt = $connect->prepare(
    "SELECT oi.quantity, oi.rental_days, oi.rate, oi.total, p.product_name
     FROM order_item oi
     INNER JOIN product p ON p.product_id = oi.product_id
     WHERE oi.order_id = ?
     ORDER BY oi.order_item_id"
);
$stmt->bind_param('i', $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $connect->prepare("SELECT amount, payment_type, reference, payment_date FROM payment_history WHERE order_id = ? ORDER BY payment_date, payment_id");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$branch = $MEL_BRANCHES[(int)$order['payment_place']] ?? reset($MEL_BRANCHES);
$scheduledDays = hire_days($order['order_date'], $order['expect_return_date']);
$actualDays = has_date($order['returned_date']) ? hire_days($order['order_date'], $order['returned_date']) : null;
$vat = (float)$order['vat'];
$lateFee = (float)$order['late_fee'];
$discount = (float)$order['discount'];
$isCancelled = (int)$order['order_status'] === 2;
$title = MEL_VAT_RATE > 0 || $vat > 0 ? 'TAX INVOICE' : 'INVOICE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice #<?php echo $orderId; ?> — <?php echo h($order['client_name']); ?></title>
<style>
  * { box-sizing: border-box; }
  body { font-family: 'Open Sans', Arial, sans-serif; color: #1a2634; font-size: 12.5px; margin: 0; background: #f7f9fb; }
  .page { max-width: 820px; margin: 20px auto; background: #fff; padding: 32px 36px; box-shadow: 0 4px 16px rgba(11,61,94,.12); }
  .toolbar { max-width: 820px; margin: 16px auto 0; text-align: right; }
  .toolbar button { background: #0b3d5e; color: #fff; border: 0; border-radius: 6px; padding: 9px 18px; font-weight: 600; cursor: pointer; margin-left: 8px; }
  .toolbar button.secondary { background: #f5a800; color: #072e46; }
  header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 4px solid #f5a800; padding-bottom: 16px; }
  header img { height: 64px; }
  .company { text-align: right; line-height: 1.55; }
  .company strong { font-size: 15px; color: #0b3d5e; }
  h1 { font-size: 20px; color: #0b3d5e; letter-spacing: 1px; margin: 20px 0 4px; }
  .cancelled { color: #c62828; font-weight: 700; font-size: 14px; }
  .meta { display: flex; gap: 24px; margin: 14px 0 18px; }
  .meta > div { flex: 1; background: #f7f9fb; border: 1px solid #dde3ea; border-radius: 6px; padding: 10px 12px; line-height: 1.6; }
  .meta h3 { margin: 0 0 4px; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #697587; }
  table { width: 100%; border-collapse: collapse; }
  table.items th { background: #0b3d5e; color: #fff; text-align: left; padding: 8px; font-size: 11.5px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  table.items td { border-bottom: 1px solid #dde3ea; padding: 8px; }
  .num, table.items th.num { text-align: right; white-space: nowrap; }
  .totals { width: 320px; margin-left: auto; margin-top: 12px; }
  .totals td { padding: 5px 8px; }
  .totals tr.grand td { border-top: 2px solid #0b3d5e; font-weight: 700; font-size: 14px; color: #0b3d5e; }
  .totals tr.balance td { font-weight: 700; }
  .words { margin-top: 10px; font-style: italic; }
  .section-title { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #697587; margin: 20px 0 6px; }
  .terms { font-size: 11px; color: #455566; line-height: 1.6; padding-left: 18px; margin: 0; }
  .signatures { display: flex; gap: 40px; margin-top: 36px; }
  .signatures div { flex: 1; border-top: 1px solid #1a2634; padding-top: 6px; font-size: 11.5px; }
  footer { margin-top: 24px; text-align: center; font-size: 11px; color: #697587; }
  @media print {
    body { background: #fff; }
    .page { box-shadow: none; margin: 0; max-width: none; padding: 0; }
    .toolbar { display: none; }
  }
</style>
</head>
<body>

<div class="toolbar">
  <button type="button" class="secondary" onclick="window.close()">Close</button>
  <button type="button" onclick="window.print()">Print</button>
</div>

<div class="page">
  <header>
    <img src="../images/logo.jpeg" alt="<?php echo h(MEL_COMPANY_NAME); ?>">
    <div class="company">
      <strong><?php echo h(MEL_COMPANY_NAME); ?></strong><br>
      <?php echo h($branch['name']); ?> Branch — <?php echo h($branch['address']); ?><br>
      Tel: <?php echo h($branch['phone']); ?> · <?php echo h(MEL_COMPANY_EMAIL); ?><br>
      <?php if(MEL_KRA_PIN !== '') { ?>KRA PIN: <?php echo h(MEL_KRA_PIN); ?><?php } ?>
    </div>
  </header>

  <h1><?php echo $title; ?> / DELIVERY NOTE</h1>
  <div>Invoice No. <strong><?php echo $orderId; ?></strong> · Date: <strong><?php echo h(format_date($order['order_date'])); ?></strong></div>
  <?php if($isCancelled) { ?><div class="cancelled">THIS ORDER HAS BEEN CANCELLED</div><?php } ?>

  <div class="meta">
    <div>
      <h3>Bill to</h3>
      <strong><?php echo h($order['client_name']); ?></strong><br>
      Tel: <?php echo h(format_phone($order['client_contact'])); ?><br>
      <?php if($order['gstn'] !== '') { ?>KRA PIN: <?php echo h($order['gstn']); ?><br><?php } ?>
      Site: <?php echo h($order['site_location']); ?>
    </div>
    <div>
      <h3>Hire details</h3>
      Hire period: <?php echo h(format_date($order['order_date'])); ?> – <?php echo h(format_date($order['expect_return_date'])); ?> (<?php echo $scheduledDays; ?> day<?php echo $scheduledDays === 1 ? '' : 's'; ?>)<br>
      Returned: <?php echo has_date($order['returned_date']) ? h(format_date($order['returned_date'])) . ' (' . $actualDays . ' day' . ($actualDays === 1 ? '' : 's') . ')' : 'Not yet returned'; ?><br>
      Driver: <?php echo h($order['driver_name']); ?> (<?php echo h(format_phone($order['driver_contact'])); ?>)<br>
      <?php if($order['returned_by'] !== '') { ?>Returned by: <?php echo h($order['returned_by']); ?><?php echo $order['returned_by_contact'] !== '' ? ' (' . h(format_phone($order['returned_by_contact'])) . ')' : ''; ?><br><?php } ?>
      <?php if($order['approved_by'] !== '') { ?>Approved by: <?php echo h($order['approved_by']); ?><?php } ?>
    </div>
  </div>

  <table class="items">
    <thead>
      <tr>
        <th style="width:32px;">#</th>
        <th>Description</th>
        <th class="num">Qty</th>
        <th class="num">Days</th>
        <th class="num">Daily rate (KSh)</th>
        <th class="num">Amount (KSh)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $i => $item) { ?>
      <tr>
        <td><?php echo $i + 1; ?></td>
        <td><?php echo h($item['product_name']); ?></td>
        <td class="num"><?php echo (int)$item['quantity']; ?></td>
        <td class="num"><?php echo (int)$item['rental_days']; ?></td>
        <td class="num"><?php echo money($item['rate']); ?></td>
        <td class="num"><?php echo money($item['total']); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

  <table class="totals">
    <tr><td>Sub total</td><td class="num"><?php echo money($order['sub_total']); ?></td></tr>
    <?php if($vat > 0) { ?>
    <tr><td>VAT (<?php echo h(rtrim(rtrim(number_format((float)$vat / max((float)$order['sub_total'], 0.01) * 100, 2), '0'), '.')); ?>%)</td><td class="num"><?php echo money($vat); ?></td></tr>
    <?php } ?>
    <?php if($lateFee > 0) { ?>
    <tr><td>Late return charge</td><td class="num"><?php echo money($lateFee); ?></td></tr>
    <?php } ?>
    <?php if($discount > 0) { ?>
    <tr><td>Discount</td><td class="num">−<?php echo money($discount); ?></td></tr>
    <?php } ?>
    <tr class="grand"><td>Grand total</td><td class="num">KSh <?php echo money($order['grand_total']); ?></td></tr>
    <tr><td>Paid</td><td class="num"><?php echo money($order['paid']); ?></td></tr>
    <tr class="balance"><td>Balance due</td><td class="num">KSh <?php echo money($order['due']); ?></td></tr>
  </table>

  <div class="words">Amount in words: <?php echo h(amount_in_words($order['grand_total'])); ?></div>

  <?php if($payments) { ?>
  <div class="section-title">Payments received</div>
  <table class="items">
    <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th class="num">Amount (KSh)</th></tr></thead>
    <tbody>
      <?php foreach($payments as $p) { ?>
      <tr>
        <td><?php echo h(date('d/m/Y', strtotime($p['payment_date']))); ?></td>
        <td><?php echo h(payment_type_label($p['payment_type'])); ?></td>
        <td><?php echo h($p['reference']); ?></td>
        <td class="num"><?php echo money($p['amount']); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <?php } ?>

  <div class="section-title">Terms &amp; conditions</div>
  <ol class="terms">
    <?php foreach($MEL_INVOICE_TERMS as $term) { ?><li><?php echo h($term); ?></li><?php } ?>
  </ol>

  <div class="signatures">
    <div>For <?php echo h(MEL_COMPANY_NAME); ?> (authorised signatory)</div>
    <div>Received by (name, signature &amp; date)</div>
  </div>

  <footer>Thank you for your business. · Printed <?php echo date('d/m/Y H:i'); ?></footer>
</div>

<script>
  window.addEventListener('load', function() { window.print(); });
</script>
</body>
</html>
