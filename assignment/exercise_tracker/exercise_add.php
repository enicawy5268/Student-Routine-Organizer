<?php

include("../auth.php");

require("../database.php");

if (
    $_SESSION['role'] == 'admin'
) {

    header(
        "Location: exercise_view.php"
    );

    exit();
}


$status = "";
$status_class = "";


$activity_type = "";
$duration = "";
$calories_burned = "";
$exercise_date = "";


if (
    isset($_POST['new']) &&
    $_POST['new'] == 1
) {

    $user_id =
        (int) $_SESSION['user_id'];


   
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

        $status_class = "error";


    } elseif ($duration <= 0) {


        $status =
            "Duration must be more than 0 minutes.";

        $status_class = "error";


    } elseif (
        $calories_burned < 0
    ) {


        $status =
            "Calories burned cannot be negative.";

        $status_class = "error";


    } elseif (
        empty($exercise_date)
    ) {


        $status =
            "Please select an exercise date.";

        $status_class = "error";


    } else {


        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO exercise_records
            (
                user_id,
                activity_type,
                duration,
                calories_burned,
                exercise_date
            )
            VALUES (?, ?, ?, ?, ?)"
        );


        $result = false;


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "isiis",
                $user_id,
                $activity_type,
                $duration,
                $calories_burned,
                $exercise_date
            );

            $result = mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);
        }


        if ($result) {


            $status =
                "Exercise record added successfully.
                 <br><br>
                 <a href='exercise_view.php'>
                    View Exercise Records
                 </a>";


            $status_class =
                "success";


            
            $activity_type = "";
            $duration = "";
            $calories_burned = "";
            $exercise_date = "";


        } else {


            error_log(
                "Exercise Add Error: "
                . mysqli_error($con)
            );


            $status =
                "Unable to add exercise record. "
                . "Please try again.";


            $status_class =
                "error";
        }
    }
}

?>

<?php

$page_title = "Add Exercise Record";
include("../header.php");

?>




<div class="container">


    <h1>
        Add Exercise Record
    </h1>


    <form
        name="form"
        method="post"
        action=""
    >


        <input
            type="hidden"
            name="new"
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
                        $activity_type
                        == "Jogging"
                    ) {
                        echo "selected";
                    }
                    ?>
                >
                    Jogging
                </option>


                <option
                    value="Cycling"
                    <?php
                    if (
                        $activity_type
                        == "Cycling"
                    ) {
                        echo "selected";
                    }
                    ?>
                >
                    Cycling
                </option>


                <option
                    value="Gym Session"
                    <?php
                    if (
                        $activity_type
                        == "Gym Session"
                    ) {
                        echo "selected";
                    }
                    ?>
                >
                    Gym Session
                </option>


                <option
                    value="Swimming"
                    <?php
                    if (
                        $activity_type
                        == "Swimming"
                    ) {
                        echo "selected";
                    }
                    ?>
                >
                    Swimming
                </option>


                <option
                    value="Walking"
                    <?php
                    if (
                        $activity_type
                        == "Walking"
                    ) {
                        echo "selected";
                    }
                    ?>
                >
                    Walking
                </option>


                <option
                    value="Other"
                    <?php
                    if (
                        $activity_type
                        == "Other"
                    ) {
                        echo "selected";
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
                placeholder="Example: 60"
                value="<?php
                    echo htmlspecialchars(
                        $duration
                    );
                ?>"
                required
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
                placeholder="Example: 350"
                value="<?php
                    echo htmlspecialchars(
                        $calories_burned
                    );
                ?>"
                required
            >

        </p>


        <p>

            <label>
                Exercise Date:
            </label>

            <input
                type="date"
                name="exercise_date"
                value="<?php
                    echo htmlspecialchars(
                        $exercise_date
                    );
                ?>"
                required
            >

        </p>


        <p>

            <input
                name="submit"
                type="submit"
                value="Add Exercise"
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
