<?php
require_once 'core.php';

require_admin();

$result = $connect->query("SELECT u.user_id, u.username, u.email,
        (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.user_id) AS order_count
    FROM users u ORDER BY u.user_id");

$output = array('data' => array());
while($row = $result->fetch_assoc()) {
    $userId = (int)$row['user_id'];
    $name = h($row['username']);
    if($userId === 1) {
        $name .= ' <span class="label label-info">Administrator</span>';
    }
    $actions = '<li><a href="#" onclick="editUser(' . $userId . '); return false;"><i class="glyphicon glyphicon-edit"></i> Edit</a></li>';
    if($userId !== 1 && $userId !== current_user_id()) {
        $actions .= '<li><a href="#" onclick="removeUser(' . $userId . '); return false;"><i class="glyphicon glyphicon-trash"></i> Remove</a></li>';
    }
    $button = '<div class="btn-group">
	  <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    Action <span class="caret"></span>
	  </button>
	  <ul class="dropdown-menu dropdown-menu-right">' . $actions . '</ul>
	</div>';

    $output['data'][] = array($name, h($row['email']), (int)$row['order_count'], $button);
}

json_out($output);
