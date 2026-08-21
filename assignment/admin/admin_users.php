<?php

include("../auth.php");

require("../database.php");



if ($_SESSION['role'] !== 'admin') {

    header(
        "Location: /assignment/dashboard.php?access_denied=1"
    );

    exit();
}



$query =
    "SELECT
        user_id,
        username,
        email,
        role,
        reg_date
     FROM users
     ORDER BY user_id ASC";


$result =
    mysqli_query(
        $con,
        $query
    );


$error_message = "";


if (!$result) {

    error_log(
        "Admin Users Error: "
        . mysqli_error($con)
    );

    $error_message =
        "Unable to retrieve registered users.";
}


$page_title = "Registered Users";

include("../header.php");

?>

<div class="container">

    <h1>
        Registered Users
    </h1>


    <p>
        This page is available only to administrator accounts.
    </p>


    <?php if ($error_message !== "") { ?>

        <p class="error">
            <?php echo htmlspecialchars($error_message); ?>
        </p>

    <?php } ?>


    <div class="table-wrapper">

        <table>

            <thead>

                <tr>

                    <th>No.</th>

                    <th>User ID</th>

                    <th>Username</th>

                    <th>Email</th>

                    <th>Role</th>

                    <th>Registered Date</th>

                </tr>

            </thead>


            <tbody>

            <?php

            $count = 1;


            if (
                $result &&
                mysqli_num_rows($result) > 0
            ) {

                while (
                    $row =
                    mysqli_fetch_assoc($result)
                ) {

            ?>

                    <tr>

                        <td>
                            <?php echo $count; ?>
                        </td>

                        <td>
                            <?php echo (int) $row['user_id']; ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row['username']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row['email']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row['role']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row['reg_date']
                            );
                            ?>
                        </td>

                    </tr>

            <?php

                    $count++;
                }

            } else {

            ?>

                <tr>

                    <td colspan="6">
                        No registered users found.
                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>


<?php

include("../footer.php");

?>