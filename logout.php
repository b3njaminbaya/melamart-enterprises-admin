<?php

require_once 'php_action/core.php';

// remove all session variables
session_unset();

// expire the session cookie, then destroy the session
if(ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: index.php');
exit();
