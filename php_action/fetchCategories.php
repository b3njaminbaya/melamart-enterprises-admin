<?php
require_once 'core.php';

require_admin();

$result = $connect->query("SELECT t.categories_id, t.categories_name, t.categories_active,
        (SELECT COUNT(*) FROM product p WHERE p.categories_id = t.categories_id AND p.status = 1) AS product_count
    FROM categories t WHERE t.categories_status = 1 ORDER BY t.categories_name");

$output = array('data' => array());
while($row = $result->fetch_assoc()) {
    $id = (int)$row['categories_id'];
    $active = (int)$row['categories_active'] === 1
        ? "<span class='label label-success'>Available</span>"
        : "<span class='label label-default'>Not available</span>";

    $button = '<div class="btn-group">
	  <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    Action <span class="caret"></span>
	  </button>
	  <ul class="dropdown-menu dropdown-menu-right">
	    <li><a href="#" onclick="editCategory(' . $id . '); return false;"><i class="glyphicon glyphicon-edit"></i> Edit</a></li>
	    <li><a href="#" onclick="removeCategory(' . $id . '); return false;"><i class="glyphicon glyphicon-trash"></i> Remove</a></li>
	  </ul>
	</div>';

    $output['data'][] = array(h($row['categories_name']), (int)$row['product_count'], $active, $button);
}

json_out($output);
