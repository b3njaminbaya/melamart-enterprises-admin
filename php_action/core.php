<?php

session_start();

require_once 'db_connect.php';
require_once 'csrf.php';  // makes csrf_token(), csrf_field(), csrf_verify() available everywhere

/*
 * Use isset() instead of a falsy check (!$_SESSION['userId']).
 * A falsy check incorrectly redirects if user_id is 0 (falsy).
 * Use a relative redirect so it works on any hostname / path prefix.
 */
if(!isset($_SESSION['userId'])) {
    header('Location: ../index.php');
    exit();
}
