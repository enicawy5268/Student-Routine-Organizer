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

// Define moods for dropdown generation
$moods = ["Happy", "Excited", "Neutral", "Sad", "Tired", "Anxious", "Angry"];

$errors = [];
$entry_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['entry_id'] ?? 0);

$sql = "SELECT * FROM diary_entries WHERE entry_id = ? AND user_id = ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "ii", $entry_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$entry = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// If entry doesn't exist, redirect back
if (!$entry) {
    mysqli_close($con);
    header("Location: index.php?status=error");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve and trim form data to remove whitespace
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $mood_status = $_POST['mood_status'] ?? 'Neutral';
    $entry_date = $_POST['entry_date'] ?? '';

    // Validation
    if ($title === '') {
        $errors[] = "Title is required.";
    }
    if ($content === '') {
        $errors[] = "Content is required.";
    }
    if ($entry_date === '') {
        $errors[] = "Date is required.";
    }
    if (!in_array($mood_status, $moods, true)) {
        $errors[] = "Invalid mood selected.";
    }

    // Save entry if no error
    if (empty($errors)) {

        $update_sql = "UPDATE diary_entries
                        SET title = ?, content = ?, mood_status = ?, entry_date = ? 
                        WHERE entry_id = ? AND user_id = ?";

        $update_stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ssssii", $title, $content, $mood_status, $entry_date, $entry_id, $user_id);

        if (mysqli_stmt_execute($update_stmt)) {

            // Redirect to view page with success status
            mysqli_stmt_close($update_stmt);
            mysqli_close($con);
            header("Location: view_diary.php?id=" . $entry_id . "&status=updated");
            exit();

        } else {
            // Record database error
            $errors[] = "Database error: " . mysqli_error($con);
        }

        mysqli_stmt_close($update_stmt);
    }

    // Keep the entry array in sync with submitted values
    $entry['title'] = $title;
    $entry['content'] = $content;
    $entry['mood_status'] = $mood_status;
    $entry['entry_date'] = $entry_date;
}

mysqli_close($con);
?>

<?php

$page_title = "Diary Journal";
include("../header.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Journal Entry</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<!-- <div class="navbar">
    <a href="../dashboard.php">Dashboard</a>
    <a href="index.php">Diary Journal</a>
    <a href="add_diary.php">Add New Entry</a>
    <a href="../logout.php" style="float: right;">Logout</a>
</div> -->

<div class="container">
    <h1>Edit Journal Entry</h1>

    <!-- Show error message -->
    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?php echo htmlspecialchars($err); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Form Section -->
    <form action="edit_diary.php?id=<?php echo $entry['entry_id']; ?>" method="POST" id="entryForm">
        <input type="hidden" name="entry_id" value="<?php echo $entry['entry_id']; ?>">
        
        <div class="entry-layout">
            <div class="entry-main">
                <div class="field" style="margin-bottom: 20px;">
                    <label for="title" style="display: block; margin-bottom: 10px;">Title</label>
                    <input type="text" id="title" name="title" maxlength="255"
                           value="<?php echo htmlspecialchars($entry['title']); ?>" required>
                </div>

                <div class="field" style="margin-bottom: 20px;">
                    <label for="content" style="display: block; margin-bottom: 10px;">Content</label>
                    <textarea id="content" name="content" style="width: 100%; box-sizing: border-box; min-height: 150px; resize: vertical;" required><?php
                        echo htmlspecialchars($entry['content']);
                    ?></textarea>
                    <p class="small-text char-count" id="charCount">0 characters</p>
                </div>
            </div>

            <aside class="entry-sidebar">
                <div class="card">
                    <div class="field" style="margin-bottom: 20px;">
                        <label for="mood_status" style="display: block; margin-bottom: 10px;">Mood</label>
                        <select id="mood_status" name="mood_status" required>
                            <?php foreach ($moods as $mood): ?>
                                <option value="<?php echo htmlspecialchars($mood); ?>"
                                    <?php echo ($entry['mood_status'] === $mood) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mood); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field" style="margin-bottom: 20px;">
                        <label for="entry_date" style="display: block; margin-bottom: 10px;">Date</label>
                        <input type="date" id="entry_date" name="entry_date"
                               value="<?php echo htmlspecialchars($entry['entry_date']); ?>" required>
                    </div>

                    <button type="submit" style="margin-top: 20px;">Update Entry</button>
                </div>
            </aside>
        </div>
    </form>

    <a href="index.php" 
        style="display: inline-block; margin-top: 20px; padding: 8px 16px; background-color: #6b7280; color: white; text-decoration: none; border-radius: 5px; text-align: center;">
        Back to list
    </a>

</div>

<script>
    const contentField = document.getElementById('content');
    const charCount = document.getElementById('charCount');

    // Update characters count when user types
    function updateCharCount() {
        charCount.textContent = contentField.value.length + ' characters';
    }

    contentField.addEventListener('input', updateCharCount);
    updateCharCount();
</script>

</body>
</html>

<?php

include("../footer.php");

?>