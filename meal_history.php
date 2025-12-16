<?php
require_once 'config.php';

$account = isset($_GET['account_number']) ? trim($_GET['account_number']) : '';
if ($account === '') {
    echo "<p>Please provide an account_number in query string, e.g. ?account_number=541</p>";
    exit;
}

// Get student
$stmt = $conn->prepare("SELECT id, full_name FROM students WHERE account_number = ?");
$stmt->bind_param('s', $account);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo "<p>Student not found.</p>";
    exit;
}
$student = $res->fetch_assoc();
$student_id = $student['id'];

// Fetch meal records
$mr = $conn->prepare("SELECT meal_type, meal_time, created_at FROM meal_records WHERE student_id = ? ORDER BY meal_time DESC LIMIT 500");
$mr->bind_param('i', $student_id);
$mr->execute();
$meals = $mr->get_result();

// Compute simple billing: constants
$price = ['breakfast' => 20.00, 'lunch' => 40.00, 'dinner' => 35.00];
$total = 0.0;
$counts = ['breakfast'=>0,'lunch'=>0,'dinner'=>0];
while ($r = $meals->fetch_assoc()) {
    $counts[$r['meal_type']]++;
    $total += $price[$r['meal_type']];
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meal History - <?php echo htmlspecialchars($student['full_name']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-content">
            <div class="header-logo"><img src="images/—Pngtree—3d hostel pin_13164212.png" alt="Logo"></div>
            <div class="header-text">
                <h1>Meal History</h1>
                <p class="today-date"><?php echo htmlspecialchars($student['full_name']) . ' — ' . htmlspecialchars($account); ?></p>
            </div>
        </div>
    </div>

    <div class="search-card">
        <h2>Summary</h2>
        <p>Breakfasts: <?php echo $counts['breakfast']; ?> &nbsp; Lunches: <?php echo $counts['lunch']; ?> &nbsp; Dinners: <?php echo $counts['dinner']; ?></p>
        <p><strong>Estimated Amount Due:</strong> ₹ <?php echo number_format($total,2); ?></p>

        <h3 style="margin-top:18px;">Recent Meal Records</h3>
        <table style="width:100%;border-collapse:collapse;margin-top:8px;">
            <thead>
                <tr style="background:#f7f7f7;"><th style="padding:8px;border:1px solid #eee;text-align:left;">Meal</th><th style="padding:8px;border:1px solid #eee;text-align:left;">Time</th></tr>
            </thead>
            <tbody>
<?php
// Re-run query from beginning
$mr->execute();
$meals2 = $mr->get_result();
while ($row = $meals2->fetch_assoc()) {
    echo '<tr><td style="padding:8px;border:1px solid #eee;">' . htmlspecialchars(ucfirst($row['meal_type'])) . '</td><td style="padding:8px;border:1px solid #eee;">' . htmlspecialchars($row['meal_time']) . '</td></tr>';
}
$mr->close();
?>
            </tbody>
        </table>

        <p style="margin-top:14px;"><a href="index.php" class="btn btn-secondary">← Back</a></p>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>