<?php
include("../auth.php");
require("../database.php");



$conn = $con;

// Fallback to cookie if Session has expired
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    $_SESSION['user_id'] = intval($_COOKIE['remember_user']);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Store user ID from session for database queries
$user_id = $_SESSION['user_id'];
$msg = "";
$msg_class = "";

// Check for session messages from redirect actions 
if (isset($_SESSION['delete_success'])) {
    $msg = $_SESSION['delete_success'];
    $msg_class = "success";
    unset($_SESSION['delete_success']); // Clear message after displaying
}

// Create a secret safety token to prevent unauthorized form submissions
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle secure delete operation via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    // Validate the token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $delete_id = intval($_POST['delete_id']); // Convert to integer for security
    $stmt = $conn->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    if ($stmt->execute()) {
        // Success message after successful deletion
        $_SESSION['delete_success'] = "Habit deleted successfully!";
        header("Location: view_habit.php");
        exit();
    } else {
        // Error message for deletion fails
        $msg = "Error deleting habit.";
        $msg_class = "error";
    }
    $stmt->close();
}

// Initialize counter variables
$total_habits = $completed_count = $progress_count = $not_started_count = 0;

// Query to count habits grouped by completion status
$metric_stmt = $conn->prepare("SELECT completion_status, COUNT(*) as qty FROM habits WHERE user_id = ? GROUP BY completion_status");
$metric_stmt->bind_param("i", $user_id);
$metric_stmt->execute();
$metric_res = $metric_stmt->get_result();

// Loop through results and populate summary counters
while ($m_row = $metric_res->fetch_assoc()) {
    $total_habits += $m_row['qty'];
    if ($m_row['completion_status'] === 'Completed') $completed_count = $m_row['qty'];
    if ($m_row['completion_status'] === 'In Progress') $progress_count = $m_row['qty'];
    if ($m_row['completion_status'] === 'Not Started') $not_started_count = $m_row['qty'];
}
$metric_stmt->close();

// Get search and filter parameters from URL
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

// Build dynamic SQL query based on search and filter criteria
$query = "SELECT * FROM habits WHERE user_id = ?";
$params = [$user_id];
$types = "i";

// Add search condition if search query is provided
if (!empty($search_query)) {
    $query .= " AND habit_name LIKE ?";
    $params[] = "%" . $search_query . "%";
    $types .= "s";
}

// Add status filter if status filter is provided
if (!empty($filter_status)) {
    $query .= " AND completion_status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

// Order results by date in descending order
$query .= " ORDER BY date DESC";

// Prepare and execute the dynamic query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$habits_result = $stmt->get_result();
?>

<?php

$page_title = "Habit Tracker";
include("../header.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Habits - Routine Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
    
    <!-- Custom CSS Overlay styling for the Custom Confirmation dialog -->
    <style>
        .custom-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 9999;
            align-items: center; justify-content: center;
        }
        .custom-modal {
            background: #ffffff;
            padding: 0;
            border-radius: 8px;
            width: 90%; max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            overflow: hidden;
            font-family: sans-serif;
        }
        .custom-modal-header {
            background: #f8fafc;
            padding: 12px 16px;
            font-weight: bold;
            font-size: 16px;
            color: #1e293b;
            border-bottom: 1px solid #e2e8f0;
        }
        .custom-modal-body {
            padding: 20px 16px;
            font-size: 14px;
            color: #475569;
            line-height: 1.5;
        }
        .custom-modal-footer {
            padding: 12px 16px;
            text-align: right;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex; justify-content: flex-end; gap: 10px;
        }
        .modal-btn {
            padding: 8px 14px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            margin: 0 !important;
            width: auto !important;
        }
        .modal-btn-cancel {
            background: #e2e8f0 !important;
            color: #475569 !important;
            border: 1px solid #cbd5e1 !important;
        }
        .modal-btn-confirm {
            background: #ef4444 !important;
            color: white !important;
            border: 1px solid #dc2626 !important;
        }
    </style>

    <script>
        let formToSubmit = null;

        // Custom intercept instead of basic browser confirm()
       function triggerDeleteConfirmation(event, formElement, habitName) {
            event.preventDefault(); // Stop immediate form submission
            formToSubmit = formElement; // Cache the current form
    
            // Confirmation message
            document.getElementById('modal-habit-text').innerHTML = 'Are you sure you want to permanently delete the habit <strong>"' + habitName + '"</strong>? This action cannot be undone.';
    
            // Make modal overlay visible
            document.getElementById('deleteModalWrapper').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModalWrapper').style.display = 'none';
            formToSubmit = null;
        }

        function proceedWithDeletion() {
            if (formToSubmit) {
                formToSubmit.submit(); // Force send the secure database request post-confirmation
            }
        }
    </script>
</head>
<body>

    <!-- Custom confirmation modal -->
    <div id="deleteModalWrapper" class="custom-modal-overlay">
        <div class="custom-modal">
            <div class="custom-modal-header">Confirmation</div>
            <div class="custom-modal-body" id="modal-habit-text">Are you sure?</div>
            <div class="custom-modal-footer">
                <button onclick="closeDeleteModal()" class="modal-btn modal-btn-cancel" type="button">Cancel</button>
                <button onclick="proceedWithDeletion()" class="modal-btn modal-btn-confirm" type="button">Delete</button>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <style>
        .navbar a {
            text-decoration: none !important;
        }
        
        .navbar a:hover {
            text-decoration: underline !important;
        }
        
        .navbar a:active {
            text-decoration: none !important;
        }
    </style>
    <!-- <div class="navbar">
        <a href="../dashboard.php">Dashboard</a>
        <a href="view_habit.php" style="text-decoration: underline;">View & Manage Habits</a>
        <a href="add_habit.php">Add New Habit</a>
        <a href="../logout.php" style="float: right;">Logout</a>
    </div> -->

    <div class="container">
        <h2>Habit Management Panel</h2>
        <p class="small-text">Review progress summaries, adjust targets, or update status items on your schedule metrics.</p>
        
        <!-- Status Notification Alert Block -->
        <?php if (!empty($msg)): ?>
            <div style="
                margin-top: 15px; 
                padding: 12px 16px; 
                border-radius: 6px; 
                font-size: 14px; 
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 8px;
                <?php echo ($msg_class === 'success') ? 'background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;' : 'background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;'; ?>
            ">
                <span style="font-size: 16px; font-weight: bold;"><?php echo ($msg_class === 'success') ? '✓' : '⚠'; ?></span>
                <span><?php echo htmlspecialchars($msg); ?></span>
            </div>
        <?php endif; ?>

        <!-- Displays summary statistics for all habits -->
        <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px;">
            <div class="card" style="flex: 1; min-width: 140px; text-align: center; border-left: 5px solid #2563eb; padding: 15px;">
                <span style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b;">Total Goals</span>
                <h3 style="margin: 5px 0 0 0; font-size: 24px; color: #1e293b;"><?php echo $total_habits; ?></h3>
            </div>
            <div class="card" style="flex: 1; min-width: 140px; text-align: center; border-left: 5px solid #dc2626; padding: 15px;">
                <span style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b;">Not Started</span>
                <h3 style="margin: 5px 0 0 0; font-size: 24px; color: #dc2626;"><?php echo $not_started_count; ?></h3>
            </div>
            <div class="card" style="flex: 1; min-width: 140px; text-align: center; border-left: 5px solid #ea580c; padding: 15px;">
                <span style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b;">In Progress</span>
                <h3 style="margin: 5px 0 0 0; font-size: 24px; color: #ea580c;"><?php echo $progress_count; ?></h3>
            </div>
            <div class="card" style="flex: 1; min-width: 140px; text-align: center; border-left: 5px solid #16a34a; padding: 15px;">
                <span style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b;">Completed</span>
                <h3 style="margin: 5px 0 0 0; font-size: 24px; color: #16a34a;"><?php echo $completed_count; ?></h3>
            </div>
        </div>

        <!-- Search & Filtering -->
        <div class="card" style="margin-top: 15px;">
            <form action="view_habit.php" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; margin: 0;">
                <div style="flex: 2; min-width: 200px;">
                    <label for="search" style="font-weight: bold; font-size: 14px;">Search Habit Objectives:</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Type keyword to search..." style="margin-top: 5px;">
                </div>

                <div style="flex: 1; min-width: 150px;">
                    <label for="status_filter" style="font-weight: bold; font-size: 14px;">Filter By Status:</label>
                    <select id="status_filter" name="status_filter" style="margin-top: 5px;">
                        <option value="">-- All Statuses --</option>
                        <option value="Not Started" <?php if($filter_status == 'Not Started') echo 'selected'; ?>>Not Started</option>
                        <option value="In Progress" <?php if($filter_status == 'In Progress') echo 'selected'; ?>>In Progress</option>
                        <option value="Completed" <?php if($filter_status == 'Completed') echo 'selected'; ?>>Completed</option>
                    </select>
                </div>

                <!-- Action Buttons Container -->
                <div style="display: flex; gap: 8px; align-items: center; padding-bottom: 2px;">
                    <button type="submit" style="margin: 0; padding: 10px 20px;">Apply</button>
                    <?php if(!empty($search_query) || !empty($filter_status)): ?>
                        <!-- Reset button appears only when search/filter is active -->
                        <a href="view_habit.php" style="
                            display: inline-block;
                            padding: 9px 16px;
                            font-size: 14px;
                            font-weight: 600;
                            text-decoration: none;
                            color: #475569;
                            background-color: #f1f5f9;
                            border: 1px solid #cbd5e1;
                            border-radius: 4px;
                            text-align: center;
                            transition: all 0.2s;
                        " onmouseover="this.style.backgroundColor='#e2e8f0'; this.style.color='#1e293b';" onmouseout="this.style.backgroundColor='#f1f5f9'; this.style.color='#475569';">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Habits Table -->
        <div class="card-row" style="margin-top: 20px;">
            <div class="card" style="flex: 1;">
                <h3>Active Routines Ledger</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Habit Objective</th>
                                <th>Target Frequency</th>
                                <th>Track Status</th>
                                <th>Scheduled Date</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($habits_result->num_rows > 0): ?>
                                <!-- Loop through each habit record and display in table -->
                                <?php while ($row = $habits_result->fetch_assoc()): ?>
                                    <tr>
                                        <td style="text-align: left; font-weight: 500;"><?php echo htmlspecialchars(ucwords($row['habit_name'])); ?></td>
                                        <td><?php echo htmlspecialchars(ucwords($row['target_frequency'])); ?></td>
                                        <td>
                                            <!-- Dynamic status color coding -->
                                            <span style="font-weight: bold; color: <?php 
                                                echo ($row['completion_status'] === 'Completed') ? '#16a34a' : (($row['completion_status'] === 'In Progress') ? '#ea580c' : '#dc2626'); 
                                            ?>;">
                                                <?php echo htmlspecialchars($row['completion_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['date']))); ?></td>
                                        <td style="white-space: nowrap; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                            <a href="update_habit.php?id=<?php echo $row['id']; ?>" style="color: #2563eb; text-decoration: none; font-weight: 600; font-size: 14px; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">Update</a>
                                            <span style="color: #cbd5e1; font-weight: 300;">|</span>
                                            
                                            <!-- Intercepts with dynamic JS custom modal trigger instead of raw direct confirm returns -->
                                            <form action="view_habit.php" method="POST" onsubmit="triggerDeleteConfirmation(event, this, '<?php echo htmlspecialchars($row['habit_name'], ENT_QUOTES); ?>');" style="display: inline-block; margin: 0; padding: 0; width: auto;">
                                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" style="background: none; border: none; color: #ef4444; font-weight: 600; font-size: 14px; cursor: pointer; padding: 0; display: inline; transition: opacity 0.2s; width: auto; margin: 0;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <!-- Empty state when no habits match criteria -->
                                <tr>
                                    <td colspan="5" class="small-text" style="padding: 30px;">
                                        No routine items match your current metrics.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
<?php 
// Close database connection
$stmt->close();
$conn->close();
?>

<?php

include("../footer.php");

?>