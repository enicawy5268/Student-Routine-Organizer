<?php


mysqli_report(MYSQLI_REPORT_OFF);



$database_host = "localhost";

$database_username = "root";

$database_password = "";

$database_name = "assignment";



$con = mysqli_connect(
    $database_host,
    $database_username,
    $database_password,
    $database_name
);



if (!$con) {

    error_log(
        "Database connection failed: "
        . mysqli_connect_error()
    );

    die(
        "Unable to connect to the database. "
        . "Please try again later."
    );
}



if (
    !mysqli_set_charset(
        $con,
        "utf8mb4"
    )
) {

    error_log(
        "Unable to set database character set: "
        . mysqli_error($con)
    );
}


$conn = $con;

?>