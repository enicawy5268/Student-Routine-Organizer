<?php
include("../auth.php");
require("../database.php");



if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if (
    $_SESSION['role'] == 'admin'
) {

    header(
        "Location: money_list.php"
    );

    exit();
}


$error = "";
$id    = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);

$stmt = mysqli_prepare($con, "SELECT * FROM money_tracker WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$record = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$record) {
    $_SESSION['message'] = "Record not found or access denied.";
    header("Location: money_list.php");
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
        $sql  = "UPDATE money_tracker
                 SET amount = ?, category = ?, description = ?, type = ?, date = ?
                 WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($con, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "dssssii",
                $amount, $category, $description, $type, $date, $id, $user_id);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['message'] = "Record updated successfully.";
                header("Location: money_list.php");
                exit();
            } else {
                $error = "Error updating record: " . mysqli_stmt_error($stmt);
            }
        } else {
            $error = "Error preparing statement: " . mysqli_error($con);
        }
    }

    $record['amount']      = $amount;
    $record['category']    = $category;
    $record['description'] = $description;
    $record['type']        = $type;
    $record['date']        = $date;
}

$categories = ['Food', 'Transport', 'Entertainment', 'Education',
               'Health', 'Salary', 'Allowance', 'Others'];
?>




<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Record - Money Tracker</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="navbar">
    <a href="../dashboard.php">Dashboard</a>
    <a href="money_list.php">Money Tracker</a>
    <a href="logout.php" style="float: right;">Logout</a>
</div>

<div class="container">

    <h1>Edit Record</h1>

    <?php if ($error !== ''): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="money_edit.php?id=<?php echo $id; ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <label>Transaction Type: *</label>
        <div class="checkbox-row">
            <input type="radio" id="income" name="type" value="income"
                <?php echo ($record['type'] === 'income') ? 'checked' : ''; ?>>
            <label for="income">Income</label>

            <input type="radio" id="expense" name="type" value="expense"
                <?php echo ($record['type'] === 'expense') ? 'checked' : ''; ?>>
            <label for="expense">Expense</label>
        </div>

        <label>Amount (RM): *</label>
        <input type="number" name="amount" step="0.01" min="0.01"
               value="<?php echo htmlspecialchars($record['amount']); ?>">

        <label>Category: *</label>
        <select name="category">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?php echo $c; ?>"
                    <?php echo ($record['category'] === $c) ? 'selected' : ''; ?>>
                    <?php echo $c; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Description:</label>
        <input type="text" name="description"
               value="<?php echo htmlspecialchars($record['description']); ?>">

        <label>Date: *</label>
        <input type="date" name="date"
               value="<?php echo htmlspecialchars($record['date']); ?>">

        <br><br>
        <input type="submit" name="submit" value="Update Record">
    </form>

    <p><a href="money_list.php">Cancel and go back</a></p>

</div>
</body>
</html>
<?php mysqli_close($con); ?>

<?php

include("../footer.php");

?>