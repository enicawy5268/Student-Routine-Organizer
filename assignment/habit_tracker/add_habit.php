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

// Create a safety token if not already active to prevent CSRF exploits
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission when POST request is made
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate token immediately upon form submission
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Trim whitespace from habit name input
    $habit_name = trim($_POST['habit_name']);
    $habit_name = ucwords($habit_name);
    
    // Check if user selected 'Other' frequency and entered custom text
    if ($_POST['target_frequency'] === 'Other' && !empty($_POST['custom_frequency'])) {
        $target_frequency = trim($_POST['custom_frequency']);
    } else {
        $target_frequency = trim($_POST['target_frequency']);
    }
    
    $completion_status = trim($_POST['completion_status']);
    $date = $_POST['date'];

    // Validate that all required fields are filled
    if (empty($habit_name) || empty($target_frequency) || empty($completion_status) || empty($date)) {
        $msg = "All fields are required.";
        $msg_class = "error";
    } else {
        
        // Check if this exact habit name already exists for this user on this specific day
        $check_stmt = $conn->prepare("SELECT id FROM habits WHERE user_id = ? AND LOWER(habit_name) = LOWER(?) AND DATE(date) = ?");
        $check_stmt->bind_param("iss", $user_id, $habit_name, $date);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // Reject insertion if has same habit in same day
            $msg = "You are already tracking a '" . htmlspecialchars($habit_name) . "' routine entry for this specific day.";
            $msg_class = "error";
        } else {
            // No same day duplicates found
            $stmt = $conn->prepare("INSERT INTO habits (user_id, habit_name, target_frequency, completion_status, date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $habit_name, $target_frequency, $completion_status, $date);
            
            if ($stmt->execute()) {
                // Success message after successful insertion
                $msg = "Premium habit track created successfully!";
                $msg_class = "success";
            } else {
                // Error message for database insertion fails
                $msg = "Error recording routine.";
                $msg_class = "error";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
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
    <title>Add Habit - Routine Workspace</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script>
        // Toggle the visibility of the custom text input field
        function toggleCustomFrequency() {
            var selectBox = document.getElementById("target_frequency");
            var customFieldWrapper = document.getElementById("custom_frequency_wrapper");
            var customInput = document.getElementById("custom_frequency");
            
            // Show custom input if 'Other' is selected, hide otherwise
            if (selectBox.value === "Other") {
                customFieldWrapper.style.display = "block";
                customInput.required = true;
            } else {
                customFieldWrapper.style.display = "none";
                customInput.required = false;
                customInput.value = ""; // Clear input when toggling away
            }
        }
    </script>
</head>
<body>

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
        <a href="view_habit.php">View & Manage Habits</a>
        <a href="add_habit.php" style="text-decoration: underline;">Add New Habit</a>
        <a href="../logout.php" style="float: right;">Logout</a>  
    </div> -->

    <div class="container">
        <h2>Create a New Routine</h2>
        <p class="small-text">Define and build habits to support healthy, organized student development schedules.</p>
        
        <hr>

        <!-- Form Container -->
        <div class="form-box" style="width: 100%; max-width: 600px; margin: 20px auto 0 auto; padding: 25px; border-top: 4px solid #2563eb;">
            
            <!-- Display success or error messages -->
            <?php if (!empty($msg)): ?>
                <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px; <?php echo ($msg_class === 'success') ? 'background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;' : 'background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;'; ?> ">
                    <span style="font-size: 16px; font-weight: bold;"><?php echo ($msg_class === 'success') ? '✓' : '⚠'; ?></span>
                    <span><?php echo htmlspecialchars($msg); ?></span>
                </div>
            <?php endif; ?>

            <!-- Habit Creation Form -->
            <form action="add_habit.php" method="POST">
                
                <!-- Add hidden CSRF security token field to form -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <label for="habit_name">Habit Description:</label>
                <input type="text" id="habit_name" name="habit_name" placeholder="e.g., LeetCode 30 mins, Gym Workout" required>

                <label for="target_frequency" style="margin-top: 15px; display:block;">Target Frequency:</label>
                <select id="target_frequency" name="target_frequency" onchange="toggleCustomFrequency();" required>
                    <option value="">-- Choose target metric --</option>
                    <option value="Daily">Daily</option>
                    <option value="3 Times a Week">3 Times a Week</option>
                    <option value="Weekly">Weekly</option>
                    <option value="Other">Other (Custom...)</option>
                </select>

                <!-- Custom Frequency Text Field (Hidden by default) -->
                <div id="custom_frequency_wrapper" style="display: none; margin-top: 10px;">
                    <label for="custom_frequency" style="font-size: 13px; color: #475569; font-weight: 500;">Type Custom Frequency:</label>
                    <input type="text" id="custom_frequency" name="custom_frequency" placeholder="e.g., Every Tuesday, 2 Times a Month">
                </div>

                <label for="completion_status" style="margin-top: 15px; display:block;">Starting Status:</label>
                <select id="completion_status" name="completion_status" required>
                    <option value="Not Started">Not Started</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>

                <label for="date" style="margin-top: 15px; display:block;">Target Date:</label>
                <!-- Today's date as default -->
                <input type="date" id="date" value="<?php echo date('Y-m-d'); ?>" name="date" required>

                <!-- Form Action Buttons -->
                <div style="display: flex; gap: 15px; margin-top: 25px; width: 100%;">
                    <button type="submit" style="flex: 1; padding: 12px 20px; background-color: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 15px;">
                        Launch Routine Track
                    </button>
                    <a href="view_habit.php" style="flex: 1; text-align: center; padding: 12px 20px; background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 15px; box-sizing: border-box; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#e2e8f0'" onmouseout="this.style.backgroundColor='#f1f5f9'">
                        View Habits Ledger
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); // Close database connection ?>

<?php

include("../footer.php");

?>