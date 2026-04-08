<?php

/**
 * Database connection template.
 *
 * Copy this file to db_connect.php and fill in your local credentials.
 * db_connect.php is gitignored — never commit real credentials.
 */

$localhost = "localhost";
$username  = "your_db_user";
$password  = "your_db_password";
$dbname    = "store";

$connect = new mysqli($localhost, $username, $password, $dbname);

if ($connect->connect_error) {
    die("Connection Failed: " . $connect->connect_error);
}
