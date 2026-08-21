<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



$timeout_duration = 1800;


if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > $timeout_duration
) {

    $_SESSION = array();

    session_destroy();

    header(
        "Location: /assignment/login.php?timeout=true"
    );

    exit();
}



if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['username']) ||
    !isset($_SESSION['role'])
) {

    header(
        "Location: /assignment/login.php"
    );

    exit();
}



if (
    $_SESSION['role'] !== 'student' &&
    $_SESSION['role'] !== 'admin'
) {

    $_SESSION = array();

    session_destroy();

    header(
        "Location: /assignment/login.php"
    );

    exit();
}


$_SESSION['last_activity'] = time();

?>