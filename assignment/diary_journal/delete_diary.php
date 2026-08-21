<?php
include("../auth.php");
require("../database.php");

// Ensure the user is logged in before allowing them to view this page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

// Retrieve requested entry ID from the URL (default to 0 if not provided)
$entry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Only proceed if valid, positive ID
if ($entry_id > 0) {

    $sql = "DELETE FROM diary_entries WHERE entry_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $entry_id, $user_id);

    if (mysqli_stmt_execute($stmt)) {

        // Clean up resources and redirect to dashboard with success
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        header("Location: index.php?status=deleted");
        exit();

    } else {

        // Clean up resources and redirect to dashboard with error
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        header("Location: index.php?status=error");
        exit();

    }
} else {

    // Invalid ID provided: Redirect to dashboard with error
    mysqli_close($con);
    header("Location: index.php?status=error");
    exit();

}

?>

<?php

include("../footer.php");

?>