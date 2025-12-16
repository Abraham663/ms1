<?php
require_once 'config.php';
$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account = isset($_POST['account_number']) ? trim($_POST['account_number']) : '';
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $quality = isset($_POST['quality']) ? (int)$_POST['quality'] : 0;
    $hygiene = isset($_POST['hygiene']) ? (int)$_POST['hygiene'] : 0;
    $service = isset($_POST['service']) ? (int)$_POST['service'] : 0;
    $comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';

    if ($account === '') $errors[] = 'Account number is required.';
    if ($rating < 1 || $rating > 5) $errors[] = 'Rating must be between 1 and 5.';

    if (empty($errors)) {
        // find student
        $s = $conn->prepare("SELECT id FROM students WHERE account_number = ?");
        $s->bind_param('s', $account);
        $s->execute();
        $r = $s->get_result();
        $student_id = null;
        if ($r->num_rows > 0) $student_id = $r->fetch_assoc()['id'];
        $s->close();

        $ins = $conn->prepare("INSERT INTO feedback (student_id, account_number, rating, quality, hygiene, service, comments) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param('isiiiss', $student_id, $account, $rating, $quality, $hygiene, $service, $comments);
        if ($ins->execute()) {
            $success = true;
        } else {
            $errors[] = 'Database error: ' . $ins->error;
        }
        $ins->close();
    }
}

// Fetch recent feedback for admin view
$fb_res = $conn->query("SELECT f.*, s.full_name FROM feedback f LEFT JOIN students s ON f.student_id = s.id ORDER BY f.created_at DESC LIMIT 50");
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Feedback & Ratings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-content">
            <div class="header-logo"><img src="images/—Pngtree—3d hostel pin_13164212.png" alt="Logo"></div>
            <div class="header-text"><h1>Feedback & Ratings</h1><p class="today-date">Student feedback on food & service</p></div>
        </div>
    </div>

    <div class="search-card">
        <?php if ($success): ?>
            <div class="alert alert-success">Thank you for your feedback!</div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Account Number *</label>
                <input name="account_number" value="<?php echo isset($_POST['account_number'])?htmlspecialchars($_POST['account_number']):''; ?>">
            </div>
            <div class="form-group">
                <label>Overall Rating (1-5)</label>
                <select name="rating">
                    <?php for ($i=5;$i>=1;$i--) echo '<option value="'.$i.'">'.$i.'</option>'; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Quality (1-5)</label>
                <select name="quality"><?php for($i=5;$i>=0;$i--) echo '<option value="'.$i.'">'.$i.'</option>'; ?></select>
            </div>
            <div class="form-group">
                <label>Hygiene (1-5)</label>
                <select name="hygiene"><?php for($i=5;$i>=0;$i--) echo '<option value="'.$i.'">'.$i.'</option>'; ?></select>
            </div>
            <div class="form-group">
                <label>Service (1-5)</label>
                <select name="service"><?php for($i=5;$i>=0;$i--) echo '<option value="'.$i.'">'.$i.'</option>'; ?></select>
            </div>
            <div class="form-group">
                <label>Comments</label>
                <textarea name="comments" rows="4"><?php echo isset($_POST['comments'])?htmlspecialchars($_POST['comments']):''; ?></textarea>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Submit Feedback</button>
                <a class="btn btn-secondary" href="index.php">Back</a>
            </div>
        </form>

        <hr style="margin:18px 0;">
        <h3>Recent Feedback</h3>
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr style="background:#f7f7f7;"><th style="padding:8px;border:1px solid #eee;text-align:left;">Student</th><th style="padding:8px;border:1px solid #eee;">Rating</th><th style="padding:8px;border:1px solid #eee;">Comments</th><th style="padding:8px;border:1px solid #eee;">Date</th></tr></thead>
            <tbody>
                <?php while ($row = $fb_res->fetch_assoc()) {
                    echo '<tr><td style="padding:8px;border:1px solid #eee;">'.htmlspecialchars($row['full_name']?:$row['account_number']).'</td>';
                    echo '<td style="padding:8px;border:1px solid #eee;">'.htmlspecialchars($row['rating']).'</td>';
                    echo '<td style="padding:8px;border:1px solid #eee;">'.nl2br(htmlspecialchars($row['comments'])).'</td>';
                    echo '<td style="padding:8px;border:1px solid #eee;">'.htmlspecialchars($row['created_at']).'</td></tr>';
                } ?>
            </tbody>
        </table>

    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>