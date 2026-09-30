<?php
/*
 * Equipment table data. Shows every product that has not been removed,
 * including ones with 0 in store (all out on hire) so they can be restocked.
 */
require_once 'core.php';

require_admin();

$sql = "SELECT p.product_id, p.product_name, p.product_image, p.quantity, p.rate, p.daily_rate, p.active,
               b.brand_name, c.categories_name,
               COALESCE(h.on_hire, 0) AS on_hire
        FROM product p
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN categories c ON p.categories_id = c.categories_id
        LEFT JOIN (
            SELECT oi.product_id, SUM(oi.quantity) AS on_hire
            FROM order_item oi
            INNER JOIN orders o ON o.order_id = oi.order_id
            WHERE o.order_status != 2 AND o.returned_date IS NULL
            GROUP BY oi.product_id
        ) h ON h.product_id = p.product_id
        WHERE p.status = 1
        ORDER BY p.product_name";
$result = $connect->query($sql);

$output = array('data' => array());
while($row = $result->fetch_assoc()) {
    $productId = (int)$row['product_id'];
    $inStore = (int)$row['quantity'];

    $active = (int)$row['active'] === 1
        ? "<span class='label label-success'>Available</span>"
        : "<span class='label label-default'>Not available</span>";

    $stock = $inStore;
    if($inStore === 0) {
        $stock = "<span class='label label-danger'>0</span>";
    } elseif($inStore <= 3) {
        $stock = "<span class='label label-warning'>" . $inStore . "</span>";
    }

    $button = '<div class="btn-group">
	  <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    Action <span class="caret"></span>
	  </button>
	  <ul class="dropdown-menu dropdown-menu-right">
	    <li><a href="#" onclick="editProduct(' . $productId . '); return false;"><i class="glyphicon glyphicon-edit"></i> Edit</a></li>
	    <li><a href="#" onclick="removeProduct(' . $productId . '); return false;"><i class="glyphicon glyphicon-trash"></i> Remove</a></li>
	  </ul>
	</div>';

    // Stored as '../assets/images/stock/x.jpg' (relative to php_action/)
    $imageUrl = preg_replace('#^\.\./#', '', (string)$row['product_image']);
    if($imageUrl === '') {
        $imageUrl = 'assets/images/photo_default.png';
    }

    $output['data'][] = array(
        "<img class='img-rounded' src='" . h($imageUrl) . "' alt='' style='height:36px; width:54px; object-fit:cover;' />",
        h($row['product_name']),
        array('display' => money($row['daily_rate']), 'sort' => (float)$row['daily_rate']),
        array('display' => (string)$stock, 'sort' => $inStore),
        (int)$row['on_hire'],
        h($row['brand_name'] ?? '—'),
        h($row['categories_name'] ?? '—'),
        $active,
        $button,
    );
}

json_out($output);
