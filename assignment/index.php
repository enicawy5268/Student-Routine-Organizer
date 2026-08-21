<?php
session_start();

$is_logged_in =
    isset($_SESSION['user_id']) &&
    isset($_SESSION['username']) &&
    isset($_SESSION['role']);


if ($is_logged_in) {

    header("Location: dashboard.php");
    exit();
}


header("Location: login.php");
exit();

?>
