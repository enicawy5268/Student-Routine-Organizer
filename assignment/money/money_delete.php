<?php
include("../auth.php");
require("../database.php");



if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];


if (
    $_SESSION['role'] == 'admin'
) {

    header(
        "Location: money_list.php"
    );

    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($con, "DELETE FROM money_tracker WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['message'] = "Record deleted successfully.";
    } else {
        $_SESSION['message'] = "Record not found or access denied.";
    }
} else {
    $_SESSION['message'] = "Error deleting record: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($con);
header("Location: money_list.php");
exit();
?>

<?php

include("../footer.php");

?>