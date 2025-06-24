<?php
include "db.php";

$new_password = "1234"; // Change this to your desired new password
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

// Update the password for the admin user
$username = "admin"; // Make sure this matches your admin username

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ? AND user_type_id = 1");
$stmt->bind_param("ss", $hashed_password, $username);

if ($stmt->execute()) {
    echo "Admin password updated successfully!";
} else {
    echo "Error updating password: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
