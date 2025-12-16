<?php
require_once 'config.php';
// Show menu for the week and allow students to mark interest
$account = isset($_GET['account_number']) ? trim($_GET['account_number']) : '';

// Handle interest POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_id'])) {
    $menu_id = (int)$_POST['menu_id'];
    $interested = isset($_POST['interested']) && $_POST['interested'] === 'yes' ? 'yes' : 'no';
    $acct = trim($_POST['account_number'] ?? '');
    if ($acct !== '') {
        // find student
        $s = $conn->prepare("SELECT id FROM students WHERE account_number = ?");
        $s->bind_param('s', $acct);
        $s->execute();
        $r = $s->get_result();
        $student_id = null;
        if ($r->num_rows > 0) $student_id = $r->fetch_assoc()['id'];
        $s->close();
        if ($student_id) {
            // upsert interest
            $ins = $conn->prepare("INSERT INTO meal_interest (student_id, account_number, menu_id, interested) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE interested = VALUES(interested), created_at = CURRENT_TIMESTAMP");
            $ins->bind_param('isis', $student_id, $acct, $menu_id, $interested);
            $ins->execute();
            $ins->close();
        }
    }
}

// Get next 7 days menu
$today = date('Y-m-d');
$end = date('Y-m-d', strtotime('+6 days'));
$stmt = $conn->prepare("SELECT * FROM menu WHERE menu_date BETWEEN ? AND ? ORDER BY menu_date, FIELD(meal_type,'breakfast','lunch','dinner')");
$stmt->bind_param('ss', $today, $end);
$stmt->execute();
$menu_res = $stmt->get_result();
$menus = [];
while ($m = $menu_res->fetch_assoc()) {
    $menus[$m['menu_date']][$m['meal_type']] = $m;
}
$stmt->close();

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Menu & Interest</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="header"><div class="header-content"><div class="header-logo"><img src="images/—Pngtree—3d hostel pin_13164212.png" alt="Logo"></div><div class="header-text"><h1>Weekly Menu</h1><p class="today-date">Mark interest for meals</p></div></div></div>

    <div class="search-card">
        <form method="GET" style="margin-bottom:12px;"><label>Account Number (optional to mark interest)</label><input name="account_number" value="<?php echo htmlspecialchars($account); ?>"><button class="btn btn-primary" type="submit">Load</button></form>
        <?php if (empty($menus)) echo '<p>No menu defined for the next 7 days.</p>'; ?>

        <?php foreach ($menus as $date => $meals): ?>
            <div style="margin-bottom:12px;padding:12px;border-radius:10px;border:1px solid #eee;background:#fff;">
                <h3><?php echo htmlspecialchars(date('l, M j, Y', strtotime($date))); ?></h3>
                <?php foreach (['breakfast','lunch','dinner'] as $mt):
                    $mitem = $meals[$mt] ?? null; ?>
                    <div style="margin-bottom:8px;padding:8px;border-radius:8px;border:1px solid #f0f0f0;background:#fafafa;">
                        <strong><?php echo ucfirst($mt); ?></strong>
                        <div><?php echo $mitem ? htmlspecialchars($mitem['title']) : '<em>Not set</em>'; ?></div>
                        <div style="margin-top:6px;">
                            <?php if ($account && $mitem): ?>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="account_number" value="<?php echo htmlspecialchars($account); ?>">
                                    <input type="hidden" name="menu_id" value="<?php echo (int)$mitem['id']; ?>">
                                    <button class="btn" name="interested" value="yes">Interested</button>
                                    <button class="btn" name="interested" value="no">Not Interested</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <p style="margin-top:12px;"><a href="index.php" class="btn btn-secondary">← Back</a></p>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>