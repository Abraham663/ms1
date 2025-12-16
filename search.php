<?php
// search.php - Handle student search via AJAX
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_number = isset($_POST['account_number']) ? trim($_POST['account_number']) : '';
    
    // Validate input
    if (empty($account_number)) {
        echo json_encode(['success' => false, 'message' => 'Please enter an account number']);
        exit;
    }
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, account_number, full_name, email, phone, photo_path, hostel_name, room_number, food_preference, registration_date FROM students WHERE account_number = ? AND status = 'active'");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    
    $stmt->bind_param("s", $account_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // Check if photo exists
        $photo_path = $student['photo_path'];
        if (!file_exists($photo_path) || empty($photo_path)) {
            $photo_path = 'assets/images/default-profile.png';
        }
        
        $student['photo_path'] = $photo_path;
        
        echo json_encode(['success' => true, 'student' => $student]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
