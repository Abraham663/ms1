<?php
require_once 'config.php';

// Insert John's student record
$account_number = '541';
$full_name = 'John';
$email = 'john@example.com';
$phone = '9876543213';
$photo_path = 'images/p1.webp';
$hostel_name = 'Sacred Heart Hostel';
$room_number = '5';
$food_preference = 'veg';

// Check if student already exists
$check_stmt = $conn->prepare("SELECT id FROM students WHERE account_number = ?");
$check_stmt->bind_param("s", $account_number);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing record
    $update_stmt = $conn->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, photo_path = ?, hostel_name = ?, room_number = ?, food_preference = ? WHERE account_number = ?");
    $update_stmt->bind_param("ssssssss", $full_name, $email, $phone, $photo_path, $hostel_name, $room_number, $food_preference, $account_number);
    
    if ($update_stmt->execute()) {
        echo "✓ John's record updated successfully!<br>";
        echo "Account: 541<br>";
        echo "Name: John<br>";
        echo "Room: 5<br>";
        echo "Hostel: Sacred Heart Hostel<br>";
        echo "Food Preference: Veg<br>";
        echo "Photo: images/p1.webp<br>";
    } else {
        echo "Error updating record: " . $update_stmt->error;
    }
    $update_stmt->close();
} else {
    // Insert new record
    $insert_stmt = $conn->prepare("INSERT INTO students (account_number, full_name, email, phone, photo_path, hostel_name, room_number, food_preference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("ssssssss", $account_number, $full_name, $email, $phone, $photo_path, $hostel_name, $room_number, $food_preference);
    
    if ($insert_stmt->execute()) {
        echo "✓ John's record inserted successfully!<br>";
        echo "Account: 541<br>";
        echo "Name: John<br>";
        echo "Room: 5<br>";
        echo "Hostel: Sacred Heart Hostel<br>";
        echo "Food Preference: Veg<br>";
        echo "Photo: images/p1.webp<br>";
        echo "<br><a href='index.php'>Go to Home & Search</a>";
    } else {
        echo "Error inserting record: " . $insert_stmt->error;
    }
    $insert_stmt->close();
}

$check_stmt->close();
$conn->close();
?>
