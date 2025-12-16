<?php
require_once 'config.php';

$student = null;
$error = null;
$success = false;
$meal_recorded = false;
$meal_message = '';

// Get today's date
$today_date = date('F j, Y');
$current_time = date('H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle meal tracking
    if (isset($_POST['meal_type'])) {
        $account_number = isset($_POST['account_number']) ? trim($_POST['account_number']) : '';
        $meal_type = isset($_POST['meal_type']) ? trim($_POST['meal_type']) : '';
        // Use the time sent from JavaScript (client-side time) or server time as fallback
        $meal_time = isset($_POST['click_time']) ? trim($_POST['click_time']) : date('Y-m-d H:i:s');
        
        if (!empty($account_number) && !empty($meal_type)) {
            // First check if student exists
            $check_stmt = $conn->prepare("SELECT id FROM students WHERE account_number = ? AND status = 'active'");
            $check_stmt->bind_param("s", $account_number);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $student_id = $check_result->fetch_assoc()['id'];
                
                // Create meals table if it doesn't exist
                $conn->query("CREATE TABLE IF NOT EXISTS meal_records (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    student_id INT NOT NULL,
                    account_number VARCHAR(50) NOT NULL,
                    meal_type VARCHAR(20) NOT NULL,
                    meal_time DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (student_id) REFERENCES students(id)
                )");
                
                // Insert meal record
                $insert_stmt = $conn->prepare("INSERT INTO meal_records (student_id, account_number, meal_type, meal_time) VALUES (?, ?, ?, ?)");
                $insert_stmt->bind_param("isss", $student_id, $account_number, $meal_type, $meal_time);
                
                if ($insert_stmt->execute()) {
                    $meal_recorded = true;
                    $meal_message = ucfirst($meal_type) . " recorded at " . date('h:i:s A', strtotime($meal_time));
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        }
    } else {
        // Handle student search
        $account_number = isset($_POST['account_number']) ? trim($_POST['account_number']) : '';
        
        if (empty($account_number)) {
            $error = 'Please enter an account number';
        } else {
            $stmt = $conn->prepare("SELECT id, account_number, full_name, email, phone, photo_path, hostel_name, room_number, food_preference, registration_date FROM students WHERE account_number = ? AND status = 'active'");
            
            if ($stmt) {
                $stmt->bind_param("s", $account_number);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $student = $result->fetch_assoc();
                    $photo_path = $student['photo_path'];
                    if (!file_exists($photo_path) || empty($photo_path)) {
                        $student['photo_path'] = 'assets/images/default-profile.png';
                    }
                    $success = true;
                } else {
                    $error = 'Student not found';
                }
                $stmt->close();
            } else {
                $error = 'Database error';
            }
        }
    }
}

// If no POST search, allow GET-based search for fast auto-search (from JS)
if (empty($student) && isset($_GET['account_number'])) {
    $account_number = trim($_GET['account_number']);
    if (!empty($account_number)) {
        $stmt = $conn->prepare("SELECT id, account_number, full_name, email, phone, photo_path, hostel_name, room_number, food_preference, registration_date FROM students WHERE account_number = ? AND status = 'active'");
        if ($stmt) {
            $stmt->bind_param("s", $account_number);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $student = $result->fetch_assoc();
                $photo_path = $student['photo_path'];
                if (!file_exists($photo_path) || empty($photo_path)) {
                    $student['photo_path'] = 'assets/images/default-profile.png';
                }
                $success = true;
            } else {
                $error = 'Student not found';
            }
            $stmt->close();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Mess Management - Home & Search</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.6)), url('./images/pexels-enginakyurt-1435752.jpg') center/cover no-repeat fixed !important;
            min-height: 100vh;
        }
        
        .header-subtitle {
            background: linear-gradient(90deg, #ffb3c1 0%, #ff758f 50%, #c9184a 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
            font-weight: 700 !important;
            font-size: 1.05rem !important;
            letter-spacing: 0.5px !important;
            margin: 8px 0 0 0 !important;
        }
        
        .header-title {
            background: linear-gradient(90deg, #ffb3c1 0%, #ff758f 50%, #c9184a 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
            font-weight: 800 !important;
            font-size: 2rem !important;
            letter-spacing: -0.5px !important;
            margin: 0 !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Logo -->
        <div class="header">
            <div class="header-content">
                <div class="header-logo">
                    <img src="images/—Pngtree—3d hostel pin_13164212.png" alt="Hostel Logo">
                </div>
                <div class="header-text">
                    <h1 class="header-title">🏠 Hostel Mess Management</h1>
                    <p class="header-subtitle">Student Lookup – By Account Number</p>
                    
                    <p><a href="register.php" class="btn btn-secondary" style="display:inline-block;margin-top:8px;">➕ Register</a></p>
                </div>
            </div>
        </div>

        <!-- Search Card -->
        <div class="search-card">
            <form method="POST" class="search-form" onsubmit="return false;">
                <input 
                    type="text" 
                    id="accountNumberInput"
                    name="account_number" 
                    placeholder="Enter Student Account Number (e.g., 541)" 
                    value="<?php echo isset($_POST['account_number']) ? htmlspecialchars($_POST['account_number']) : (isset($_GET['account_number']) ? htmlspecialchars($_GET['account_number']) : ''); ?>"
                    oninput="debouncedSearch(this.value)"
                >
                <button type="submit" id="manualSearchBtn">Search</button>
            </form>

            <!-- Alert Messages -->
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success && $student): ?>
                <div class="alert alert-success">
                    Student found: <?php echo htmlspecialchars($student['full_name']); ?>
                </div>
            <?php endif; ?>

            <?php if ($meal_recorded): ?>
                <div class="alert alert-success">
                    ✓ <?php echo $meal_message; ?>
                </div>
            <?php endif; ?>

            <!-- Empty State -->
            <?php if (!$student): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p>Enter a student account number and click search to view details</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Student Details Card -->
        <?php if ($student): ?>
        <div class="student-details">
            <!-- Student Header with Photo -->
            <div class="student-header centered">
                <div class="student-photo">
                    <img src="<?php echo htmlspecialchars($student['photo_path']); ?>" alt="Student Photo">
                </div>
                <div class="student-basic-info">
                    <h2><?php echo htmlspecialchars($student['full_name']); ?></h2>
                    <p><strong>Account Number:</strong> <?php echo htmlspecialchars($student['account_number']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone'] ?: 'N/A'); ?></p>
                    <p><strong>Hostel:</strong> <?php echo htmlspecialchars($student['hostel_name'] ?: 'N/A'); ?></p>
                    <p><strong>Room:</strong> <?php echo htmlspecialchars($student['room_number'] ?: 'N/A'); ?></p>
                </div>
            </div>

            <!-- Student Details Body -->
            <div class="student-body">
                <div class="info-grid">
                    <!-- Food Preference -->
                    <div class="info-card">
                        <h3>🍽️ Food Preference</h3>
                        <p>
                            <span class="food-preference <?php echo $student['food_preference']; ?>">
                                <?php echo strtoupper($student['food_preference']); ?>
                            </span>
                        </p>
                    </div>

                    <!-- Registration Date -->
                    <div class="info-card">
                        <h3>📅 Registered Since</h3>
                        <p><?php echo $today_date; ?></p>
                    </div>
                </div>

                <!-- Meal Tracking Section -->
                <div class="meal-section">
                    <h3>🍴 Record Meal Entry for Today</h3>
                    <div class="meal-buttons">
                        <form method="POST" style="flex: 1;" onsubmit="setClickTime(event)">
                            <input type="hidden" name="account_number" value="<?php echo htmlspecialchars($student['account_number']); ?>">
                            <input type="hidden" name="meal_type" value="breakfast">
                            <input type="hidden" name="click_time" id="breakfast_time">
                            <button type="submit" class="btn btn-meal breakfast">
                                🌅 Breakfast<br>
                                <span class="meal-time">7:30 AM - 8:30 AM</span>
                            </button>
                        </form>
                        <form method="POST" style="flex: 1;" onsubmit="setClickTime(event)">
                            <input type="hidden" name="account_number" value="<?php echo htmlspecialchars($student['account_number']); ?>">
                            <input type="hidden" name="meal_type" value="lunch">
                            <input type="hidden" name="click_time" id="lunch_time">
                            <button type="submit" class="btn btn-meal lunch">
                                🌤️ Lunch<br>
                                <span class="meal-time">12:30 PM - 1:30 PM</span>
                            </button>
                        </form>
                        <form method="POST" style="flex: 1;" onsubmit="setClickTime(event)">
                            <input type="hidden" name="account_number" value="<?php echo htmlspecialchars($student['account_number']); ?>">
                            <input type="hidden" name="meal_type" value="dinner">
                            <input type="hidden" name="click_time" id="dinner_time">
                            <button type="submit" class="btn btn-meal dinner">
                                🌙 Dinner<br>
                                <span class="meal-time">7:00 PM - 8:00 PM</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="edit.php?account_number=<?php echo urlencode($student['account_number']); ?>" class="btn btn-primary">✏️ Edit Details</a>
                    <a href="meal_history.php?account_number=<?php echo urlencode($student['account_number']); ?>" class="btn btn-primary">📊 View Meal History</a>
                    <form method="POST" style="flex: 1;">
                        <button type="submit" class="btn btn-secondary" style="width: 100%;">🔄 New Search</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Debounced auto-search: updates location to ?account_number= after user pauses typing
        let searchTimer = null;
        function debouncedSearch(value) {
            clearTimeout(searchTimer);
            // if empty, do nothing (could also clear results)
            if (!value) return;
            searchTimer = setTimeout(function() {
                const url = window.location.pathname + '?account_number=' + encodeURIComponent(value);
                window.location.href = url;
            }, 600); // 600ms debounce
        }

        // Set exact click time into hidden input before submitting meal form
        function setClickTime(event) {
            const form = event.target;
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const date = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const clickTime = `${year}-${month}-${date} ${hours}:${minutes}:${seconds}`;
            const timeInput = form.querySelector('input[name="click_time"]');
            if (timeInput) {
                timeInput.value = clickTime;
            }
            // allow form to submit normally after setting time
            return true;
        }
    </script>
</body>
</html>
