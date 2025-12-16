<?php
require_once 'config.php';
$account = isset($_GET['account_number']) ? trim($_GET['account_number']) : '';

if ($account === '') {
    echo '<p>Please provide ?account_number= in the URL.</p>'; exit;
}

// Find student
$s = $conn->prepare("SELECT id, full_name FROM students WHERE account_number = ?");
$s->bind_param('s', $account);
$s->execute();
$r = $s->get_result();
if ($r->num_rows === 0) { echo '<p>Student not found.</p>'; exit; }
$student = $r->fetch_assoc();
$student_id = $student['id'];
$s->close();

// Calculate billing from meal_records
$price = ['breakfast' => 20.00, 'lunch' => 40.00, 'dinner' => 35.00];
$q = $conn->prepare("SELECT meal_type, COUNT(*) as cnt FROM meal_records WHERE student_id = ? GROUP BY meal_type");
$q->bind_param('i', $student_id);
$q->execute();
$res = $q->get_result();
$total = 0.0;
$counts = ['breakfast'=>0,'lunch'=>0,'dinner'=>0];
while ($row = $res->fetch_assoc()) {
    $counts[$row['meal_type']] = (int)$row['cnt'];
    $total += $counts[$row['meal_type']] * $price[$row['meal_type']];
}
$q->close();

// Get existing billing record (latest)
$b = $conn->prepare("SELECT * FROM billing WHERE student_id = ? ORDER BY last_updated DESC LIMIT 1");
$b->bind_param('i', $student_id);
$b->execute();
$billres = $b->get_result();
$billing = $billres->fetch_assoc();
$b->close();

// If POST to mark paid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_paid') {
    // create or update billing row
    if ($billing) {
        $u = $conn->prepare("UPDATE billing SET amount = ?, status = 'paid', last_updated = CURRENT_TIMESTAMP WHERE id = ?");
        $u->bind_param('di', $total, $billing['id']); $u->execute(); $u->close();
    } else {
        $ins = $conn->prepare("INSERT INTO billing (student_id, account_number, amount, due_date, status) VALUES (?, ?, ?, NULL, 'paid')");
        $ins->bind_param('isd', $student_id, $account, $total); $ins->execute(); $ins->close();
    }
    header('Location: billing.php?account_number=' . urlencode($account)); exit;
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Billing - <?php echo htmlspecialchars($student['full_name']); ?></title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="header"><div class="header-content"><div class="header-logo"><img src="images/—Pngtree—3d hostel pin_13164212.png" alt="Logo"></div><div class="header-text"><h1>Billing</h1><p class="today-date"><?php echo htmlspecialchars($student['full_name']); ?></p></div></div></div>

    <div class="search-card">
        <h3>Consumption Summary</h3>
        <p>Breakfast: <?php echo $counts['breakfast']; ?> × ₹<?php echo number_format($price['breakfast'],2); ?> = ₹<?php echo number_format($counts['breakfast']*$price['breakfast'],2); ?></p>
        <p>Lunch: <?php echo $counts['lunch']; ?> × ₹<?php echo number_format($price['lunch'],2); ?> = ₹<?php echo number_format($counts['lunch']*$price['lunch'],2); ?></p>
        <p>Dinner: <?php echo $counts['dinner']; ?> × ₹<?php echo number_format($price['dinner'],2); ?> = ₹<?php echo number_format($counts['dinner']*$price['dinner'],2); ?></p>
        <hr>
        <h2>Total Due: ₹ <?php echo number_format($total,2); ?></h2>

        <p>Payment Status: <strong><?php echo $billing ? htmlspecialchars($billing['status']) : 'unpaid'; ?></strong></p>

        <form method="POST" style="margin-top:12px;">
            <input type="hidden" name="action" value="mark_paid">
            <button class="btn btn-primary" type="submit">Mark as Paid</button>
            <a class="btn btn-secondary" href="index.php">Back</a>
        </form>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>