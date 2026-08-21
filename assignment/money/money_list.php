<?php
include("../auth.php");
require("../database.php");



if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';



$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$search      = $_GET['search'] ?? '';
$filter_type = $_GET['filter_type'] ?? 'all';
$sort        = $_GET['sort'] ?? 'date';
$order       = $_GET['order'] ?? 'DESC';

$valid_columns = ['date', 'amount'];
if (!in_array($sort, $valid_columns)) {
    $sort = 'date';
}
$order = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';

$sql_query = "SELECT * FROM money_tracker WHERE user_id = ?";
$types     = "i";
$params    = [$user_id];

if ($filter_type === 'income' || $filter_type === 'expense') {
    $sql_query .= " AND type = ?";
    $types     .= "s";
    $params[]   = $filter_type;
}

if (!empty($search)) {
    $sql_query .= " AND (category LIKE ? OR description LIKE ?)";
    $types     .= "ss";
    $params[]   = "%$search%";
    $params[]   = "%$search%";
}

$sql_query .= " ORDER BY $sort $order";

$records       = [];
$total_income  = 0;
$total_expense = 0;

$stmt = mysqli_prepare($con, $sql_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
        if ($row['type'] === 'income') {
            $total_income += $row['amount'];
        } else {
            $total_expense += $row['amount'];
        }
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p class='error'>Unable to load records: " . mysqli_error($con) . "</p>";
}

$balance = $total_income - $total_expense;
?>


<?php

$page_title = "Money Tracker";
include("../header.php");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Money Tracker</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<!-- <div class="navbar">
   
    <a href="../dashboard.php">Dashboard</a>
    <a href="money_list.php">Money Tracker</a>
    <a href="logout.php" style="float: right;">Logout</a>
</div> -->

<div class="container">

    <h1>Money Tracker</h1>
    <p class="small-text">Welcome, <?php echo htmlspecialchars($username); ?>. Manage your income and expenses below.</p>

    <?php if ($message !== ''): ?>
        <p class="success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <div class="card-row">
        <div class="card">
            <h3>Total Income</h3>
            <p>RM <?php echo number_format($total_income, 2); ?></p>
        </div>
        <div class="card">
            <h3>Total Expense</h3>
            <p>RM <?php echo number_format($total_expense, 2); ?></p>
        </div>
        <div class="card">
            <h3>Balance</h3>
            <p>RM <?php echo number_format($balance, 2); ?></p>
        </div>
    </div>

    <hr>

    <h3>Search &amp; Filter</h3>
    <form method="GET" action="money_list.php">
        <div class="card-row">
            <div class="card">
                <label>Search</label>
                <input type="text" name="search"
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Category or description">
            </div>
            <div class="card">
                <label>Type</label>
                <select name="filter_type">
                    <option value="all"     <?php echo $filter_type === 'all'     ? 'selected' : ''; ?>>All</option>
                    <option value="income"  <?php echo $filter_type === 'income'  ? 'selected' : ''; ?>>Income</option>
                    <option value="expense" <?php echo $filter_type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                </select>
            </div>
            <div class="card">
                <label>Sort By</label>
                <select name="sort">
                    <option value="date"   <?php echo $sort === 'date'   ? 'selected' : ''; ?>>Date</option>
                    <option value="amount" <?php echo $sort === 'amount' ? 'selected' : ''; ?>>Amount</option>
                </select>
            </div>
            <div class="card">
                <label>Order</label>
                <select name="order">
                    <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                    <option value="ASC"  <?php echo $order === 'ASC'  ? 'selected' : ''; ?>>Ascending</option>
                </select>
            </div>
        </div>
        <button type="submit">Search &amp; Filter</button>
        <button type="button" onclick="window.location.href='money_list.php'">Reset</button>
    </form>

    <hr>

    <h3>My Records</h3>
    <p class="small-text">Showing <?php echo count($records); ?> record(s).</p>
    <p><a href="money_add.php">+ Add New Record</a></p>

    <div class="table-wrapper">
    <table>
        <tr>
            <th>No.</th>
            <th>Date</th>
            <th>Type</th>
            <th>Category</th>
            <th>Description</th>
            <th>Amount (RM)</th>
            <th>Action</th>
        </tr>

        <?php if (count($records) === 0): ?>
            <tr>
                <td colspan="7">No records found. Click "Add New Record" to get started.</td>
            </tr>
        <?php else: ?>
            <?php $count = 1; foreach ($records as $row): ?>
            <tr>
                <td><?php echo $count++; ?></td>
                <td><?php echo htmlspecialchars($row['date']); ?></td>
                <td><?php echo ($row['type'] === 'income') ? 'Income' : 'Expense'; ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo number_format($row['amount'], 2); ?></td>
                <td>
                    <a href="money_edit.php?id=<?php echo (int)$row['id']; ?>">Edit</a> |
                    <a href="money_delete.php?id=<?php echo (int)$row['id']; ?>"
                       onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    </div>

</div>
</body>
</html>
<?php mysqli_close($con); ?>

<?php

include("../footer.php");

?>