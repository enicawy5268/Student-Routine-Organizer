<?php

$base_url = "/assignment";

$is_logged_in =
    isset($_SESSION['user_id']) &&
    isset($_SESSION['username']) &&
    isset($_SESSION['role']);

$is_admin =
    $is_logged_in &&
    $_SESSION['role'] === 'admin';

$is_student =
    $is_logged_in &&
    $_SESSION['role'] === 'student';

if (!isset($page_title)) {
    $page_title = "Student Routine Organizer";
}

$current_script = $_SERVER['PHP_SELF'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/assets/style.css">
</head>

<body>

<header>

    <h1 class="site-title">
        Student Routine Organizer
    </h1>

    <nav class="navbar" style="display: flex; align-items: center; justify-content: space-between;">

        <?php if ($is_logged_in) { ?>

            <div class="nav-left" style="display: flex; gap: 15px; align-items: center;">
                <a href="<?php echo $base_url; ?>/dashboard.php">
                    Dashboard
                </a>

                <?php if (strpos($current_script, 'diary_journal') !== false || strpos($current_script, 'diary') !== false) { ?>
                    <a href="<?php echo $base_url; ?>/diary_journal/view_diary.php">Diary Journal</a>
                    <?php if (!$is_admin) { ?>
                        <a href="<?php echo $base_url; ?>/diary_journal/add_diary.php">Add New Entry</a>
                    <?php } ?>
                <?php } ?>

                <?php if (strpos($current_script, 'habit_tracker') !== false || strpos($current_script, 'habit') !== false) { ?>
                    <a href="<?php echo $base_url; ?>/habit_tracker/view_habit.php">View & Manage Habits</a>
                    <?php if (!$is_admin) { ?>
                        <a href="<?php echo $base_url; ?>/habit_tracker/add_habit.php">Add New Habit</a>
                    <?php } ?>
                <?php } ?>



                <?php if ($is_admin) { ?>
                    <a href="<?php echo $base_url; ?>/admin/admin_users.php">
                        Registered Users
                    </a>
                    <a href="<?php echo $base_url; ?>/admin/admin_summary.php">
                        System Summary
                    </a>
                <?php } ?>
            </div>

            <div class="nav-right" style="margin-left: auto; display: flex; align-items: center; gap: 15px;">
                <span class="nav-user" style="color: #cbd5e1;">
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                    (
                    <?php echo htmlspecialchars($_SESSION['role']); ?>
                    )
                </span>

                <a class="logout-link" href="<?php echo $base_url; ?>/logout.php" >
                    Logout
                </a>
            </div>

        <?php } else { ?>

            <div class="nav-right" style="margin-left: auto; display: flex; gap: 15px;">
                <a href="<?php echo $base_url; ?>/login.php">
                    Login
                </a>
                <a href="<?php echo $base_url; ?>/registration.php">
                    Register
                </a>
            </div>

        <?php } ?>

    </nav>

</header>