<?php
/**
 * Database connection template.
 *
 * Copy this file to db_connect.php and fill in your credentials.
 * db_connect.php is gitignored — never commit real credentials.
 *
 * Use a restricted MySQL user for the app (SELECT, INSERT, UPDATE, DELETE
 * on this database only) — never root. See docs/DEPLOYMENT.md.
 */

$localhost = "localhost";
$username  = "your_app_db_user";
$password  = "your_app_db_password";
$dbname    = "store";

try {
    $connect = new mysqli($localhost, $username, $password, $dbname);
    $connect->set_charset('utf8mb4');
} catch(mysqli_sql_exception $e) {
    // Log the real reason; never show connection details to visitors.
    error_log('[melamart-admin] Database connection failed: ' . $e->getMessage());
    http_response_code(503);
    exit('The system is temporarily unavailable. Please try again in a few minutes.');
}
