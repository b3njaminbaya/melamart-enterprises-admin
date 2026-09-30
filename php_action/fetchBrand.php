<?php
require_once 'core.php';

require_admin();

$result = $connect->query("SELECT t.brand_id, t.brand_name, t.brand_active,
        (SELECT COUNT(*) FROM product p WHERE p.brand_id = t.brand_id AND p.status = 1) AS product_count
    FROM brands t WHERE t.brand_status = 1 ORDER BY t.brand_name");

$output = array('data' => array());
while($row = $result->fetch_assoc()) {
    $id = (int)$row['brand_id'];
    $active = (int)$row['brand_active'] === 1
        ? "<span class='label label-success'>Available</span>"
        : "<span class='label label-default'>Not available</span>";

    $button = '<div class="btn-group">
	  <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    Action <span class="caret"></span>
	  </button>
	  <ul class="dropdown-menu dropdown-menu-right">
	    <li><a href="#" onclick="editBrand(' . $id . '); return false;"><i class="glyphicon glyphicon-edit"></i> Edit</a></li>
	    <li><a href="#" onclick="removeBrand(' . $id . '); return false;"><i class="glyphicon glyphicon-trash"></i> Remove</a></li>
	  </ul>
	</div>';

    $output['data'][] = array(h($row['brand_name']), (int)$row['product_count'], $active, $button);
}

json_out($output);
