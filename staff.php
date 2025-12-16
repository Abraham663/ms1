<?php
require_once 'config.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $joined = trim($_POST['joined_at'] ?? null);
    if ($name === '' || $role === '') $errors[] = 'Name and role are required.';
    if (empty($errors)) {
        $ins = $conn->prepare("INSERT INTO staff (full_name, role, phone, email, joined_at) VALUES (?, ?, ?, ?, ?)");
        $ins->bind_param('sssss', $name, $role, $phone, $email, $joined);
        $ins->execute();
        $ins->close();
        header('Location: staff.php'); exit;
    }
}

// Fetch staff
$res = $conn->query("SELECT * FROM staff ORDER BY active DESC, joined_at DESC");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Staff Management</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="header"><div class="header-content"><div class="header-logo"><img src="images/—Pngtree—3d hostel pin_13164212.png" alt="Logo"></div><div class="header-text"><h1>Mess Staff</h1><p class="today-date">Manage mess working members</p></div></div></div>

    <div class="search-card">
        <?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div><?php endif; ?>
        <h3>Add Staff Member</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group"><label>Full Name</label><input name="full_name"></div>
            <div class="form-group"><label>Role</label><input name="role"></div>
            <div class="form-group"><label>Phone</label><input name="phone"></div>
            <div class="form-group"><label>Email</label><input name="email" type="email"></div>
            <div class="form-group"><label>Joined At</label><input name="joined_at" type="date"></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Add</button><a class="btn btn-secondary" href="index.php">Back</a></div>
        </form>

        <hr style="margin:16px 0;">
        <h3>Current Staff</h3>
        <table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#f7f7f7;"><th style="padding:8px;border:1px solid #eee;">Name</th><th style="padding:8px;border:1px solid #eee;">Role</th><th style="padding:8px;border:1px solid #eee;">Phone</th><th style="padding:8px;border:1px solid #eee;">Email</th><th style="padding:8px;border:1px solid #eee;">Joined</th></tr></thead><tbody>
        <?php while ($r = $res->fetch_assoc()) {
            echo '<tr><td style="padding:8px;border:1px solid #eee;">'.htmlspecialchars($r['full_name']).'</td>';
            echo '<td style="padding:8px;border:1px solid #eee;">'.htmlspecialchars($r['role']).'</td>';
            echo '<td style="padding:8px;border:1px solid #eee;">'.htmlspecialchars($r['phone']).'</td>';
            echo '<td style="padding:8px;border:1px solid #eee;">'.htmlspecialchars($r['email']).'</td>';
            echo '<td style="padding:8px;border:1px solid #eee;">'.htmlspecialchars($r['joined_at']).'</td></tr>';
        }
        ?></tbody></table>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>