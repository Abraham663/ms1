<?php
require_once 'config.php';

$student = null;
$errors = [];
$success = false;
$account_number = isset($_GET['account_number']) ? trim($_GET['account_number']) : '';

// Fetch student if account_number provided
if (!empty($account_number)) {
    $stmt = $conn->prepare("SELECT id, account_number, full_name, email, phone, photo_path, hostel_name, room_number, food_preference FROM students WHERE account_number = ?");
    if ($stmt) {
        $stmt->bind_param("s", $account_number);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
        } else {
            $errors[] = 'Student not found.';
        }
        $stmt->close();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($account_number)) {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $hostel_name = isset($_POST['hostel_name']) ? trim($_POST['hostel_name']) : '';
    $room_number = isset($_POST['room_number']) ? trim($_POST['room_number']) : '';
    $food_preference = isset($_POST['food_preference']) ? trim($_POST['food_preference']) : 'veg';

    // Basic validation
    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($hostel_name === '') $errors[] = 'Hostel name is required.';
    if ($room_number === '') $errors[] = 'Room number is required.';

    // Handle file upload
    $photo_path = $student['photo_path'] ?? '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['photo'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/png','image/jpeg','image/webp'];
            if (!in_array($file['type'], $allowed)) {
                $errors[] = 'Only PNG, JPG, or WEBP images are allowed.';
            } else {
                $uploadsDir = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images';
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $safeName = preg_replace('/[^A-Za-z0-9_-]/', '_', $account_number);
                $newFilename = $safeName . '_' . time() . '.' . $ext;
                $destination = $uploadsDir . DIRECTORY_SEPARATOR . $newFilename;
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $photo_path = 'assets/images/' . $newFilename;
                } else {
                    $errors[] = 'Failed to move uploaded file.';
                }
            }
        }
    }

    // Update student in DB
    if (empty($errors)) {
        $query = "UPDATE students SET full_name = ?, email = ?, phone = ?, photo_path = ?, hostel_name = ?, room_number = ?, food_preference = ? WHERE account_number = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('ssssssss', $full_name, $email, $phone, $photo_path, $hostel_name, $room_number, $food_preference, $account_number);
            if ($stmt->execute()) {
                // Redirect back to index to show updated details
                header('Location: index.php?account_number=' . urlencode($account_number));
                exit;
            } else {
                $errors[] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = 'Database prepare error: ' . $conn->error;
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Student</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        html, body {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.6)), url('./images/pexels-enginakyurt-1435752.jpg') center/cover no-repeat fixed !important;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        body {
            width: 100%;
            padding: 20px;
        }

        .edit-container {
            max-width: 600px;
            width: 100%;
        }

        .edit-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .edit-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .edit-header h1 {
            font-size: 1.8rem;
            color: var(--cb-text);
            margin-bottom: 8px;
        }

        .edit-header p {
            color: var(--cb-muted);
            font-size: 0.95rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 14px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--cb-text);
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            padding: 10px 12px;
            border: 2px solid var(--cb-accent);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color .2s, box-shadow .2s;
            background: rgba(255, 255, 255, 0.98);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--dinner-bg);
            box-shadow: 0 0 0 6px rgba(var(--dinner-rgb), 0.08);
        }

        .photo-section {
            text-align: center;
            padding: 16px;
            background: rgba(var(--cb-accent-rgb), 0.04);
            border-radius: 12px;
            margin-bottom: 14px;
        }

        .photo-preview {
            max-width: 180px;
            height: auto;
            border-radius: 12px;
            margin: 12px auto;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--cb-accent);
            display: block;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .form-actions .btn {
            flex: 1;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert li {
            margin: 6px 0;
        }

        @media (max-width: 768px) {
            .edit-card {
                padding: 18px;
            }

            .edit-header h1 {
                font-size: 1.4rem;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .photo-preview {
                max-width: 140px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .edit-card {
                padding: 14px;
            }

            .edit-header h1 {
                font-size: 1.2rem;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="edit-container">
    <div class="edit-card">
        <div class="edit-header">
            <h1>✏️ Edit Student Details</h1>
            <p><?php echo !empty($student) ? 'Update info for ' . htmlspecialchars($student['full_name']) : 'Loading...'; ?></p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($student)): ?>
        <form method="POST" enctype="multipart/form-data" id="editForm">
            <div class="photo-section">
                <label>📷 Profile Photo</label>
                <?php if (!empty($student['photo_path']) && file_exists($student['photo_path'])): ?>
                    <img id="photoPreview" class="photo-preview" src="<?php echo htmlspecialchars($student['photo_path']); ?>" alt="Current Photo">
                <?php else: ?>
                    <img id="photoPreview" class="photo-preview" src="assets/images/default-profile.png" alt="Default Photo">
                <?php endif; ?>
                <div style="margin-top: 10px;">
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <small style="color: var(--cb-muted);">PNG, JPG, or WEBP</small>
                </div>
            </div>

            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input id="full_name" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="hostel_name">Hostel Name *</label>
                <input id="hostel_name" name="hostel_name" value="<?php echo htmlspecialchars($student['hostel_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="room_number">Room Number *</label>
                <input id="room_number" name="room_number" value="<?php echo htmlspecialchars($student['room_number']); ?>" required>
            </div>

            <div class="form-group">
                <label for="food_preference">Food Preference</label>
                <select id="food_preference" name="food_preference">
                    <option value="veg" <?php echo $student['food_preference'] === 'veg' ? 'selected' : ''; ?>>Veg</option>
                    <option value="non-veg" <?php echo $student['food_preference'] === 'non-veg' ? 'selected' : ''; ?>>Non-Veg</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                <a href="index.php?account_number=<?php echo urlencode($account_number); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <script>
            const photoInput = document.getElementById('photo');
            const photoPreview = document.getElementById('photoPreview');

            photoInput.addEventListener('change', function(e) {
                const file = this.files[0];
                if (!file) return;
                const allowed = ['image/png','image/jpeg','image/webp'];
                if (!allowed.includes(file.type)) {
                    alert('Only PNG, JPG, or WEBP images are allowed.');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(ev) {
                    photoPreview.src = ev.target.result;
                }
                reader.readAsDataURL(file);
            });

            document.getElementById('editForm').addEventListener('submit', function(e) {
                const name = document.getElementById('full_name').value.trim();
                const hostel = document.getElementById('hostel_name').value.trim();
                const room = document.getElementById('room_number').value.trim();
                if (!name || !hostel || !room) {
                    alert('Please fill required fields.');
                    e.preventDefault();
                    return false;
                }
            });
        </script>
        <?php else: ?>
            <div class="alert alert-error">
                <p>No student found. Please search for a student first.</p>
                <a href="index.php" class="btn btn-secondary" style="margin-top: 12px; display: inline-block;">← Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
