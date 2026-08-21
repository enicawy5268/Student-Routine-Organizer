<?php
include("../auth.php");
require("../database.php");



if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$error = "";

if (
    $_SESSION['role'] == 'admin'
) {

    header(
        "Location: money_list.php"
    );

    exit();
}


if (isset($_POST['submit'])) {

    $amount      = $_POST['amount']      ?? '';
    $category    = $_POST['category']    ?? '';
    $description = $_POST['description'] ?? '';
    $type        = $_POST['type']        ?? '';
    $date        = $_POST['date']        ?? '';

    if ($amount === '' || $category === '' || $type === '' || $date === '') {
        $error = "Please fill in all required fields.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Amount must be a positive number.";
    } elseif ($type !== 'income' && $type !== 'expense') {
        $error = "Please select a valid transaction type.";
    } else {
        $sql  = "INSERT INTO money_tracker (user_id, amount, category, description, type, date)
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "idssss",
                $user_id, $amount, $category, $description, $type, $date);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['message'] = "Record added successfully.";
                header("Location: money_list.php");
                exit();
            } else {
                $error = "Error adding record: " . mysqli_stmt_error($stmt);
            }
        } else {
            $error = "Error preparing statement: " . mysqli_error($con);
        }
    }
}

$categories = ['Food', 'Transport', 'Entertainment', 'Education',
               'Health', 'Salary', 'Allowance', 'Others'];
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Record - Money Tracker</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="navbar">
    <a href="../dashboard.php">Dashboard</a>
    <a href="money_list.php">Money Tracker</a>
    <a href="logout.php" style="float: right;">Logout</a>
</div>

<div class="container">

    <h1>Add New Record</h1>

    <?php if ($error !== ''): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="money_add.php">

        <label>Transaction Type: *</label>
        <div class="checkbox-row">
            <input type="radio" id="income" name="type" value="income"
                <?php echo (($_POST['type'] ?? '') === 'income') ? 'checked' : ''; ?>>
            <label for="income">Income</label>

            <input type="radio" id="expense" name="type" value="expense"
                <?php echo (($_POST['type'] ?? '') === 'expense') ? 'checked' : ''; ?>>
            <label for="expense">Expense</label>
        </div>

        <label>Amount (RM): *</label>
        <input type="number" name="amount" step="0.01" min="0.01"
               value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>"
               placeholder="e.g. 50.00">

        <label>Category: *</label>
        <select name="category">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?php echo $c; ?>"
                    <?php echo (($_POST['category'] ?? '') === $c) ? 'selected' : ''; ?>>
                    <?php echo $c; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Description:</label>
        <input type="text" name="description"
               value="<?php echo htmlspecialchars($_POST['description'] ?? ''); ?>"
               placeholder="e.g. Lunch at cafeteria">

        <label>Date: *</label>
        <input type="date" name="date"
               value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d')); ?>">

        <br><br>
        <input type="submit" name="submit" value="Save Record">
    </form>

    <p><a href="money_list.php">Cancel and go back</a></p>

</div>
</body>
</html>
<?php mysqli_close($con); ?>

<?php

include("../footer.php");

?>