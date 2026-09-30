<?php

require_once __DIR__ . '/helpers.php';

mel_start_session();

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/csrf.php';  // makes csrf_token(), csrf_field(), csrf_verify() available everywhere

// Dates written with NOW() must be in Kenyan time too.
$connect->query("SET time_zone = '+03:00'");
// Throw on SQL errors so transactions roll back instead of half-saving.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Unexpected errors: log the details, show staff a plain message (never SQL).
set_exception_handler(function($e) {
    error_log('[melamart-admin] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    $inActionDir = basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'php_action';
    if(is_ajax() || $inActionDir) {
        json_out(array('success' => false, 'messages' => 'Something went wrong on the server. Please try again, or contact the administrator if it keeps happening.'), 500);
    }
    http_response_code(500);
    echo '<div class="alert alert-danger" style="margin:20px;">Something went wrong while loading this page. Please try again.</div>';
});

/*
 * Not logged in: AJAX/handler calls get a JSON 401 (the page shows a
 * "session expired" message and redirects); pages redirect to the login.
 */
if(!isset($_SESSION['userId'])) {
    $inActionDir = basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'php_action';
    if(is_ajax()) {
        json_out(array('success' => false, 'messages' => 'Your session has expired. Please log in again.'), 401);
    }
    header('Location: ' . ($inActionDir ? '../index.php' : 'index.php'));
    exit();
}
