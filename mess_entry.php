<?php
// mess_entry.php - API to record a meal entry (JSON)
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$account_number = isset($_POST['account_number']) ? trim($_POST['account_number']) : '';
$meal_type = isset($_POST['meal_type']) ? trim($_POST['meal_type']) : '';
$meal_time = isset($_POST['meal_time']) && !empty($_POST['meal_time']) ? $_POST['meal_time'] : date('Y-m-d H:i:s');

if ($account_number === '' || $meal_type === '') {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

// Validate meal_type
$allowed = ['breakfast','lunch','dinner'];
if (!in_array($meal_type, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid meal type']);
    exit;
}

// Find student
$stmt = $conn->prepare("SELECT id FROM students WHERE account_number = ? AND status = 'active'");
$stmt->bind_param('s', $account_number);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}
$student = $res->fetch_assoc();
$student_id = $student['id'];
$stmt->close();

// Insert meal record
$ins = $conn->prepare("INSERT INTO meal_records (student_id, account_number, meal_type, meal_time) VALUES (?, ?, ?, ?)");
$ins->bind_param('isss', $student_id, $account_number, $meal_type, $meal_time);
if ($ins->execute()) {
    echo json_encode(['success' => true, 'message' => ucfirst($meal_type) . ' recorded', 'meal_time' => $meal_time]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $ins->error]);
}
$ins->close();
$conn->close();
?>