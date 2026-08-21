<?php

include("../auth.php");

require("../database.php");


$user_id =
    (int) $_SESSION['user_id'];



if (
    $_SESSION['role'] == 'admin'
) {

    header(
        "Location: exercise_view.php"
    );

    exit();
}



if (
    !isset($_GET['id'])
) {

    header(
        "Location: exercise_view.php"
    );

    exit();
}


$exercise_id =
    (int) $_GET['id'];


$status = "";
$status_class = "";



$select_query =
    "SELECT *
     FROM exercise_records

     WHERE
     exercise_id =
     '$exercise_id'

     AND
     user_id =
     '$user_id'";


$result =
    mysqli_query(
        $con,
        $select_query
    );


if (!$result) {

    error_log(
        "Exercise Select Error: "
        . mysqli_error($con)
    );


    echo
        "<h3>
            Unable to retrieve exercise record.
         </h3>

         <p>
            <a href='exercise_view.php'>
                Back to Exercise Records
            </a>
         </p>";


    exit();
}



if (
    mysqli_num_rows($result) == 0
) {

    echo
        "<h3>
            Record not found or access denied.
         </h3>

         <p>
            <a href='exercise_view.php'>
                Back to Exercise Records
            </a>
         </p>";


    exit();
}


$row =
    mysqli_fetch_assoc(
        $result
    );



if (
    isset($_POST['update']) &&
    $_POST['update'] == 1
) {


    $activity_type =
        isset($_POST['activity_type'])
        ? stripslashes(
            $_POST['activity_type']
        )
        : '';


    $activity_type =
        mysqli_real_escape_string(
            $con,
            $activity_type
        );


    $duration =
        isset($_POST['duration'])
        ? (int) $_POST['duration']
        : 0;


    $calories_burned =
        isset(
            $_POST['calories_burned']
        )
        ? (int)
            $_POST['calories_burned']
        : -1;


    $exercise_date =
        isset($_POST['exercise_date'])
        ? stripslashes(
            $_POST['exercise_date']
        )
        : '';


    $exercise_date =
        mysqli_real_escape_string(
            $con,
            $exercise_date
        );


    $valid_activities = [

        'Jogging',
        'Cycling',
        'Gym Session',
        'Swimming',
        'Walking',
        'Other'

    ];


    
    if (
        empty($activity_type) ||
        !in_array(
            $activity_type,
            $valid_activities
        )
    ) {

        $status =
            "Please select a valid activity type.";

        $status_class =
            "error";


    } elseif (
        $duration <= 0
    ) {


        $status =
            "Duration must be more than 0 minutes.";

        $status_class =
            "error";


    } elseif (
        $calories_burned < 0
    ) {


        $status =
            "Calories burned cannot be negative.";

        $status_class =
            "error";


    } elseif (
        empty($exercise_date)
    ) {


        $status =
            "Please select an exercise date.";

        $status_class =
            "error";


    } else {


      
        $stmt = mysqli_prepare(
            $con,
            "UPDATE exercise_records
             SET
                activity_type = ?,
                duration = ?,
                calories_burned = ?,
                exercise_date = ?
             WHERE exercise_id = ?
             AND user_id = ?"
        );


        $update_result = false;


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "siisii",
                $activity_type,
                $duration,
                $calories_burned,
                $exercise_date,
                $exercise_id,
                $user_id
            );

            $update_result =
                mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);
        }


        if ($update_result) {


            $status =
                "Exercise record updated successfully.

                 <br><br>

                 <a href='exercise_view.php'>
                     View Updated Exercise Records
                 </a>";


            $status_class =
                "success";


            $row['activity_type'] =
                $activity_type;

            $row['duration'] =
                $duration;

            $row['calories_burned'] =
                $calories_burned;

            $row['exercise_date'] =
                $exercise_date;


        } else {


            error_log(
                "Exercise Update Error: "
                . mysqli_error($con)
            );


            $status =
                "Unable to update exercise record. "
                . "Please try again.";


            $status_class =
                "error";
        }
    }
}

?>

<?php

$page_title = "Edit Exercise Record";
include("../header.php");

?>





<div class="container">


    <h1>
        Edit Exercise Record
    </h1>


    <form
        name="form"
        method="post"
        action=""
    >


        <input
            type="hidden"
            name="update"
            value="1"
        >


        <p>

            <label>
                Activity Type:
            </label>


            <select
                name="activity_type"
                required
            >


                <option value="">
                    -- Select Activity --
                </option>


                <option
                    value="Jogging"
                    <?php
                    if (
                        $row[
                            'activity_type'
                        ]
                        == 'Jogging'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Jogging
                </option>


                <option
                    value="Cycling"
                    <?php
                    if (
                        $row[
                            'activity_type'
                        ]
                        == 'Cycling'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Cycling
                </option>


                <option
                    value="Gym Session"
                    <?php
                    if (
                        $row[
                            'activity_type'
                        ]
                        == 'Gym Session'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Gym Session
                </option>


                <option
                    value="Swimming"
                    <?php
                    if (
                        $row[
                            'activity_type'
                        ]
                        == 'Swimming'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Swimming
                </option>


                <option
                    value="Walking"
                    <?php
                    if (
                        $row[
                            'activity_type'
                        ]
                        == 'Walking'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Walking
                </option>


                <option
                    value="Other"
                    <?php
                    if (
                        $row[
                            'activity_type'
                        ]
                        == 'Other'
                    ) {
                        echo 'selected';
                    }
                    ?>
                >
                    Other
                </option>


            </select>

        </p>


        <p>

            <label>
                Duration (minutes):
            </label>

            <input
                type="number"
                name="duration"
                min="1"
                required
                value="<?php
                    echo htmlspecialchars(
                        $row['duration']
                    );
                ?>"
            >

        </p>


        <p>

            <label>
                Calories Burned:
            </label>

            <input
                type="number"
                name="calories_burned"
                min="0"
                required
                value="<?php
                    echo htmlspecialchars(
                        $row[
                            'calories_burned'
                        ]
                    );
                ?>"
            >

        </p>


        <p>

            <label>
                Exercise Date:
            </label>

            <input
                type="date"
                name="exercise_date"
                required
                value="<?php
                    echo htmlspecialchars(
                        $row[
                            'exercise_date'
                        ]
                    );
                ?>"
            >

        </p>


        <p>

            <input
                name="submit"
                type="submit"
                value="Update Exercise"
            >

        </p>


    </form>


    <?php

    if (!empty($status)) {

    ?>

        <p class="<?php
            echo $status_class;
        ?>">

            <?php
            echo $status;
            ?>

        </p>

    <?php } ?>


</div>


<?php

include("../footer.php");

?>
