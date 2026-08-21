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

// Total Entries
$stmt_stat = mysqli_prepare($con, "SELECT COUNT(*) as total FROM diary_entries WHERE user_id = ?");
mysqli_stmt_bind_param($stmt_stat, "i", $user_id);
mysqli_stmt_execute($stmt_stat);
$total_entries = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_stat))['total'] ?? 0;
mysqli_stmt_close($stmt_stat);

// Top Mood
$top_mood = "None yet";
if ($total_entries > 0) {
    $stmt_stat = mysqli_prepare($con, "SELECT mood_status FROM diary_entries 
                                        WHERE user_id = ? GROUP BY mood_status 
                                        ORDER BY COUNT(*) DESC LIMIT 1");

    mysqli_stmt_bind_param($stmt_stat, "i", $user_id);
    mysqli_stmt_execute($stmt_stat);
    $result_mood = mysqli_stmt_get_result($stmt_stat);
    if ($row = mysqli_fetch_assoc($result_mood)) {
        $top_mood = $row['mood_status'];
    }
    mysqli_stmt_close($stmt_stat);
}

// Entries This Month
$stmt_stat = mysqli_prepare($con, "SELECT COUNT(*) as total_month FROM diary_entries 
                                    WHERE user_id = ? AND MONTH(entry_date) = MONTH(CURRENT_DATE()) 
                                    AND YEAR(entry_date) = YEAR(CURRENT_DATE())");

mysqli_stmt_bind_param($stmt_stat, "i", $user_id);
mysqli_stmt_execute($stmt_stat);
$entries_this_month = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_stat))['total_month'] ?? 0;
mysqli_stmt_close($stmt_stat);

// Define moods for dropdown generation
$moods = ["Happy", "Excited", "Neutral", "Sad", "Tired", "Anxious", "Angry"];

$search = trim($_GET['search'] ?? '');
$mood_filter = $_GET['mood'] ?? '';
$has_mood_filter = in_array($mood_filter, $moods, true);
$start_date = trim($_GET['start_date'] ?? '');
$end_date = trim($_GET['end_date'] ?? '');

$sql = "SELECT entry_id, title, mood_status, entry_date 
        FROM diary_entries
        WHERE user_id = ?";
        
$types = "i";
$params = [$user_id];

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR content LIKE ?)";
    $like = "%" . $search . "%";
    $types .= "ss";
    $params[] = $like;
    $params[] = $like;
}

if ($has_mood_filter) {
    $sql .= " AND mood_status = ?";
    $types .= "s";
    $params[] = $mood_filter;
}

if ($start_date !== '') {
    $sql .= " AND entry_date >= ?";
    $types .= "s";
    $params[] = $start_date;
}

if ($end_date !== '') {
    $sql .= " AND entry_date <= ?";
    $types .= "s";
    $params[] = $end_date;
}

// Sorting entry, default to latest
$sort_by = $_GET['sort'] ?? 'latest';

switch ($sort_by) {
    case 'oldest':
        $sql .= " ORDER BY entry_date ASC, entry_id ASC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY title DESC";
        break;
    case 'latest':
    default:
        $sql .= " ORDER BY entry_date DESC, entry_id DESC";
        break;
}

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Display success or error messages after adding/editing/deleting an entry
$message = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'added') {
        $message = "<p class='success'>Journal entry added successfully.</p>";
    } elseif ($_GET['status'] === 'updated') {
        $message = "<p class='success'>Journal entry updated successfully.</p>";
    } elseif ($_GET['status'] === 'deleted') {
        $message = "<p class='success'>Journal entry deleted successfully.</p>";
    } elseif ($_GET['status'] === 'error') {
        $message = "<p class='error'>Something went wrong. Please try again.</p>";
    }
}

$has_active_filters = ($search !== '' || $has_mood_filter || $start_date !== '' || $end_date !== '');

?>

<?php

$page_title = "Diary Journal";
include("../header.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Diary Journal</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body style="display: flex; flex-direction: column; min-height: 100vh; margin: 0;">

<!-- <div class="navbar">
    <a href="../dashboard.php">Dashboard</a>
    <a href="index.php">Diary Journal</a>
    <a href="add_diary.php">Add New Entry</a>
    <a href="../logout.php" style="float: right;">Logout</a>
</div> -->

<div class="container" style="flex: 1; max-width: 1200px; margin: 0 auto; padding: 20px; box-sizing: border-box;">
    
    <h1>My Diary Journal</h1>

    <?php echo $message; ?>

    <!-- Summary -->
    <div style="display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap;">
        
        <!-- Total Entries -->
        <div style="flex: 1; min-width: 200px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #3b82f6; font-family: sans-serif;"><?php echo $total_entries; ?></div>
            <div style="font-size: 14px; color: #64748b; font-family: sans-serif; margin-top: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Total Entries</div>
        </div>

        <!-- Entries This Month -->
        <div style="flex: 1; min-width: 200px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #8b5cf6; font-family: sans-serif;"><?php echo $entries_this_month; ?></div>
            <div style="font-size: 14px; color: #64748b; font-family: sans-serif; margin-top: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Entries This Month</div>
        </div>

        <!-- Top Mood -->
        <div style="flex: 1; min-width: 200px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #10b981; font-family: sans-serif;"><?php echo htmlspecialchars($top_mood); ?></div>
            <div style="font-size: 14px; color: #64748b; font-family: sans-serif; margin-top: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Top Mood</div>
        </div>

        

    </div>

    <!-- Filter Bar -->
    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px; width: 100%; box-sizing: border-box;">
        <form method="GET" action="index.php" class="filter-bar" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; margin: 0;">
            
           <div class="field" style="flex: 2; min-width: 200px;">
                <label for="search" style="display: block; margin-bottom: 8px; font-weight: bold; font-family: sans-serif; font-size: 14px;">Search</label>
                <input type="text" id="search" name="search" placeholder="Search title or content..."
                       value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div class="field" style="flex: 1; min-width: 130px;">
                <label for="mood" style="display: block; margin-bottom: 8px; font-weight: bold; font-family: sans-serif; font-size: 14px;">Mood</label>
                <select id="mood" name="mood" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                    <option value="">All moods</option>
                    <?php foreach ($moods as $mood): ?>
                        <option value="<?php echo htmlspecialchars($mood); ?>"
                            <?php echo ($mood_filter === $mood) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mood); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field" style="flex: 1; min-width: 130px;">
                <label for="start_date" style="display: block; margin-bottom: 8px; font-weight: bold; font-family: sans-serif; font-size: 14px;">From</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div class="field" style="flex: 1; min-width: 130px;">
                <label for="end_date" style="display: block; margin-bottom: 8px; font-weight: bold; font-family: sans-serif; font-size: 14px;">To</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div class="field" style="flex: 1; min-width: 130px;">
                <label for="sort" style="display: block; margin-bottom: 8px; font-weight: bold; font-family: sans-serif; font-size: 14px; color: #1e293b;">Sort By</label>
                <select id="sort" name="sort" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; font-size: 14px; outline-color: #3b82f6; background-color: white;">
                    <option value="latest" <?php echo ($sort_by === 'latest') ? 'selected' : ''; ?>>Latest</option>
                    <option value="oldest" <?php echo ($sort_by === 'oldest') ? 'selected' : ''; ?>>Oldest</option>
                    <option value="title_asc" <?php echo ($sort_by === 'title_asc') ? 'selected' : ''; ?>>Title (A-Z)</option>
                    <option value="title_desc" <?php echo ($sort_by === 'title_desc') ? 'selected' : ''; ?>>Title (Z-A)</option>
                </select>
            </div>

            <div class="field filter-actions" style="flex: 0 0 auto;">
                <button type="submit" style="padding: 11px 30px; min-width: 80px; background-color: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">Filter</button>
            </div>

            <?php if ($has_active_filters): ?>
                <div class="field filter-actions" style="flex: 0 0 auto;">
                    <a href="index.php" style="display: inline-block; color: #64748b; text-decoration: none; font-size: 14px; padding-bottom: 10px;">Clear filters</a>
                </div>
            <?php endif; ?>
            
        </form>
    </div>

    <!-- Table Section -->
    <?php if (mysqli_num_rows($result) === 0): ?>
        <?php if ($has_active_filters): ?>
            <p>No journal entries match your filters.</p>
        <?php else: ?>
            <p>You have no journal entries yet. <a href="add_diary.php">Add your first entry</a>.</p>
        <?php endif; ?>

    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Title</th>
                    <th>Mood</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['mood_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['entry_date']); ?></td>
                        <td>
                            <a href="view_diary.php?id=<?php echo $row['entry_id']; ?>">View</a> |
                            <a href="edit_diary.php?id=<?php echo $row['entry_id']; ?>">Edit</a> |
                            <a href="delete_diary.php?id=<?php echo $row['entry_id']; ?>"
                               onclick="return confirm('Delete this journal entry? This cannot be undone.');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>

</div>

<?php
mysqli_stmt_close($stmt);
mysqli_close($con);
?>

<?php include("../footer.php"); ?>

</body>
</html>