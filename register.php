<?php
require_once 'config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_number = isset($_POST['account_number']) ? trim($_POST['account_number']) : '';
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $hostel_name = isset($_POST['hostel_name']) ? trim($_POST['hostel_name']) : '';
    $room_number = isset($_POST['room_number']) ? trim($_POST['room_number']) : '';
    $food_preference = isset($_POST['food_preference']) ? trim($_POST['food_preference']) : 'veg';

    // Basic validation
    if ($account_number === '') $errors[] = 'Account number is required.';
    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($hostel_name === '') $errors[] = 'Hostel name is required.';
    if ($room_number === '') $errors[] = 'Room number is required.';

    // Handle file upload
    $photo_path = '';
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
                    // store relative path for DB
                    $photo_path = 'assets/images/' . $newFilename;
                } else {
                    $errors[] = 'Failed to move uploaded file.';
                }
            }
        } else {
            $errors[] = 'Error uploading file.';
        }
    }

    if (empty($errors)) {
        // Insert student into DB or update existing record (upsert)
        $query = "INSERT INTO students (account_number, full_name, email, phone, photo_path, hostel_name, room_number, food_preference) VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                  ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), email = VALUES(email), phone = VALUES(phone), photo_path = VALUES(photo_path), hostel_name = VALUES(hostel_name), room_number = VALUES(room_number), food_preference = VALUES(food_preference), status = 'active'";

        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('ssssssss', $account_number, $full_name, $email, $phone, $photo_path, $hostel_name, $room_number, $food_preference);
            if ($stmt->execute()) {
                $success = true;
                // Redirect back to index with account number so user sees the new record
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
    <title>Register Student</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Page background and centering */
        html, body {
            background: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.6)), url('./images/pexels-enginakyurt-1435752.jpg') center/cover no-repeat fixed !important;
            min-height: 100vh;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 28px 20px;
        }

        .container {
            width: 100%;
            max-width: 680px;
        }

        /* Header styling - Professional */
        .header {
            border-radius: 14px 14px 0 0;
            background: linear-gradient(135deg, rgba(var(--header-rgb), 0.98) 0%, rgba(var(--header-rgb), 0.94) 100%);
            color: white !important;
            padding: 26px 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.20), inset 0 1px 0 rgba(255, 255, 255, 0.06);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.05), transparent 50%);
            pointer-events: none;
        }

        .header .header-content { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 16px; 
            position: relative;
            z-index: 1;
        }
        
        .header-logo { width: 70px; height: 70px; }
        .header-logo img { 
            border-radius: 12px; 
            background: white; 
            padding: 6px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }
        
        .header-text h1 { 
            margin: 0; 
            font-size: 1.7rem; 
            font-weight: 800;
            letter-spacing: -0.3px;
        }
        
        .header-text p { 
            margin: 6px 0 0 0; 
            font-size: 0.9rem;
        }

        /* Turn the top back-link into a button look */
        .header-text p { margin:6px 0 0 0; }
        .header-text p a.btn-home {
            display:inline-block;
            padding:8px 14px;
            background: rgba(255,255,255,0.12);
            color: #fff;
            border-radius: 10px;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.08);
            font-weight:600;
            transition: transform .12s ease, box-shadow .12s ease;
        }
        .header-text p a.btn-home:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.25); }

        /* Card */
        .search-card {
            background: rgba(255, 255, 255, 0.98) !important;
            border-radius: 0 0 14px 14px;
            padding: 20px !important;
            box-shadow: 0 8px 30px rgba(0,0,0,0.10);
            margin-top: 14px;
        }

        /* Form layout: stacked fields with clear spacing */
        .form-group { display:flex; flex-direction:column; margin-bottom:18px; }
        .form-group label { font-weight:700; color:var(--cb-text); margin-bottom:8px; }
        .form-group input, .form-group select {
            width:100%;
            padding:12px 14px;
            border-radius:10px;
            border:1px solid rgba(0,0,0,0.08);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
            font-size:1rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-group input::placeholder { color: #9b858c; }
        .form-group input:focus, .form-group select:focus { outline:none; border-color:var(--dinner-bg); box-shadow:0 6px 28px rgba(var(--dinner-rgb),0.06); }

        /* Buttons - moderate size (reduced) */
        .form-actions { display:flex; gap:12px; align-items:center; }
        .form-actions .btn { flex: none; min-width:110px; padding:8px 12px; font-size:0.95rem; border-radius:10px; }
        .form-actions .btn.btn-primary { background: var(--cb-cta); color: var(--cb-cta-contrast); border:none; }
        .form-actions .btn.btn-secondary { background: transparent; border:1px solid rgba(0,0,0,0.08); color:var(--cb-text); }

        /* Divider and edit section styling */
        .divider-text { text-align:center; margin:26px 0 12px; color:var(--cb-muted); }
        .divider-text span { background: rgba(255,255,255,0.98); padding: 0 10px; }

        .edit-section { background: rgba(var(--cb-accent-rgb),0.04); border-radius:8px; padding:14px; border:1px solid rgba(var(--cb-accent-rgb),0.06); }
        .edit-section p { margin:0 0 12px; color:var(--cb-text); }
        .edit-section a { display:inline-block; text-decoration:none; padding:10px 14px; border-radius:10px; background:var(--cb-accent-2); color:var(--cb-text); }

        @media (max-width:768px) {
            .container { max-width:100%; }
            .header-text h1 { font-size:1.3rem; }
            .form-actions { flex-direction:column; align-items:stretch; }
            .form-actions .btn { width:100%; }
        }

        @media (max-width:480px) {
            body { padding:12px; }
            .search-card { padding:18px !important; }
            .form-group { margin-bottom:12px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
            <div class="header-content">
                <div class="header-logo"><img src="images/—Pngtree—3d hostel pin_13164212.png" alt="Logo"></div>
            <div class="header-text">
                <h1>Register New Student</h1>
                <p><a href="index.php" class="btn-home">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <div class="search-card">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="registerForm" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="account_number">Account Number *</label>
                <input id="account_number" name="account_number" placeholder="Enter your account number" value="<?php echo isset($_POST['account_number'])?htmlspecialchars($_POST['account_number']):''; ?>" required>
            </div>

            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input id="full_name" name="full_name" placeholder="Enter your full name" value="<?php echo isset($_POST['full_name'])?htmlspecialchars($_POST['full_name']):''; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email'])?htmlspecialchars($_POST['email']):''; ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" placeholder="Enter your phone number" value="<?php echo isset($_POST['phone'])?htmlspecialchars($_POST['phone']):''; ?>">
            </div>

            <div class="form-group">
                <label for="hostel_name">Hostel Name *</label>
                <input id="hostel_name" name="hostel_name" placeholder="Enter hostel name" value="<?php echo isset($_POST['hostel_name'])?htmlspecialchars($_POST['hostel_name']):''; ?>" required>
            </div>

            <div class="form-group">
                <label for="room_number">Room Number *</label>
                <input id="room_number" name="room_number" placeholder="Enter room number" value="<?php echo isset($_POST['room_number'])?htmlspecialchars($_POST['room_number']):''; ?>" required>
            </div>

            <div class="form-group">
                <label for="food_preference">Food Preference</label>
                <select id="food_preference" name="food_preference">
                    <option value="veg" <?php echo (isset($_POST['food_preference']) && $_POST['food_preference'] === 'veg') ? 'selected' : ''; ?>>Veg</option>
                    <option value="non-veg" <?php echo (isset($_POST['food_preference']) && $_POST['food_preference'] === 'non-veg') ? 'selected' : ''; ?>>Non-Veg</option>
                </select>
            </div>

            <div class="form-group">
                <label for="photo">📷 Profile Photo</label>
                <input id="photo" type="file" name="photo" accept="image/*">
                <img id="photoPreview" src="#" alt="Preview" style="display:none;max-width:180px;border-radius:8px;border:2px solid var(--cb-accent);margin-top:8px;" />
                <small style="color: var(--cb-muted); margin-top: 4px; display: block;">PNG, JPG, or WEBP only</small>
            </div>

            <div class="form-actions">
                <button id="submitBtn" type="submit" class="btn btn-primary">✅ Register Student</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>

            <div class="divider-text">
                <span>Already Registered?</span>
            </div>

            <div class="edit-section">
                <p>If you already have an account, search for yourself on the home page and click the "✏️ Edit Details" button to update your information.</p>
                <a href="index.php" class="btn btn-secondary" style="width: 100%; text-align: center; display: block;">← Back to Home</a>
            </div>
        </form>
        <script>
            // Client-side validation and image preview
            (function(){
                const form = document.getElementById('registerForm');
                const account = document.getElementById('account_number');
                const name = document.getElementById('full_name');
                const hostel = document.getElementById('hostel_name');
                const room = document.getElementById('room_number');
                const email = document.getElementById('email');
                const phone = document.getElementById('phone');
                const photo = document.getElementById('photo');
                const preview = document.getElementById('photoPreview');

                photo.addEventListener('change', function(e){
                    const file = this.files[0];
                    if (!file) { preview.style.display='none'; preview.src='#'; return; }
                    const allowed = ['image/png','image/jpeg','image/webp'];
                    if (!allowed.includes(file.type)) {
                        alert('Only PNG, JPG, or WEBP images are allowed.');
                        this.value = '';
                        preview.style.display='none';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(ev){
                        preview.src = ev.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                });

                form.addEventListener('submit', function(e){
                    // small client-side checks
                    if (!account.value.trim() || !name.value.trim() || !hostel.value.trim() || !room.value.trim()) {
                        alert('Please fill required fields: Account, Full name, Hostel, Room.');
                        e.preventDefault();
                        return false;
                    }
                    if (email.value && !/^\S+@\S+\.\S+$/.test(email.value)) {
                        alert('Please enter a valid email address.');
                        e.preventDefault();
                        return false;
                    }
                    if (phone.value && !/^[0-9+\-\s()]{6,20}$/.test(phone.value)) {
                        alert('Please enter a valid phone number.');
                        e.preventDefault();
                        return false;
                    }
                    // allow submit
                });
            })();
        </script>
    </div>
</div>
</body>
</html>
