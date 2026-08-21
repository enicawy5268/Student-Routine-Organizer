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

// Create a safety token if not already active
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get habit ID from URL parameter and validate it
$habit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($habit_id <= 0) {
    // Redirect if ID is invalid
    header("Location: view_habit.php");
    exit();
}

// Fetch the existing habit data to display in the form
$stmt = $conn->prepare("SELECT * FROM habits WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $habit_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Verify that the habit exists and belongs to the current user
if ($result->num_rows !== 1) {
    header("Location: view_habit.php");
    exit();
}

// Store habit data 
$habit = $result->fetch_assoc();
$stmt->close();

// Check if current frequency value matches pre-set choices
$pre_defined_frequencies = ['Daily', '3 Times a Week', 'Weekly'];
$is_custom = !in_array($habit['target_frequency'], $pre_defined_frequencies);

// Handle form submission when POST request is made
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate token before execution
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

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
        // Update the habit record in the database
        $stmt = $conn->prepare("UPDATE habits SET habit_name = ?, target_frequency = ?, completion_status = ?, date = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $habit_name, $target_frequency, $completion_status, $date, $habit_id, $user_id);
        
        if ($stmt->execute()) {
            // Set session message and redirect to view page on success
            $_SESSION['delete_success'] = "Habit updated successfully!";
            header("Location: view_habit.php");
            exit();
        } else {
            // Error message for update fails
            $msg = "Error updating habit.";
            $msg_class = "error";
        }
        $stmt->close();
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
    <title>Update Habit - Routine Workspace</title>
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
        <a href="add_habit.php">Add New Habit</a>
        <a href="../logout.php" style="float: right;">Logout</a>
    </div> -->

    <div class="container">
        <h2>Update Habit</h2>
        <p class="small-text">Modify your habit details to keep your routine on track.</p>
        
        <hr>

        <!-- Form Container -->
        <div class="form-box" style="width: 100%; max-width: 600px; margin: 20px auto 0 auto; padding: 25px; border-top: 4px solid #2563eb;">
            
            <!-- Display error messages -->
            <?php if (!empty($msg)): ?>
                <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px; background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                    <span style="font-size: 16px; font-weight: bold;">⚠</span>
                    <span><?php echo htmlspecialchars($msg); ?></span>
                </div>
            <?php endif; ?>

            <!-- Update Habit Form -->
            <form action="update_habit.php?id=<?php echo $habit_id; ?>" method="POST">
                
                <!-- Hidden CSRF input field -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <label for="habit_name">Habit Description:</label>
                <input type="text" id="habit_name" name="habit_name" value="<?php echo htmlspecialchars($habit['habit_name']); ?>" required>

                <label for="target_frequency" style="margin-top: 15px; display:block;">Target Frequency:</label>
                <select id="target_frequency" name="target_frequency" onchange="toggleCustomFrequency();" required>
                    <!-- Pre-select the current frequency value -->
                    <option value="Daily" <?php echo ($habit['target_frequency'] == 'Daily') ? 'selected' : ''; ?>>Daily</option>
                    <option value="3 Times a Week" <?php echo ($habit['target_frequency'] == '3 Times a Week') ? 'selected' : ''; ?>>3 Times a Week</option>
                    <option value="Weekly" <?php echo ($habit['target_frequency'] == 'Weekly') ? 'selected' : ''; ?>>Weekly</option>
                    <option value="Other" <?php echo ($is_custom) ? 'selected' : ''; ?>>Other (Custom...)</option>
                </select>

                <!-- Custom Frequency Text Field -->
                <div id="custom_frequency_wrapper" style="display: <?php echo ($is_custom) ? 'block' : 'none'; ?>; margin-top: 10px;">
                    <label for="custom_frequency" style="font-size: 13px; color: #475569; font-weight: 500;">Type Custom Frequency:</label>
                    <input type="text" id="custom_frequency" name="custom_frequency" value="<?php echo ($is_custom) ? htmlspecialchars($habit['target_frequency']) : ''; ?>" <?php echo ($is_custom) ? 'required' : ''; ?>>
                </div>

                <label for="completion_status" style="margin-top: 15px; display:block;">Status:</label>
                <select id="completion_status" name="completion_status" required>
                    <!-- Pre-select the current status value -->
                    <option value="Not Started" <?php echo ($habit['completion_status'] == 'Not Started') ? 'selected' : ''; ?>>Not Started</option>
                    <option value="In Progress" <?php echo ($habit['completion_status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Completed" <?php echo ($habit['completion_status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                </select>

                <label for="date" style="margin-top: 15px; display:block;">Target Date:</label>
                <!-- Pre-populate date with existing date -->
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($habit['date']))); ?>" required>

                <!-- Form Action Buttons -->
                <div style="display: flex; gap: 15px; margin-top: 25px; width: 100%;">
                    <button type="submit" style="flex: 1; padding: 12px 20px; background-color: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; line-height: 1.2; display: flex; align-items: center; justify-content: center; box-sizing: border-box; -webkit-appearance: none; appearance: none; margin: 0; font-family: inherit;">
                        Update Habit
                    </button>
                    <a href="view_habit.php" style="flex: 1; text-align: center; padding: 12px 20px; background-color: #6b7280; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; line-height: 1.2; display: flex; align-items: center; justify-content: center; box-sizing: border-box;">
                        Cancel
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