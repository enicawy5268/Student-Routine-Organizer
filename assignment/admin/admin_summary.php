<?php

include("../auth.php");

require("../database.php");


if ($_SESSION['role'] !== 'admin') {

    header(
        "Location: /assignment/dashboard.php?access_denied=1"
    );

    exit();
}



function get_summary_count(
    $con,
    $query,
    $column_name
) {

    $result =
        mysqli_query(
            $con,
            $query
        );


    if (!$result) {

        error_log(
            "Admin Summary Error: "
            . mysqli_error($con)
        );

        return 0;
    }


    $row =
        mysqli_fetch_assoc($result);


    return (int) (
        $row[$column_name] ?? 0
    );
}



$total_users =
    get_summary_count(
        $con,
        "SELECT COUNT(*) AS total FROM users",
        "total"
    );


$total_students =
    get_summary_count(
        $con,
        "SELECT COUNT(*) AS total
         FROM users
         WHERE role = 'student'",
        "total"
    );


$total_admins =
    get_summary_count(
        $con,
        "SELECT COUNT(*) AS total
         FROM users
         WHERE role = 'admin'",
        "total"
    );


$total_exercise_records =
    get_summary_count(
        $con,
        "SELECT COUNT(*) AS total
         FROM exercise_records",
        "total"
    );


$total_diary_records =
    get_summary_count(
        $con,
        "SELECT COUNT(*) AS total
         FROM diary_entries",
        "total"
    );


$total_money_records =
    get_summary_count(
        $con,
        "SELECT COUNT(*) AS total
         FROM money_tracker",
        "total"
    );


$total_habit_records =
    get_summary_count(
        $con,
        "SELECT COUNT(*) AS total
         FROM habits",
        "total"
    );



$exercise_summary = [

    'total_duration' => 0,

    'total_calories' => 0

];


$exercise_summary_query =
    "SELECT
        COALESCE(
            SUM(duration),
            0
        ) AS total_duration,

        COALESCE(
            SUM(calories_burned),
            0
        ) AS total_calories

     FROM exercise_records";


$exercise_summary_result =
    mysqli_query(
        $con,
        $exercise_summary_query
    );


if ($exercise_summary_result) {

    $exercise_summary =
        mysqli_fetch_assoc(
            $exercise_summary_result
        );

} else {

    error_log(
        "Exercise Summary Error: "
        . mysqli_error($con)
    );
}


$exercise_by_student_query =
    "SELECT

        users.user_id,

        users.username,

        COUNT(
            exercise_records.exercise_id
        ) AS record_count,

        COALESCE(
            SUM(exercise_records.duration),
            0
        ) AS total_duration,

        COALESCE(
            SUM(exercise_records.calories_burned),
            0
        ) AS total_calories

     FROM users

     LEFT JOIN exercise_records

     ON users.user_id =
        exercise_records.user_id

     WHERE users.role = 'student'

     GROUP BY
        users.user_id,
        users.username

     ORDER BY
        users.username ASC";


$exercise_by_student_result =
    mysqli_query(
        $con,
        $exercise_by_student_query
    );


if (!$exercise_by_student_result) {

    error_log(
        "Exercise By Student Error: "
        . mysqli_error($con)
    );
}


$page_title = "System Summary";

include("../header.php");

?>

<div class="container">

    <h1>
        System Summary
    </h1>


    <p>
        Overview of registered users
        and module records.
    </p>


    <hr>


    <h2>
        User Summary
    </h2>


    <div class="card-row">

        <div class="card">

            <h3>
                Total Users
            </h3>

            <p>
                <?php echo $total_users; ?>
            </p>

        </div>


        <div class="card">

            <h3>
                Students
            </h3>

            <p>
                <?php echo $total_students; ?>
            </p>

        </div>


        <div class="card">

            <h3>
                Administrators
            </h3>

            <p>
                <?php echo $total_admins; ?>
            </p>

        </div>

    </div>


    <hr>


    <h2>
        Module Summary
    </h2>


    <div class="card-row">

        <div class="card">

            <h3>
                Exercise Records
            </h3>

            <p>
                <?php echo $total_exercise_records; ?>
            </p>

        </div>


        <div class="card">

            <h3>
                Diary Entries
            </h3>

            <p>
                <?php echo $total_diary_records; ?>
            </p>

        </div>


        <div class="card">

            <h3>
                Money Records
            </h3>

            <p>
                <?php echo $total_money_records; ?>
            </p>

        </div>


        <div class="card">

            <h3>
                Habit Records
            </h3>
            <p>
                <?php echo $total_habit_records; ?>
            </p>

        </div>

    </div>


    <hr>


    <h2>
        Overall Exercise Summary
    </h2>


    <div class="card-row">

        <div class="card">

            <h3>
                Total Duration
            </h3>

            <p>

                <?php
                echo (int)
                    $exercise_summary[
                        'total_duration'
                    ];
                ?>

                minutes

            </p>

        </div>


        <div class="card">

            <h3>
                Total Calories Burned
            </h3>

            <p>

                <?php
                echo (int)
                    $exercise_summary[
                        'total_calories'
                    ];
                ?>

                calories

            </p>

        </div>

    </div>


    <hr>


    <h2>
        Exercise Summary by Student
    </h2>


    <div class="table-wrapper">

        <table>

            <thead>

                <tr>

                    <th>No.</th>

                    <th>User ID</th>

                    <th>Username</th>

                    <th>Exercise Records</th>

                    <th>Total Duration</th>

                    <th>Total Calories</th>

                </tr>

            </thead>


            <tbody>

            <?php

            $count = 1;


            if (
                $exercise_by_student_result &&
                mysqli_num_rows(
                    $exercise_by_student_result
                ) > 0
            ) {

                while (
                    $row =
                    mysqli_fetch_assoc(
                        $exercise_by_student_result
                    )
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
                            echo (int)
                                $row['record_count'];
                            ?>
                        </td>

                        <td>

                            <?php
                            echo (int)
                                $row['total_duration'];
                            ?>

                            minutes

                        </td>

                        <td>

                            <?php
                            echo (int)
                                $row['total_calories'];
                            ?>

                            calories

                        </td>

                    </tr>

            <?php

                    $count++;
                }

            } else {

            ?>

                <tr>

                    <td colspan="6">
                        No student exercise summary found.
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