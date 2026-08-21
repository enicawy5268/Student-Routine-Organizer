<?php

include("auth.php");

$page_title = "Dashboard";

include("header.php");

?>

<div class="container">

    <?php if (isset($_GET['access_denied'])) { ?>

        <p class="error">
            Access denied. Admin account is required.
        </p>

    <?php } ?>


    <p>

        <h1>Welcome,

        <strong>
            <?php
            echo htmlspecialchars(
                $_SESSION['username']
            );
            ?>
        </strong>

        !
</h1>
    </p>


    <p>

        Your role:

        <strong>
            <?php
            echo htmlspecialchars(
                $_SESSION['role']
            );
            ?>
        </strong>

    </p>


    <?php

    
    if (isset($_COOKIE["user"])) {

    ?>

        <p>

            Remember Me cookie is set for:

            <strong>
                <?php
                echo htmlspecialchars(
                    $_COOKIE["user"]
                );
                ?>
            </strong>

        </p>

    <?php } ?>


    <hr>


    <h2>
        Main Menu
    </h2>


    <div class="card-row">

        <div class="card">

            <h3>
                Exercise Tracker
            </h3>

            <p>
                Record workout activity, duration,
                calories burned and exercise date.
            </p>

            <p>
                <a href="exercise_tracker/exercise_view.php">
                    Open Exercise Tracker
                </a>
            </p>

        </div>


        <div class="card">

            <h3>
                Diary Journal
            </h3>

            <p>
                Record daily reflections,
                mood and personal journal entries.
            </p>

            <p>
                <a href="diary_journal/index.php">
                    Open Diary Journal
                </a>
            </p>

        </div>


        <div class="card">

            <h3>
                Money Tracker
            </h3>

            <p>
                Record income, expenses,
                categories and transaction details.
            </p>

            <p>
                <a href="money/money_list.php">
                    Open Money Tracker
                </a>
            </p>

        </div>


        <div class="card">

            <h3>
                Habit Tracker
            </h3>

            <p>
                Monitor habit completion
                and daily routine progress.
            </p>

            <p>
                <a href="habit_tracker/view_habit.php">
                    Open Habit Tracker
                </a>
            </p>

        </div>

    </div>


    <?php if ($_SESSION['role'] === 'admin') { ?>

        <hr>


        <h2>
            Admin Menu
        </h2>


        <div class="card-row">

            <div class="card">

                <h3>
                    Registered Users
                </h3>

                <p>
                    View all registered student
                    and administrator accounts.
                </p>

                <p>
                    <a href="admin/admin_users.php">
                        View Registered Users
                    </a>
                </p>

            </div>


            <div class="card">

                <h3>
                    System Summary
                </h3>

                <p>
                    View system-level record
                    and module summaries.
                </p>

                <p>
                    <a href="admin/admin_summary.php">
                        View System Summary
                    </a>
                </p>

            </div>

        </div>

    <?php } ?>

</div>


<?php

include("footer.php");

?>