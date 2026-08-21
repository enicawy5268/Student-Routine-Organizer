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

$sql = "SELECT entry_id, title, content, mood_status, entry_date, created_at, updated_at 
        FROM diary_entries
        WHERE entry_id = ? AND user_id = ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "ii", $entry_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$entry = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);
mysqli_close($con);

// If entry doesn't exist, redirect back
if (!$entry) {
    header("Location: index.php?status=error");
    exit();
}

// Check for success status in URL
$message = "";
if (isset($_GET['status']) && $_GET['status'] === 'updated') {
    $message = "<p class='success' style='margin-bottom: 20px;'>Journal entry updated successfully.</p>";
}

?>

<?php

$page_title = "Diary Journal";
include("../header.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Journal Entry</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body style="display: flex; flex-direction: column; min-height: 100vh; margin: 0;">

<!-- <div class="navbar">
    <a href="../dashboard.php">Dashboard</a>
    <a href="index.php">Diary Journal</a>
    <a href="add_diary.php">Add New Entry</a>
    <a href="../logout.php" style="float: right;">Logout</a>
</div> -->

<div class="container" style="flex: 1;">

    <?php echo $message; ?>

    <h1><?php echo htmlspecialchars($entry['title']); ?></h1>

    <!-- Information Cards (Mood, Date, Last Updated) -->
    <div class="card-row">
        <div class="card">
            <p class="small-text">Mood</p>
            <p><?php echo htmlspecialchars($entry['mood_status']); ?></p>
        </div>
        <div class="card">
            <p class="small-text">Date</p>
            <p><?php echo htmlspecialchars($entry['entry_date']); ?></p>
        </div>
        <div class="card">
            <p class="small-text">Last Updated</p>
            <p><?php echo htmlspecialchars($entry['updated_at']); ?></p>
        </div>
    </div>

    <hr>
    <p style="white-space: pre-line; overflow-wrap: break-word; word-wrap: break-word; margin: 0;"><?php echo htmlspecialchars($entry['content']); ?></p>
    <hr>

    <!-- Button Row -->
    <div style="display: flex; gap: 15px; margin-top: 20px;">

        <!-- Edit Button -->
        <a href="edit_diary.php?id=<?php echo $entry['entry_id']; ?>" 
           style="padding: 8px 16px; background-color: #2564ebdd; color: white; text-decoration: none; border-radius: 5px; text-align: center;">
           Edit
        </a> 
        
        <!-- Delete Button -->
        <a href="delete_diary.php?id=<?php echo $entry['entry_id']; ?>"
           onclick="return confirm('Delete this journal entry? This cannot be undone.');" 
           style="padding: 8px 16px; background-color: #dc2626df; color: white; text-decoration: none; border-radius: 5px; text-align: center;">
           Delete
        </a> 
        
        <!-- Back to List Button -->
        <a href="index.php" 
           style="padding: 8px 16px; background-color: #6b7280; color: white; text-decoration: none; border-radius: 5px; text-align: center;">
           Back to list
        </a>
    </div>
</div>

</body>
</html>

<?php

include("../footer.php");

?>