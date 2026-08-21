<?php

include("../auth.php");
require("../database.php");

$user_id = (int) $_SESSION['user_id'];


if ($_SESSION['role'] == 'admin') {

    header("Location: exercise_view.php");
    exit();
}


if (
    $_SERVER['REQUEST_METHOD'] != 'POST' ||
    !isset($_POST['id'])
) {

    header("Location: exercise_view.php");
    exit();
}

$exercise_id = (int) $_POST['id'];


$stmt = mysqli_prepare(
    $con,
    "DELETE FROM exercise_records
     WHERE exercise_id = ?
     AND user_id = ?"
);

$result = false;

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $exercise_id,
        $user_id
    );

    $result = mysqli_stmt_execute($stmt);

    if (
        $result &&
        mysqli_stmt_affected_rows($stmt) == 0
    ) {
        $result = false;
    }

    mysqli_stmt_close($stmt);
}

if ($result) {

    header("Location: exercise_view.php?delete_success=1");

} else {

    error_log(
        "Exercise Delete Error: "
        . mysqli_error($con)
    );

    header("Location: exercise_view.php?delete_error=1");
}

exit();

?>
