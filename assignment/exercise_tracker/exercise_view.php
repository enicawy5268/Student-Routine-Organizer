<?php

include("../auth.php");

require("../database.php");


$user_id =
    (int) $_SESSION['user_id'];

$role =
    $_SESSION['role'];



$search =
    $_GET['search'] ?? '';

$date_from =
    $_GET['date_from'] ?? '';

$date_to =
    $_GET['date_to'] ?? '';


$search =
    mysqli_real_escape_string(
        $con,
        $search
    );

$date_from =
    mysqli_real_escape_string(
        $con,
        $date_from
    );

$date_to =
    mysqli_real_escape_string(
        $con,
        $date_to
    );



$sort =
    $_GET['sort']
    ?? 'exercise_date';

$order =
    $_GET['order']
    ?? 'DESC';


$valid_sort_columns = [
    'activity_type',
    'duration',
    'calories_burned',
    'exercise_date',
    'created_at'
];


$valid_order = [
    'ASC',
    'DESC'
];


if (
    !in_array(
        $sort,
        $valid_sort_columns
    )
) {

    $sort =
        'exercise_date';
}


if (
    !in_array(
        $order,
        $valid_order
    )
) {

    $order =
        'DESC';
}



$conditions = [];



if (
    $role != 'admin'
) {

    $conditions[] =
        "exercise_records.user_id
         = '$user_id'";
}



if (
    !empty($search)
) {

    $conditions[] =
        "exercise_records.activity_type
         LIKE '%$search%'";
}


if (
    !empty($date_from)
) {

    $conditions[] =
        "exercise_records.exercise_date
         >= '$date_from'";
}


if (
    !empty($date_to)
) {

    $conditions[] =
        "exercise_records.exercise_date
         <= '$date_to'";
}


$where_clause = "";


if (
    count($conditions) > 0
) {

    $where_clause =
        "WHERE "
        . implode(
            " AND ",
            $conditions
        );
}



if (
    $role == 'admin'
) {

    $query =
        "SELECT
            exercise_records.*,
            users.username
         FROM exercise_records

         JOIN users
         ON exercise_records.user_id
            = users.user_id

         $where_clause

         ORDER BY
         $sort $order";

} else {

    $query =
        "SELECT
            exercise_records.*

         FROM exercise_records

         $where_clause

         ORDER BY
         $sort $order";
}


$result =
    mysqli_query(
        $con,
        $query
    );


if (!$result) {

    error_log(
        "Exercise View Error: "
        . mysqli_error($con)
    );
}



$summary_query =
    "SELECT

        COUNT(*) AS total_records,

        SUM(duration)
        AS total_duration,

        SUM(calories_burned)
        AS total_calories

     FROM exercise_records

     $where_clause";


$summary_result =
    mysqli_query(
        $con,
        $summary_query
    );


if ($summary_result) {

    $summary =
        mysqli_fetch_assoc(
            $summary_result
        );

} else {

    error_log(
        "Exercise Summary Error: "
        . mysqli_error($con)
    );

    $summary = [
        'total_records' => 0,
        'total_duration' => 0,
        'total_calories' => 0
    ];
}

?>

<?php

$page_title = "Exercise Tracker";
include("../header.php");

?>




<div class="container">


    <h1>
        Exercise Tracker
    </h1>
    <a href="exercise_add.php" class="btn btn-primary">Add Exercise</a>


    <?php

    if (
        isset($_GET['delete_success'])
    ) {

        echo "<p class='success'>
                Exercise record deleted successfully.
              </p>";
    }


    if (
        isset($_GET['delete_error'])
    ) {

        echo "<p class='error'>
                Unable to delete exercise record.
                Please try again.
              </p>";
    }

    ?>


    <hr>


    <h2>
        Search / Filter / Sort
    </h2>


    <form
        method="GET"
        action="exercise_view.php"
    >


        <p>

            <label>
                Search Activity Type:
            </label>

            <input
                type="text"
                name="search"
                placeholder="Example: Gym"
                value="<?php
                    echo htmlspecialchars(
                        $search
                    );
                ?>"
            >

        </p>


        <p>

            <label>
                Date From:
            </label>

            <input
                type="date"
                name="date_from"
                value="<?php
                    echo htmlspecialchars(
                        $date_from
                    );
                ?>"
            >

        </p>


        <p>

            <label>
                Date To:
            </label>

            <input
                type="date"
                name="date_to"
                value="<?php
                    echo htmlspecialchars(
                        $date_to
                    );
                ?>"
            >

        </p>


        <p>

            <label>
                Sort By:
            </label>

            <select name="sort">


                <option
                    value="exercise_date"
                    <?php
                    if (
                        $sort
                        == 'exercise_date'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Exercise Date
                </option>


                <option
                    value="activity_type"
                    <?php
                    if (
                        $sort
                        == 'activity_type'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Activity Type
                </option>


                <option
                    value="duration"
                    <?php
                    if (
                        $sort
                        == 'duration'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Duration
                </option>


                <option
                    value="calories_burned"
                    <?php
                    if (
                        $sort
                        == 'calories_burned'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Calories Burned
                </option>


                <option
                    value="created_at"
                    <?php
                    if (
                        $sort
                        == 'created_at'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Created At
                </option>


            </select>

        </p>


        <p>

            <label>
                Order:
            </label>

            <select name="order">

                <option
                    value="DESC"
                    <?php
                    if (
                        $order
                        == 'DESC'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Descending
                </option>


                <option
                    value="ASC"
                    <?php
                    if (
                        $order
                        == 'ASC'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Ascending
                </option>

            </select>

        </p>


        <p>

            <button type="submit">
                Apply
            </button>

            <button
                type="button"
                onclick="
                window.location.href=
                'exercise_view.php'
                "
            >
                Reset
            </button>

        </p>


    </form>


    <hr>


    <h2>
        Exercise Summary
    </h2>


    <table>

        <tr>

            <th>
                Total Records
            </th>

            <th>
                Total Duration
            </th>

            <th>
                Total Calories Burned
            </th>

        </tr>


        <tr>

            <td>

                <?php
                echo
                    $summary[
                        'total_records'
                    ] ?? 0;
                ?>

            </td>


            <td>

                <?php
                echo
                    $summary[
                        'total_duration'
                    ] ?? 0;
                ?>

                minutes

            </td>


            <td>

                <?php
                echo
                    $summary[
                        'total_calories'
                    ] ?? 0;
                ?>

                kcal

            </td>

        </tr>

    </table>


    <hr>


    <h2>
        Exercise Records
    </h2>


    <div class="table-wrapper">


        <table>


            <thead>

            <tr>

                <th>
                    No.
                </th>


                <?php
                if (
                    $role == 'admin'
                ) {
                ?>

                    <th>
                        Username
                    </th>

                <?php } ?>


                <th>
                    Activity Type
                </th>

                <th>
                    Duration
                </th>

                <th>
                    Calories Burned
                </th>

                <th>
                    Exercise Date
                </th>

                <th>
                    Created At
                </th>


                <?php
                if (
                    $role != 'admin'
                ) {
                ?>

                    <th>
                        Edit
                    </th>

                    <th>
                        Delete
                    </th>

                <?php } ?>


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
                    mysqli_fetch_assoc(
                        $result
                    )
                ) {

            ?>


                    <tr>


                        <td>
                            <?php
                            echo $count;
                            ?>
                        </td>


                        <?php
                        if (
                            $role == 'admin'
                        ) {
                        ?>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row['username']
                                );
                                ?>

                            </td>

                        <?php } ?>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row[
                                    'activity_type'
                                ]
                            );
                            ?>

                        </td>


                        <td>

                            <?php
                            echo
                                $row['duration'];
                            ?>

                            minutes

                        </td>


                        <td>

                            <?php
                            echo
                                $row[
                                    'calories_burned'
                                ];
                            ?>

                            kcal

                        </td>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row[
                                    'exercise_date'
                                ]
                            );
                            ?>

                        </td>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row[
                                    'created_at'
                                ]
                            );
                            ?>

                        </td>


                        <?php

                        if (
                            $role != 'admin'
                        ) {

                        ?>


                            <td>

                                <a href="exercise_edit.php?id=<?php
                                    echo (int) $row['exercise_id'];
                                ?>">
                                    Edit
                                </a>

                            </td>


                            <td>

                                <form
                                    class="inline-form"
                                    method="post"
                                    action="exercise_delete.php"
                                    onsubmit="return confirm(
                                    'Are you sure you want to delete this exercise record?'
                                    )"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php
                                        echo (int) $row['exercise_id'];
                                        ?>"
                                    >

                                    <button
                                        class="delete-button"
                                        type="submit"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </td>


                        <?php } ?>


                    </tr>


            <?php

                    $count++;
                }


            } else {

            ?>


                <tr>

                    <td
                        colspan="<?php
                        echo
                            ($role == 'admin')
                            ? '7'
                            : '8';
                        ?>"
                    >

                        No exercise records found.

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
